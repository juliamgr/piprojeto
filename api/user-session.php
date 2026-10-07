<?php
declare(strict_types=1);

require __DIR__ . '/../../BACKEND/app/bootstrap.php';

api_run('GET', function () {
    if (
        empty($_SESSION['user_id']) ||
        time() - ($_SESSION['user_last_active'] ?? 0) > 1800
    ) {
        unset(
            $_SESSION['user_id'],
            $_SESSION['user_name'],
            $_SESSION['user_last_active']
        );

        json_out(['loggedIn' => false]);
    }

    $_SESSION['user_last_active'] = time();

    json_out([
        'loggedIn' => true,
        'name' => $_SESSION['user_name'] ?? ''
    ]);
});