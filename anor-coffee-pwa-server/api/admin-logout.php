<?php
require_once __DIR__ . '/../bootstrap.php';
require_admin(); require_csrf();
$_SESSION=[]; session_regenerate_id(true);
json_response(['ok'=>true]);
