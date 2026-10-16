<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../app/bootstrap.php';
try {
    echo 'Banco configurado: '.query('SELECT DATABASE()')->fetchColumn().PHP_EOL;
    foreach (['admins','users','products','orders','order_items','order_delivery'] as $table) {
        try { echo $table.': '.query('SELECT COUNT(*) FROM '.$table)->fetchColumn().' registro(s)'.PHP_EOL; }
        catch (Throwable $err) { echo $table.': indisponível. Confira a importação de RECUPERAR-SEM-APAGAR.sql.'.PHP_EOL; }
    }
} catch (Throwable $err) { fwrite(STDERR, 'Falha de conexão. Confira app/config.php e o MySQL.'.PHP_EOL); exit(1); }
