# Docker Compose 部署说明（/opt/project）

## 1. 克隆代码
```bash
cd /opt/project
git clone <你的仓库地址> gamefriend
cd gamefriend
```

## 2. 启动容器
```bash
docker compose up -d --build
```

启动后：
- Web: `http://服务器IP:8080`
- MySQL: `127.0.0.1:33066`

## 3. 手动导入 SQL（必须手动）

> 本项目不会自动执行建表 SQL，请手动导入 `database/friend_trade.sql`。

```bash
docker compose exec -T db mysql -uroot -proot123 czz < database/friend_trade.sql
```

## 4. 验证接口
```bash
curl -H "X-User-Id: 1001" http://127.0.0.1:8080/friend/index
curl -X POST -H "X-User-Id: 1001" http://127.0.0.1:8080/friend/sign
curl -H "X-User-Id: 1001" "http://127.0.0.1:8080/friend/market?page=1&limit=20"
```

## 5. 常用命令
```bash
# 查看状态
docker compose ps

# 查看日志
docker compose logs -f web app db

# 重启
docker compose restart

# 停止
docker compose down
```

## 6. 生产建议
- 修改 `docker-compose.yml` 里的 MySQL 密码。
- Nginx 前再加一层 HTTPS 反向代理（如 443 入口）。
- 给 `db_data` 做定期备份。
