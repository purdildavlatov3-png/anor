<?php
require_once __DIR__ . '/../bootstrap.php';
$token = clean_string($_GET['t'] ?? '', 100);
if (!preg_match('/^[a-f0-9]{64}$/', $token)) json_response(['error'=>'Ссылка карты недействительна.'],404);
$stmt = db()->prepare('SELECT public_id,name,stamps,reward_ready FROM members WHERE qr_token_hash=? AND active=1');
$stmt->execute([hash('sha256',$token)]);
$m = $stmt->fetch();
if (!$m) json_response(['error'=>'Карта не найдена или заблокирована.'],404);
json_response(['member'=>['publicId'=>$m['public_id'],'name'=>$m['name'],'stamps'=>(int)$m['stamps'],'rewardReady'=>(bool)$m['reward_ready']]]);
