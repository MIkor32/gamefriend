# Docker Compose 部署说明（仅远程 MySQL）

## 1. 克隆代码
```bash
cd /opt/project
git clone <你的仓库地址> gamefriend
cd gamefriend
```

## 2. 配置远程数据库连接

复制模板：
```bash
cp .env.remote.example .env
```

编辑 `.env`：
```dotenv
DB_DSN="mysql:host=你的远程数据库IP;port=3306;dbname=czz;charset=utf8mb4"
DB_USER="你的数据库账号"
DB_PASS="你的数据库密码"
```

## 3. 启动（不启动本地 MySQL）
```bash
docker compose -f docker-compose.remote.yml up -d --build
```

启动后访问：
- Web: `http://服务器IP:8080`

## 4. 手动导入 SQL 到远程 MySQL（必须手动）

> 本项目不会自动执行建表 SQL，请手动导入 `database/friend_trade.sql`。

方式 A（宿主机有 mysql 客户端）：
```bash
mysql -h 你的远程数据库IP -P 3306 -u 你的数据库账号 -p czz < database/friend_trade.sql
```

方式 B（从 app 容器执行）：
```bash
docker compose -f docker-compose.remote.yml exec app sh -lc 'apk add --no-cache mysql-client && mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" czz' 
# 然后在 mysql 提示符中 source /var/www/html/database/friend_trade.sql;
```

## 5. 验证接口
```bash
curl -H "X-User-Id: 1001" http://127.0.0.1:8080/friend/index
curl -X POST -H "X-User-Id: 1001" http://127.0.0.1:8080/friend/sign
curl -H "X-User-Id: 1001" "http://127.0.0.1:8080/friend/market?page=1&limit=20"
```

## 6. 常用命令
```bash
# 查看状态
docker compose -f docker-compose.remote.yml ps

# 查看日志
docker compose -f docker-compose.remote.yml logs -f web app

# 重启
docker compose -f docker-compose.remote.yml restart

# 停止
docker compose -f docker-compose.remote.yml down
```
