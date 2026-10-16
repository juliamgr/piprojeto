<?php
// Herda host, porta, usuário e senha de app/config.php.
// Troca somente o nome do banco para agrolink_produtores.
// Opcionalmente, defina AGROLINK_PRODUTORES_DSN/USER/PASSWORD no servidor.
return [
    'database' => 'agrolink_produtores',
    'dsn' => getenv('AGROLINK_PRODUTORES_DSN') ?: null,
    'user' => getenv('AGROLINK_PRODUTORES_USER') !== false ? getenv('AGROLINK_PRODUTORES_USER') : null,
    'password' => getenv('AGROLINK_PRODUTORES_PASSWORD') !== false ? getenv('AGROLINK_PRODUTORES_PASSWORD') : null,
];
