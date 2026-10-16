<?php
return [
    'dsn' => getenv('AGROLINK_DSN') ?: 'mysql:host=127.0.0.1;dbname=agrolink;charset=utf8mb4',
    'user' => getenv('AGROLINK_DB_USER') !== false ? getenv('AGROLINK_DB_USER') : 'root',
    'password' => getenv('AGROLINK_DB_PASSWORD') ?: '',
    'shipping_cents' => 990,
    'free_shipping_from_cents' => 12000,
    'regions' => ['Porto Alegre', 'Canoas', 'Viamão', 'Guaíba'],
];
