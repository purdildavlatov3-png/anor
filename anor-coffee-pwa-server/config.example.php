<?php
// Скопируйте файл в config.php и заполните параметры MySQL.
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'anor_coffee',
        'user' => 'DB_USERNAME',
        'pass' => 'DB_PASSWORD',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'Анор кофе',
        'base_url' => 'https://ВАШ-ДОМЕН.RU', // без слеша в конце
        'timezone' => 'Europe/Moscow',
        'session_name' => 'anor_session',
    ],
];
