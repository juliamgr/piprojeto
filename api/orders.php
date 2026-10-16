<?php
declare(strict_types=1);
require __DIR__ . '/../BACKEND/app/bootstrap.php';
require __DIR__ . '/../BACKEND/app/orders.php';

api_run('POST', function () {
    if (empty($_SESSION['user_id']) || time() - (int)($_SESSION['user_last_active'] ?? 0) > 1800) {
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_last_active']);
        json_out(['success' => false, 'message' => 'Entre na sua conta para finalizar o pedido.'], 401);
    }
    $order = create_order(input_json());
    json_out(['success' => true, 'order' => $order], 201);
});
