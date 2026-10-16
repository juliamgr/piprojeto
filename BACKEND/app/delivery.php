<?php
declare(strict_types=1);

function save_delivery_estimate(int $orderId, array $data): void {
    if (empty($_SESSION['admin_id']) || time() - (int)($_SESSION['last_active'] ?? 0) > 1800) {
        throw new DomainException('Entre novamente no painel administrativo.');
    }
    $minutes = integer($data['delivery_minutes'] ?? null, 1, 43200);
    db()->beginTransaction();
    try {
        $order = query('SELECT status FROM orders WHERE id=? FOR UPDATE', [$orderId])->fetch();
        if (!$order) throw new DomainException('Pedido não encontrado.');
        if (in_array($order['status'], ['concluido', 'cancelado'], true)) {
            throw new DomainException('Não é possível alterar a previsão de um pedido encerrado.');
        }
        $eta = (new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo')))->modify('+' . $minutes . ' minutes');
        query('INSERT INTO order_delivery(order_id,estimated_at) VALUES(?,?) ON DUPLICATE KEY UPDATE estimated_at=VALUES(estimated_at),updated_at=CURRENT_TIMESTAMP', [$orderId, $eta->format('Y-m-d H:i:s')]);
        query('INSERT INTO order_history(order_id,admin_id,status,note) VALUES(?,?,?,?)', [$orderId, $_SESSION['admin_id'], $order['status'], 'Previsão de entrega atualizada para ' . $eta->format('d/m/Y H:i') . ' (horário de Brasília).']);
        db()->commit();
    } catch (Throwable $err) {
        if (db()->inTransaction()) db()->rollBack();
        throw $err;
    }
}

function delivery_iso(?string $value): ?string {
    return $value ? (new DateTimeImmutable($value, new DateTimeZone('America/Sao_Paulo')))->format(DateTimeInterface::ATOM) : null;
}
