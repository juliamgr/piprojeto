<?php
declare(strict_types=1);
require __DIR__ . '/../BACKEND/app/bootstrap.php';
require __DIR__ . '/../BACKEND/app/profile.php';

api_run('GET', function () {
    $user = current_customer();
    // Only expose fields required for checkout, never password hashes or CPF.
    $customer = $user ? array_intersect_key($user, array_flip(['name','email','phone','city','address','neighborhood','zip_code'])) : null;
    json_out([
        'success' => true,
        'csrf' => $_SESSION['csrf'],
        'loggedIn' => $user !== null,
        'userId' => $user ? (int)$user['id'] : null,
        'name' => $user['name'] ?? '',
        'customer' => $customer
    ]);
});
