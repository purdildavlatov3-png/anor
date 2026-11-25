<?php
require_once __DIR__ . '/../bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error'=>'Метод не поддерживается'],405);
$data=request_json();
$user=clean_string($data['username']??'',80); $pass=(string)($data['password']??'');
$stmt=db()->prepare('SELECT id,password_hash FROM admins WHERE username=? AND active=1');
$stmt->execute([$user]); $row=$stmt->fetch();
if (!$row || !password_verify($pass,$row['password_hash'])) json_response(['error'=>'Неверный логин или пароль.'],401);
session_regenerate_id(true);
$_SESSION['admin_id']=(int)$row['id'];
json_response(['ok'=>true,'csrf'=>csrf_token()]);
