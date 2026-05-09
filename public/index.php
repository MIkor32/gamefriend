<?php
require_once __DIR__ . '/../src/controller/FriendTradeController.php';

$dsn = getenv('DB_DSN') ?: 'mysql:host=127.0.0.1;dbname=czz;charset=utf8mb4';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try { $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]); }
catch (Throwable $e) { http_response_code(500); echo json_encode(['code'=>500,'msg'=>'数据库连接失败','data'=>[]], JSON_UNESCAPED_UNICODE); exit; }

$model = new FriendTradeModel($pdo);
$ctl = new FriendTradeController($model);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$map = ['/friend/index'=>'index','/friend/sign'=>'sign','/friend/market'=>'market','/friend/buy'=>'buy','/friend/release'=>'release','/friend/work'=>'work','/friend/partners'=>'partners','/friend/rank'=>'rank','/friend/notices'=>'notices'];
header('Content-Type: application/json; charset=utf-8');
if (!isset($map[$path])) { echo json_encode(['code'=>404,'msg'=>'not found','data'=>[]], JSON_UNESCAPED_UNICODE); exit; }
$result = $ctl->{$map[$path]}();
echo json_encode($result, JSON_UNESCAPED_UNICODE);
