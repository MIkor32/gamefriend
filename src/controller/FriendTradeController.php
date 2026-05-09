<?php
require_once __DIR__ . '/../model/FriendTradeModel.php';

class FriendTradeController
{
    private FriendTradeModel $model;
    public function __construct(FriendTradeModel $model){$this->model=$model;}

    private function getCurrentUserId(): int { return (int)($_SERVER['HTTP_X_USER_ID'] ?? $_GET['user_id'] ?? 0); }
    private function getCurrentUserInfo(): array { return ['nickname'=>$_SERVER['HTTP_X_USER_NICKNAME'] ?? '游客','avatar'=>$_SERVER['HTTP_X_USER_AVATAR'] ?? '']; }

    public function index(){ $uid=$this->getCurrentUserId(); if($uid<=0) return $this->model->jsonError('未登录',401); $info=$this->getCurrentUserInfo(); $this->model->initUser($uid,$info['nickname'],$info['avatar']); $this->model->dailyGrowth($uid); $user=$this->model->getUser($uid); $owner=$user['owner_user_id']>0?$this->model->getUser((int)$user['owner_user_id']):null; return $this->model->jsonSuccess(['user'=>$user,'owner'=>$owner,'partners'=>$this->model->getPartners($uid),'notices'=>$this->model->getNotices($uid,5),'rank_preview'=>$this->model->getRank('worth',10)]); }
    public function sign(){ $uid=$this->getCurrentUserId(); if($uid<=0) return $this->model->jsonError('未登录',401); return $this->model->sign($uid); }
    public function market(){ $uid=$this->getCurrentUserId(); if($uid<=0) return $this->model->jsonError('未登录',401); return $this->model->jsonSuccess($this->model->getMarket($uid,$_GET)); }
    public function buy(){ $uid=$this->getCurrentUserId(); $input=json_decode(file_get_contents('php://input'),true) ?: $_POST; return $this->model->buyPartner($uid,(int)($input['partner_user_id']??0)); }
    public function release(){ $uid=$this->getCurrentUserId(); return $this->model->releaseSelf($uid); }
    public function work(){ $uid=$this->getCurrentUserId(); $input=json_decode(file_get_contents('php://input'),true) ?: $_POST; return $this->model->work($uid,(int)($input['partner_user_id']??0)); }
    public function partners(){ $uid=$this->getCurrentUserId(); return $this->model->jsonSuccess(['list'=>$this->model->getPartners($uid)]); }
    public function rank(){ return $this->model->jsonSuccess(['list'=>$this->model->getRank($_GET['type']??'worth',50)]); }
    public function notices(){ $uid=$this->getCurrentUserId(); return $this->model->jsonSuccess(['list'=>$this->model->getNotices($uid,20)]); }
}
