<?php
require __DIR__.'/../app/bootstrap.php'; require_admin(); require __DIR__.'/../app/admin-layout.php';
$status=is_string($_GET['status'] ?? null)?$_GET['status']:''; $search=is_string($_GET['q'] ?? null)?substr(trim($_GET['q']),0,100):'';
$page=max(1,(int)($_GET['page'] ?? 1)); $where=[];$params=[];
if (isset(statuses()[$status])) { $where[]='status=?';$params[]=$status; }
if ($search!=='') { $where[]='(code LIKE ? OR customer_name LIKE ?)'; $params[]='%'.$search.'%';$params[]='%'.$search.'%'; }
$filter=$where?' WHERE '.implode(' AND ',$where):'';
$count=(int)query('SELECT COUNT(*) FROM orders'.$filter,$params)->fetchColumn(); $pages=max(1,(int)ceil($count/20)); $page=min($page,$pages); $offset=($page-1)*20;
$orders=query('SELECT * FROM orders'.$filter.' ORDER BY id DESC LIMIT 20 OFFSET '.$offset,$params)->fetchAll();
page_start('Pedidos','orders'); ?>
<p class="lead">Receba, confira e acompanhe cada solicitação até a entrega.</p>
<form class="filters" method="get"><label class="grow">Buscar pedido ou cliente<input type="search" name="q" value="<?= e($search) ?>" placeholder="Código ou nome do cliente"></label><label>Status<select name="status"><option value="">Todos os status</option><?php foreach(statuses() as $key=>$label): ?><option value="<?= $key ?>" <?= $status===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label><button class="button">Filtrar</button><a class="button secondary" href="orders.php">Limpar</a></form>
<section class="panel"><div class="section-heading"><h2>Solicitações</h2><span class="muted"><?= $count ?> pedido(s)</span></div><div class="table-wrap"><table><thead><tr><th>Pedido</th><th>Cliente</th><th>Recebido em</th><th>Status</th><th>Total</th><th></th></tr></thead><tbody><?php foreach($orders as $o): ?><tr><td><a href="order.php?id=<?= (int)$o['id'] ?>"><?= e($o['code']) ?></a></td><td><?= e($o['customer_name']) ?><small><?= e($o['region']) ?></small></td><td><?= date('d/m/Y H:i',strtotime($o['created_at'])) ?></td><td><?php pill($o['status']); ?></td><td><?= brl($o['total_cents']) ?></td><td><a class="button secondary compact" href="order.php?id=<?= (int)$o['id'] ?>">Abrir →</a></td></tr><?php endforeach; ?><?php if (!$orders): ?><tr><td colspan="6" class="empty">Nenhum pedido encontrado.</td></tr><?php endif; ?></tbody></table></div><nav class="pagination" aria-label="Paginação"><?php if($page>1): ?><a href="?<?= e(http_build_query(['q'=>$search,'status'=>$status,'page'=>$page-1])) ?>">← Anterior</a><?php endif; ?><span>Página <?= $page ?> de <?= $pages ?></span><?php if($page<$pages): ?><a href="?<?= e(http_build_query(['q'=>$search,'status'=>$status,'page'=>$page+1])) ?>">Próxima →</a><?php endif; ?></nav></section>
<?php page_end(); ?>
