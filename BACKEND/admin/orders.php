<?php
require __DIR__.'/../app/bootstrap.php'; require_admin(); require __DIR__.'/../app/admin-layout.php'; require __DIR__.'/../app/orders.php';
$status=is_string($_GET['status'] ?? null)?$_GET['status']:''; $search=is_string($_GET['q'] ?? null)?substr(trim($_GET['q']),0,100):'';
$page=max(1,(int)($_GET['page'] ?? 1));
if ($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        csrf_check();
        if (($_POST['action'] ?? '')!=='confirm_delivery') throw new DomainException('Operação inválida.');
        $orderId=integer($_POST['order_id'] ?? null,1,2147483647);
        update_order($orderId,'concluido','Entrega recebida pelo cliente. Confirmada pela equipe.');
        flash('Entrega confirmada. O cliente já pode consultar o status Entregue.');
    } catch (Throwable $err) { admin_error($err); }
    redirect('orders.php?'.http_build_query(['status'=>$status,'q'=>$search,'page'=>$page]));
}
$where=[];$params=[];
if (isset(statuses()[$status])) { $where[]='status=?';$params[]=$status; }
if ($search!=='') { $where[]='(code LIKE ? OR customer_name LIKE ?)'; $params[]='%'.$search.'%';$params[]='%'.$search.'%'; }
$filter=$where?' WHERE '.implode(' AND ',$where):'';
$count=(int)query('SELECT COUNT(*) FROM orders'.$filter,$params)->fetchColumn(); $pages=max(1,(int)ceil($count/20)); $page=min($page,$pages); $offset=($page-1)*20;
$orders=query('SELECT * FROM orders'.$filter.' ORDER BY id DESC LIMIT 20 OFFSET '.$offset,$params)->fetchAll();
page_start('Pedidos','orders'); ?>
<p class="lead">Receba, confira e acompanhe cada solicitação até a entrega.</p>
<form class="filters" method="get"><label class="grow">Buscar pedido ou cliente<input type="search" name="q" value="<?= e($search) ?>" placeholder="Código ou nome do cliente"></label><label>Status<select name="status"><option value="">Todos os status</option><?php foreach(statuses() as $key=>$label): ?><option value="<?= $key ?>" <?= $status===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label><button class="button">Filtrar</button><a class="button secondary" href="orders.php">Limpar</a></form>
<section class="panel"><div class="section-heading"><h2>Solicitações</h2><span class="muted"><?= $count ?> pedido(s)</span></div><div class="table-wrap"><table><thead><tr><th>Pedido</th><th>Cliente</th><th>Recebido em</th><th>Status</th><th>Total</th><th>Ações</th></tr></thead><tbody><?php foreach($orders as $o): ?><tr><td><a href="order.php?id=<?= (int)$o['id'] ?>"><?= e($o['code']) ?></a></td><td><?= e($o['customer_name']) ?><small><?= e($o['region']) ?></small></td><td><?= date('d/m/Y H:i',strtotime($o['created_at'])) ?></td><td><?php pill($o['status']); ?></td><td><?= brl($o['total_cents']) ?></td><td><a class="button secondary compact" href="order.php?id=<?= (int)$o['id'] ?>">Abrir →</a>
<?php if ($o['status']==='enviado'): ?>
<form method="post" style="margin-top:8px"><?php csrf_field(); ?>
<input type="hidden" name="action" value="confirm_delivery"><input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
<button class="button lime compact" type="submit">✓ Confirmar entrega</button></form>
<?php elseif ($o['status']==='concluido'): ?><small>✓ Entrega confirmada</small><?php endif; ?>
</td></tr><?php endforeach; ?><?php if (!$orders): ?><tr><td colspan="6" class="empty">Nenhum pedido encontrado.</td></tr><?php endif; ?></tbody></table></div><nav class="pagination" aria-label="Paginação"><?php if($page>1): ?><a href="?<?= e(http_build_query(['q'=>$search,'status'=>$status,'page'=>$page-1])) ?>">← Anterior</a><?php endif; ?><span>Página <?= $page ?> de <?= $pages ?></span><?php if($page<$pages): ?><a href="?<?= e(http_build_query(['q'=>$search,'status'=>$status,'page'=>$page+1])) ?>">Próxima →</a><?php endif; ?></nav></section>
<?php page_end(); ?>
