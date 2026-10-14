<?php
declare(strict_types=1);
require __DIR__ . '/../BACKEND/app/bootstrap.php';
require __DIR__ . '/../BACKEND/app/catalog.php';
api_run('GET', function () {
    json_out(['success'=>true, 'products'=>public_products()]);
});
