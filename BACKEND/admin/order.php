<?php
require __DIR__.'/../app/bootstrap.php'; require_admin(); require __DIR__.'/../app/admin-layout.php'; require __DIR__.'/../app/orders.php'; require __DIR__.'/../app/delivery.php';
$id=max(0,(int)($_GET['id'] ?? 0));
if ($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        csrf_check();
        if (($_POST['action'] ?? '')==='status') {
            $next=field($_POST,'status',3,20); $note=field($_POST,'note',0,220);
            if ($next==='cancelado' && empty($_POST['confirm_cancel'])) throw new DomainException('Marque a confirmação para cancelar e devolver os itens ao estoque.');
            update_order($id,$next,$note); flash('Status atualizado.');
        } elseif (($_POST['action'] ?? '')==='confirm_delivery') {
            update_order($id,'concluido','Entrega recebida pelo cliente. Confirmada pela equipe.');
            flash('Entrega confirmada. O cliente já pode consultar o status Entregue.');
        } elseif (($_POST['action'] ?? '')==='delivery') {
            save_delivery_estimate($id,$_POST); flash('Previsão salva e disponível para o cliente.');
        } elseif (($_POST['action'] ?? '')==='notes') {
            $note=field($_POST,'notes',0,3000); query('UPDATE orders SET notes=? WHERE id=?',[$note,$id]); flash('Observação salva.');
        }
    } catch (Throwable $err) { admin_error($err); }
    redirect('order.php?id='.$id);
}
$o=query('SELECT * FROM orders WHERE id=?',[$id])->fetch();
if (!$o) { http_response_code(404); page_start('Pedido não encontrado','orders'); echo '<a href="orders.php">Voltar aos pedidos</a>'; page_end(); exit; }
$delivery = null;
$deliveryReady = true;
try { $delivery = query('SELECT estimated_at FROM order_delivery WHERE order_id=?', [$id])->fetch(); }
catch (Throwable $err) { $deliveryReady = false; error_log((string)$err); }
$items=query('SELECT * FROM order_items WHERE order_id=?',[$id])->fetchAll();
$history=query('SELECT h.*,a.name AS admin_name FROM order_history h LEFT JOIN admins a ON a.id=h.admin_id WHERE order_id=? ORDER BY h.id DESC',[$id])->fetchAll();
page_start('Detalhes do pedido','orders'); ?>
<div class="section-heading"><a href="orders.php">← Todos os pedidos</a><?php pill($o['status']); ?></div>
<h2 class="order-code"><?= e($o['code']) ?></h2><p class="muted">Recebido em <?= date('d/m/Y H:i',strtotime($o['created_at'])) ?></p>
<div class="dashboard-grid"><div><section class="panel"><h2>Itens da sacola</h2><div class="table-wrap"><table><thead><tr><th>Produto</th><th>Qtd.</th><th>Preço</th><th>Subtotal</th></tr></thead><tbody><?php foreach($items as $i): ?><tr><td><?= e($i['product_name']) ?><small><?= e($i['unit']) ?></small></td><td><?= (int)$i['quantity'] ?></td><td><?= brl($i['unit_price_cents']) ?></td><td><?= brl((int)$i['quantity']*(int)$i['unit_price_cents']) ?></td></tr><?php endforeach; ?></tbody></table></div><dl class="totals"><div><dt>Subtotal</dt><dd><?= brl($o['subtotal_cents']) ?></dd></div><div><dt>Entrega</dt><dd><?= brl($o['shipping_cents']) ?></dd></div><div class="grand-total"><dt>Total</dt><dd><?= brl($o['total_cents']) ?></dd></div></dl></section>
<section class="panel"><h2>Histórico</h2><ol class="timeline"><?php foreach($history as $h): ?><li><strong><?= e(statuses()[$h['status']] ?? $h['status']) ?></strong><p><?= e($h['note']) ?></p><small><?= date('d/m/Y H:i',strtotime($h['created_at'])) ?> · <?= e($h['admin_name'] ?? 'Loja') ?></small></li><?php endforeach; ?></ol></section></div>
<div><section class="panel"><h2>Cliente e entrega</h2><dl class="details"><dt>Nome</dt><dd><?= e($o['customer_name']) ?></dd><dt>Telefone</dt><dd><?= e($o['customer_phone']) ?></dd><dt>E-mail</dt><dd><?= e($o['customer_email']) ?></dd><dt>Endereço</dt><dd><?= nl2br(e($o['address'])) ?><br><?= e($o['region']) ?></dd><dt>Pagamento combinado</dt><dd><?= $o['payment_method']==='pix'?'Pix após confirmação':'Na entrega' ?></dd></dl><p class="small muted">A solicitação não comprova pagamento. Combine a cobrança e a entrega com o cliente.</p></section>
<section class="panel"><h2>Previsão de entrega</h2>
<?php if (!$deliveryReady): ?><p role="alert">Não foi possível carregar a previsão. Confira se BACKEND/database/entregas.sql foi importado no banco do Agrolink e se o banco está disponível.</p>
<?php else: ?>
<p><?= $delivery ? 'Chegada estimada: <strong>'.e(date('d/m/Y H:i',strtotime($delivery['estimated_at']))).'</strong>' : 'Nenhum prazo informado ao cliente.' ?></p>
<?php if (!in_array($o['status'], ['concluido','cancelado'], true)): ?>
<form method="post"><?php csrf_field(); ?><input type="hidden" name="action" value="delivery">
<label>Entrega prevista em quantos minutos a partir de agora?<input name="delivery_minutes" type="number" min="1" max="43200" step="1" placeholder="Ex.: 45" required></label>
<p class="small muted">60 minutos = 1 hora · 1440 minutos = 1 dia. A previsão usa o horário de Brasília. Salvar novamente substitui o prazo anterior.</p>
<button class="button full" type="submit">Salvar previsão para o cliente</button></form>
<p class="small muted">Quando o pedido sair, altere o status para Enviado em Próxima etapa. O cliente verá “Saiu para entrega”. Concluído aparece como “Entregue”. A previsão não muda o status sozinha.</p>
<?php endif; endif; ?></section>
<section class="panel"><h2>Próxima etapa</h2>
<?php if ($o['status']==='enviado'): ?>
<p>Confirme quando o cliente receber o pedido. O acompanhamento será atualizado para <strong>Entregue</strong>.</p>
<form method="post"><?php csrf_field(); ?><input type="hidden" name="action" value="confirm_delivery">
<button class="button lime full" type="submit">✓ Confirmar entrega</button></form>
<?php elseif ($o['status']==='concluido'): ?>
<p class="muted">✓ Entrega confirmada. Este pedido está concluído.</p>
<?php else: $next=next_statuses($o['status']); if($next): ?><form method="post"><?php csrf_field(); ?><input type="hidden" name="action" value="status"><label>Alterar status<select name="status"><?php foreach($next as $s): ?><option value="<?= e($s) ?>"><?= e(statuses()[$s]) ?></option><?php endforeach; ?></select></label><label>Motivo ou observação<input name="note" maxlength="220" placeholder="Ex.: itens conferidos pela equipe"></label><?php if(in_array('cancelado',$next,true)): ?><label class="checkbox"><input type="checkbox" name="confirm_cancel" value="1"> Se escolher Cancelado, confirmo a devolução dos itens ao estoque.</label><?php endif; ?><button class="button full">Salvar status</button></form><?php else: ?><p class="muted">Este pedido foi encerrado.</p><?php endif; endif; ?></section>
<section class="panel"><h2>Observações internas</h2><form method="post"><?php csrf_field(); ?><input type="hidden" name="action" value="notes"><label class="sr-only" for="notes">Observações internas</label><textarea id="notes" name="notes" rows="4" maxlength="3000"><?= e($o['notes']) ?></textarea><button class="button secondary full">Salvar observação</button></form></section></div></div>
<?php page_end(); ?>
