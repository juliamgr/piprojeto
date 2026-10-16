<?php
declare(strict_types=1);
require __DIR__ . '/../BACKEND/app/bootstrap.php';
require __DIR__ . '/../BACKEND/app/profile.php';
require __DIR__ . '/../BACKEND/app/delivery.php';

api_run('GET', function () {
    $user = current_customer();
    if (!$user) json_out(['success'=>false,'message'=>'Entre na sua conta para acompanhar suas entregas.'], 401);
    $before = isset($_GET['before']) ? integer($_GET['before'], 1, 2147483647) : 2147483647;
    // Ownership is applied in SQL. Never accept a user ID or email from the browser.
    $rows = query('SELECT o.id,o.code,o.status,o.created_at,o.address,o.region,o.total_cents,d.estimated_at,d.updated_at AS estimate_updated_at,
        (SELECT MIN(h.created_at) FROM order_history h WHERE h.order_id=o.id AND h.status=\'enviado\') AS dispatched_at,
        (SELECT MIN(h.created_at) FROM order_history h WHERE h.order_id=o.id AND h.status=\'concluido\') AS delivered_at
        FROM orders o LEFT JOIN order_delivery d ON d.order_id=o.id
        WHERE o.user_id=? AND o.id<? ORDER BY o.id DESC LIMIT 21', [(int)$user['id'], $before])->fetchAll();
    $hasMore = count($rows) > 20;
    $rows = array_slice($rows, 0, 20);
    $orders = [];
    foreach ($rows as $row) {
        $orders[] = [
            'id'=>(int)$row['id'], 'code'=>$row['code'], 'status'=>$row['status'],
            'createdAt'=>delivery_iso($row['created_at']),
            'estimatedAt'=>delivery_iso($row['estimated_at']),
            'estimateUpdatedAt'=>delivery_iso($row['estimate_updated_at']),
            'dispatchedAt'=>delivery_iso($row['dispatched_at']),
            'deliveredAt'=>delivery_iso($row['delivered_at']),
            'address'=>$row['address'], 'region'=>$row['region'], 'total'=>(int)$row['total_cents']/100
        ];
    }
    json_out(['success'=>true,'serverNow'=>date(DateTimeInterface::ATOM),'orders'=>$orders,
        'nextBefore'=>$hasMore ? (int)$rows[count($rows)-1]['id'] : null]);
});
