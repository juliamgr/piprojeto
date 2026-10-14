<?php
require __DIR__.'/../app/bootstrap.php'; require_admin(); require __DIR__.'/../app/admin-layout.php';
try {
$summary=query("SELECT COUNT(*) AS total, COALESCE(SUM(status='recebido'),0) AS received, COALESCE(SUM(status IN ('confirmado','separacao','enviado')),0) AS progressing, COALESCE(SUM(CASE WHEN status='concluido' THEN total_cents ELSE 0 END),0) AS completed FROM orders")->fetch();
$low=query('SELECT COUNT(*) FROM products WHERE active=1 AND stock<=min_stock')->fetchColumn();
$alerts=query('SELECT * FROM products WHERE active=1 AND stock>0 AND expires_on IS NOT NULL AND expires_on<=DATE_ADD(CURRENT_DATE,INTERVAL 3 DAY) ORDER BY expires_on LIMIT 8')->fetchAll();
$recent=query('SELECT * FROM orders ORDER BY id DESC LIMIT 6')->fetchAll();
} catch (Throwable $err) { error_log((string)$err); http_response_code(503); exit('Banco indisponível. Importe database/schema.sql e confira app/config.php.'); }
page_start('Visão geral','index'); ?>
<section class="welcome">
    <div><span class="eyebrow">CUIDAR DO ALIMENTO, DO INÍCIO AO FIM</span>
    <h2>Uma boa colheita<br>começa na organização.</h2><p>Veja o que chegou e o que precisa de atenção.</p>
    <a class="button lime" href="orders.php?status=recebido">Ver novos pedidos</a>
</div>
<div class="welcome-art" aria-hidden="true">
</div></section>
<section class="stats" aria-label="Resumo da operação"><article>
    <span>Novos pedidos</span><strong>
        <?= (int)$summary['received'] ?></strong>
        <small>Aguardando confirmação</small>
    </article><article>
        <span>Em andamento</span>
        <strong>
            <?= (int)$summary['progressing'] ?>
        </strong>
        <small>Da confirmação à entrega</small>
    </article>
    <article><span>Estoque baixo</span><strong>
        <?= (int)$low ?>
    </strong>
    <small>Produtos no limite mínimo</small>
</article><article>
    <span>Pedidos concluídos</span>
    <strong class="value">
        <?= brl($summary['completed']) ?>
    </strong>
    <small>Valor acumulado · não é confirmação de pagamento</small>
</article></section>
<div class="dashboard-grid">
    <section class="panel">
        <div class="section-heading">
            <h2>Pedidos recentes</h2>
            <a href="orders.php">Ver todos →</a>
        </div>
        <?php if (!$recent): ?>
            <div class="empty">
                <h3>Sua primeira solicitação aparece aqui.</h3>
                <p>Quando alguém finalizar a sacola na loja, o pedido será registrado no painel.</p>
            </div><?php else: ?><div class="table-wrap"><table>
                <thead><tr><th>Pedido / cliente</th>
                <th>Status</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody><?php foreach($recent as $o): ?><tr>
            <td><a href="order.php?id=<?= (int)$o['id'] ?>"><?= e($o['code']) ?></a><small><?= e($o['customer_name']) ?>
        </small></td><td><?php pill($o['status']); ?>
    </td><td><?= brl($o['total_cents']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<section class="panel"><div class="section-heading"><h2>Olho na validade</h2><span class="mini-tag">Até 3 dias</span></div><p class="muted small">Priorize a distribuição e registre perdas quando necessário.</p><?php if (!$alerts): ?><div class="empty"><p>Nenhum alerta de validade no momento.</p></div><?php endif; ?><?php foreach($alerts as $p): ?><a class="alert-row" href="product.php?id=<?= (int)$p['id'] ?>"><div><strong><?= e($p['name']) ?></strong><small><?= e($p['batch_code'] ?: 'Sem lote') ?> · <?= (int)$p['stock'] ?> disponíveis</small></div><span class="pill <?= $p['expires_on']<date('Y-m-d')?'cancelado':'recebido' ?>"><?= date('d/m',strtotime($p['expires_on'])) ?></span></a><?php endforeach; ?><a class="text-link" href="inventory.php">Abrir estoque →</a></section></div>
<?php page_end(); ?>
