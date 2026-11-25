<?php
require_once __DIR__ . '/../bootstrap.php';
$admin=require_admin(); require_csrf();
$data=request_json(); $id=(int)($data['memberId']??0);
if (!$id) json_response(['error'=>'Клиент не выбран.'],422);
$pdo=db();
try {
  $pdo->beginTransaction();
  $s=$pdo->prepare('SELECT stamps,reward_ready FROM members WHERE id=? AND active=1 FOR UPDATE');
  $s->execute([$id]); $m=$s->fetch();
  if (!$m || !(bool)$m['reward_ready'] || (int)$m['stamps']<10) { $pdo->rollBack(); json_response(['error'=>'У клиента пока нет доступного подарка.'],409); }
  $pdo->prepare('UPDATE members SET stamps=0,reward_ready=0 WHERE id=?')->execute([$id]);
  $pdo->prepare("INSERT INTO loyalty_transactions(member_id,admin_id,action,delta,note) VALUES(?,?, 'reward_redeemed',-10,'Бесплатный напиток выдан')")->execute([$id,$admin['id']]);
  $pdo->commit(); json_response(['ok'=>true,'stamps'=>0,'rewardReady'=>false]);
} catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); json_response(['error'=>'Не удалось погасить подарок.'],500); }
