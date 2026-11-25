<?php
require_once __DIR__ . '/../bootstrap.php';
require_admin();
$pdo=db();
$stats=$pdo->query("SELECT COUNT(*) members, SUM(reward_ready=1) rewards FROM members WHERE active=1")->fetch();
$recent=$pdo->query("SELECT public_id,name,phone,stamps,reward_ready,created_at FROM members WHERE active=1 ORDER BY id DESC LIMIT 30")->fetchAll();
$tx=$pdo->query("SELECT t.action,t.delta,t.note,t.created_at,m.name,m.public_id,a.username FROM loyalty_transactions t JOIN members m ON m.id=t.member_id LEFT JOIN admins a ON a.id=t.admin_id ORDER BY t.id DESC LIMIT 30")->fetchAll();
json_response(['stats'=>['members'=>(int)$stats['members'],'rewards'=>(int)$stats['rewards']], 'members'=>$recent,'transactions'=>$tx]);
