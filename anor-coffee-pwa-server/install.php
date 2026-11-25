<?php
declare(strict_types=1);
session_start();
$error = '';
$done = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['host'] ?? 'localhost');
    $name = trim($_POST['dbname'] ?? '');
    $user = trim($_POST['dbuser'] ?? '');
    $pass = (string)($_POST['dbpass'] ?? '');
    $base = rtrim(trim($_POST['base_url'] ?? ''), '/');
    $admin = trim($_POST['admin'] ?? '');
    $adminPass = (string)($_POST['admin_pass'] ?? '');
    if (!$name || !$user || !$base || !$admin || strlen($adminPass) < 12) {
        $error = 'Заполните все поля. Пароль администратора — минимум 12 символов.';
    } elseif (!filter_var($base, FILTER_VALIDATE_URL) || !str_starts_with($base, 'https://')) {
        $error = 'Укажите полный HTTPS-адрес сайта, например https://anor-coffee.ru';
    } else {
        try {
            $pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $schema = file_get_contents(__DIR__ . '/schema.sql');
            // schema.sql содержит отдельные CREATE TABLE без процедур.
            foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) $pdo->exec($statement);
            $stmt = $pdo->prepare('INSERT INTO admins (username,password_hash,role) VALUES (?,?,?)');
            $stmt->execute([$admin, password_hash($adminPass, PASSWORD_DEFAULT), 'owner']);
            $cfg = "<?php\nreturn " . var_export([
                'db' => ['host'=>$host,'name'=>$name,'user'=>$user,'pass'=>$pass,'charset'=>'utf8mb4'],
                'app' => ['name'=>'Анор кофе','base_url'=>$base,'timezone'=>'Europe/Moscow','session_name'=>'anor_session']
            ], true) . ";\n";
            if (file_put_contents(__DIR__ . '/config.php', $cfg, LOCK_EX) === false) throw new RuntimeException('Не удалось записать config.php. Проверьте права на папку.');
            $done = true;
            session_destroy();
        } catch (Throwable $e) {
            $error = 'Ошибка установки: ' . $e->getMessage();
        }
    }
}
?><!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Установка ANOR COFFEE</title><style>
*{box-sizing:border-box}body{margin:0;background:#121210;color:#f2ebdd;font:14px Arial,sans-serif;padding:25px}.box{max-width:520px;margin:5vh auto;background:#20201c;border:1px solid #48402f;border-radius:22px;padding:26px}h1{font-size:25px}p{color:#b7ad9d;line-height:1.6}label{display:block;margin:14px 0 6px;color:#d7c6a8;font-size:12px}input{width:100%;padding:13px;border-radius:10px;border:1px solid #514938;background:#151512;color:white}button{margin-top:20px;width:100%;padding:15px;border:0;border-radius:12px;background:#e6a12d;color:#21170b;font-weight:bold}.err{color:#ffad91}.ok{color:#a9c195}
</style></head><body><div class="box"><h1>ANOR COFFEE · Установка</h1>
<?php if ($done): ?><h2 class="ok">База данных и администратор созданы.</h2><p>Удалите файл <b>install.php</b> с сервера. Затем откройте <b>/admin/</b> и войдите с созданными учётными данными.</p>
<?php else: ?><p>Мастер подключит MySQL, создаст таблицы и первого владельца системы. Перед установкой создайте пустую базу данных в панели хостинга.</p>
<?php if ($error): ?><p class="err"><?=htmlspecialchars($error)?></p><?php endif; ?>
<form method="post">
<label>MySQL host</label><input name="host" value="<?=htmlspecialchars($_POST['host']??'localhost')?>" required>
<label>Имя базы данных</label><input name="dbname" required>
<label>Пользователь MySQL</label><input name="dbuser" required>
<label>Пароль MySQL</label><input name="dbpass" type="password">
<label>Адрес сайта (HTTPS)</label><input name="base_url" placeholder="https://anor-coffee.ru" required>
<label>Логин владельца</label><input name="admin" autocomplete="username" required>
<label>Пароль владельца (не менее 12 символов)</label><input name="admin_pass" type="password" autocomplete="new-password" minlength="12" required>
<button>Установить систему</button></form><?php endif; ?></div></body></html>
