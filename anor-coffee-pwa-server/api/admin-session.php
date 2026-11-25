<?php
require_once __DIR__ . '/../bootstrap.php';
if (empty($_SESSION['admin_id'])) json_response(['authenticated'=>false],200);
$admin=require_admin();
json_response(['authenticated'=>true,'admin'=>$admin,'csrf'=>csrf_token()]);
