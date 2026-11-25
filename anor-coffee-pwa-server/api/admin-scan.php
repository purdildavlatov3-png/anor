<?php
require_once __DIR__ . '/../bootstrap.php';
require_admin();
$data=request_json();
$raw=clean_string($data['qr']??'',500);
$token='';
if (preg_match('/[?&]t=([a-f0-9]{64})/', $raw, $match)) $token=$match[1];
elseif (preg_match('/^[a-f0-9]{64}$/',$raw)) $token=$raw;
if (!$token) json_response(['error'=>'QR-код не распознан. Отсканируйте QR карты Анор кофе.'],422);
$stmt=db()->prepare('SELECT id,public_id,name,phone,stamps,reward_ready,created_at FROM members WHERE qr_token_hash=? AND active=1');
$stmt->execute([hash('sha256',$token)]);
$m=$stmt->fetch();
if (!$m) json_response(['error'=>'Карта не найдена или заблокирована.'],404);
json_response(['member'=>[
 'id'=>(int)$m['id'],'publicId'=>$m['public_id'],'name'=>$m['name'],
 'phone'=>$m['phone'],'stamps'=>(int)$m['stamps'],'rewardReady'=>(bool)$m['reward_ready'],
 'createdAt'=>$m['created_at'],'token'=>$token
]]);
