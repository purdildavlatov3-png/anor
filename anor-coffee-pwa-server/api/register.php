<?php
require_once __DIR__ . '/../bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error'=>'Метод не поддерживается'],405);
$data = request_json();
$name = clean_string($data['name'] ?? '', 120);
$phone = preg_replace('/[^\d+]/', '', (string)($data['phone'] ?? ''));
$phone = substr($phone, 0, 24);
if (mb_strlen($name) < 2) json_response(['error'=>'Введите имя (минимум 2 символа).'],422);
if (!preg_match('/^\+?\d{10,15}$/', $phone)) json_response(['error'=>'Проверьте номер телефона.'],422);
try {
    $publicId = strtoupper(bin2hex(random_bytes(6)));
    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $stmt = db()->prepare('INSERT INTO members (public_id,qr_token_hash,name,phone) VALUES (?,?,?,?)');
    $stmt->execute([$publicId,$hash,$name,$phone]);
    $id = (int)db()->lastInsertId();
    json_response([
        'member'=>['id'=>$id,'publicId'=>$publicId,'name'=>$name,'stamps'=>0,'rewardReady'=>false],
        'qrUrl'=>app_url('') . '/?t=' . rawurlencode($token)
    ],201);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') json_response(['error'=>'Этот номер уже зарегистрирован. Откройте свою карту по ранее сохранённой ссылке или обратитесь к сотруднику.'],409);
    json_response(['error'=>'Не удалось создать карту. Попробуйте позже.'],500);
}
