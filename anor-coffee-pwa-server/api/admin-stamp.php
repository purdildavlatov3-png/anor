<?php
require_once __DIR__ . '/../bootstrap.php';
$admin=require_admin(); require_csrf();
$data=request_json();
$id=(int)($data['memberId']??0);
$requestId=clean_string($data['requestId']??'',36);
if (!$id || !preg_match('/^[a-f0-9-]{36}$/i',$requestId)) json_response(['error'=>'Некорректный запрос.'],422);
$pdo=db();
try {
    $pdo->beginTransaction();
    $stmt=$pdo->prepare('SELECT id,stamps,reward_ready FROM members WHERE id=? AND active=1 FOR UPDATE');
    $stmt->execute([$id]); $m=$stmt->fetch();
    if (!$m) { $pdo->rollBack(); json_response(['error'=>'Клиент не найден.'],404); }
    if ((int)$m['stamps'] >= 10 || (bool)$m['reward_ready']) { $pdo->rollBack(); json_response(['error'=>'Сначала необходимо выдать и отметить погашение подарка.'],409); }
    $new=(int)$m['stamps']+1; $reward=$new>=10 ? 1 : 0;
    $upd=$pdo->prepare('UPDATE members SET stamps=?,reward_ready=? WHERE id=?');
    $upd->execute([$new,$reward,$id]);
    $tx=$pdo->prepare("INSERT INTO loyalty_transactions(member_id,admin_id,action,delta,note,request_id) VALUES(?,?, 'stamp',1,?,?)");
    $tx->execute([$id,$admin['id'],'Штамп начислен сотрудником',$requestId]);
    $pdo->commit();
    json_response(['ok'=>true,'stamps'=>$new,'rewardReady'=>(bool)$reward]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if ($e instanceof PDOException && $e->getCode()==='23000') json_response(['error'=>'Этот запрос уже обработан. Обновите данные карты.'],409);
    json_response(['error'=>'Не удалось начислить штамп.'],500);
}
