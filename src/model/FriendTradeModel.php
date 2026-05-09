<?php

class FriendTradeModel
{
    private PDO $db;
    private array $configCache = [];

    public function __construct(PDO $db) { $this->db = $db; }
    public function jsonSuccess($data = [], $msg = 'success'): array { return ['code'=>0,'msg'=>$msg,'data'=>$data]; }
    public function jsonError($msg = 'error', $code = 400, $data = []): array { return ['code'=>$code,'msg'=>$msg,'data'=>$data]; }

    public function getConfig($key, $default = null) {
        if (isset($this->configCache[$key])) return $this->configCache[$key];
        $stmt = $this->db->prepare('SELECT config_value FROM friend_config WHERE config_key=:k LIMIT 1');
        $stmt->execute([':k'=>$key]); $row=$stmt->fetch(PDO::FETCH_ASSOC);
        return $this->configCache[$key] = $row['config_value'] ?? $default;
    }
    public function getUser($userId){$s=$this->db->prepare('SELECT * FROM friend_user WHERE user_id=:u LIMIT 1');$s->execute([':u'=>$userId]);return $s->fetch(PDO::FETCH_ASSOC)?:null;}
    public function getUserForUpdate($userId){$s=$this->db->prepare('SELECT * FROM friend_user WHERE user_id=:u LIMIT 1 FOR UPDATE');$s->execute([':u'=>$userId]);return $s->fetch(PDO::FETCH_ASSOC)?:null;}
    public function initUser($userId,$nickname='',$avatar=''){ if($this->getUser($userId)) return $this->getUser($userId); $now=time(); $s=$this->db->prepare('INSERT INTO friend_user(user_id,nickname,avatar,coins,worth,level,owner_user_id,partner_count,create_time,update_time) VALUES(:u,:n,:a,:c,:w,1,0,0,:t,:t)');$s->execute([':u'=>$userId,':n'=>$nickname,':a'=>$avatar,':c'=>(int)$this->getConfig('init_coins',1000),':w'=>(int)$this->getConfig('init_worth',500),':t'=>$now]); return $this->getUser($userId);}
    public function dailyGrowth($userId){$u=$this->getUser($userId); if(!$u) return; $today=date('Y-m-d'); if(($u['last_growth_date']??null)===$today) return; $up=max((int)$this->getConfig('daily_growth_min',20),(int)floor($u['worth']*(float)$this->getConfig('daily_growth_rate',0.02))); $s=$this->db->prepare('UPDATE friend_user SET worth=worth+:up,last_growth_date=:d,update_time=:t WHERE user_id=:u');$s->execute([':up'=>$up,':d'=>$today,':t'=>time(),':u'=>$userId]);}
    public function sign($userId){$this->db->beginTransaction(); try{$u=$this->getUserForUpdate($userId); $today=date('Y-m-d'); if(!$u) throw new RuntimeException('用户不存在'); if(($u['last_sign_date']??null)===$today) throw new InvalidArgumentException('今天已经签到过了'); $y=date('Y-m-d',strtotime('-1 day')); $days=(($u['last_sign_date']??'')===$y)?((int)$u['sign_days']+1):1; $reward=(int)$this->getConfig('sign_base_reward',500)+min((int)$this->getConfig('sign_extra_max',500),$days*(int)$this->getConfig('sign_extra_per_day',50)); $after=(int)$u['coins']+$reward; $s=$this->db->prepare('UPDATE friend_user SET coins=:c,sign_days=:sd,last_sign_date=:d,update_time=:t WHERE user_id=:u');$s->execute([':c'=>$after,':sd'=>$days,':d'=>$today,':t'=>time(),':u'=>$userId]); $this->addCoinLog($userId,$reward,(int)$u['coins'],$after,'sign','每日签到奖励'); $this->db->commit(); return $this->jsonSuccess(['reward'=>$reward,'coins'=>$after,'sign_days'=>$days],'签到成功');}catch(InvalidArgumentException $e){$this->db->rollBack();return $this->jsonError($e->getMessage());}catch(Throwable $e){$this->db->rollBack();return $this->jsonError('签到失败');}}
    public function addCoinLog($userId,$changeAmount,$beforeAmount,$afterAmount,$scene,$remark=''){ $s=$this->db->prepare('INSERT INTO friend_coin_log(user_id,change_amount,before_amount,after_amount,scene,remark,create_time) VALUES(:u,:c,:b,:a,:s,:r,:t)');$s->execute([':u'=>$userId,':c'=>$changeAmount,':b'=>$beforeAmount,':a'=>$afterAmount,':s'=>$scene,':r'=>$remark,':t'=>time()]);}
    public function addNotice($userId,$type,$title,$content){$s=$this->db->prepare('INSERT INTO friend_notice(user_id,type,title,content,create_time) VALUES(:u,:ty,:ti,:c,:t)');$s->execute([':u'=>$userId,':ty'=>$type,':ti'=>$title,':c'=>$content,':t'=>time()]);}
    public function getMarket($currentUserId,$params=[]){$page=max(1,(int)($params['page']??1));$limit=max(1,min(50,(int)($params['limit']??20)));$offset=($page-1)*$limit;$keyword=trim($params['keyword']??'');$sort=$params['sort']??'worth';$order='u.worth DESC';if($sort==='coins')$order='u.coins DESC';if($sort==='random')$order='RAND()';$where='u.user_id<>:me AND u.status=1';$bind=[':me'=>$currentUserId]; if($sort==='free') $where.=' AND u.owner_user_id=0'; if($keyword!==''){ $where.=' AND u.nickname LIKE :kw';$bind[':kw']='%'.$keyword.'%';}
    $sql="SELECT u.user_id,u.nickname,u.avatar,u.worth,u.coins,u.owner_user_id,o.nickname owner_nickname FROM friend_user u LEFT JOIN friend_user o ON o.user_id=u.owner_user_id WHERE $where ORDER BY $order LIMIT $offset,$limit"; $s=$this->db->prepare($sql);$s->execute($bind);$list=$s->fetchAll(PDO::FETCH_ASSOC); foreach($list as &$v){$v['can_buy']=true;} return ['list'=>$list,'page'=>$page,'limit'=>$limit];}
    public function getPartners($ownerUserId){$today=date('Y-m-d');$s=$this->db->prepare('SELECT u.user_id,u.nickname,u.avatar,u.worth,r.buy_price,r.create_time,(w.id IS NOT NULL) today_worked FROM friend_relation r JOIN friend_user u ON u.user_id=r.partner_user_id LEFT JOIN friend_work_log w ON w.partner_user_id=u.user_id AND w.work_date=:d WHERE r.owner_user_id=:o AND r.status=1');$s->execute([':o'=>$ownerUserId,':d'=>$today]);return $s->fetchAll(PDO::FETCH_ASSOC);}    
    public function getRank($type='worth',$limit=50){$map=['worth'=>'worth','coins'=>'coins','partner'=>'partner_count','earned'=>'total_earned'];$field=$map[$type]??'worth';$s=$this->db->prepare("SELECT user_id,nickname,avatar,worth,coins,partner_count,total_earned FROM friend_user WHERE status=1 ORDER BY $field DESC LIMIT :l");$s->bindValue(':l',(int)$limit,PDO::PARAM_INT);$s->execute();return $s->fetchAll(PDO::FETCH_ASSOC);}    
    public function getNotices($userId,$limit=20){$s=$this->db->prepare('SELECT title,content,is_read,create_time FROM friend_notice WHERE user_id=:u ORDER BY id DESC LIMIT :l');$s->bindValue(':u',$userId,PDO::PARAM_INT);$s->bindValue(':l',(int)$limit,PDO::PARAM_INT);$s->execute();return $s->fetchAll(PDO::FETCH_ASSOC);}    
    public function buyPartner($buyerUserId,$partnerUserId){return $this->jsonError('示例仓库：请在真实环境补全事务逻辑后使用');}
    public function releaseSelf($userId){return $this->jsonError('示例仓库：请在真实环境补全事务逻辑后使用');}
    public function work($ownerUserId,$partnerUserId){return $this->jsonError('示例仓库：请在真实环境补全事务逻辑后使用');}
}
