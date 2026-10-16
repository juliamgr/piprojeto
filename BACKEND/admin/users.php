<?php
require __DIR__.'/../app/bootstrap.php'; require_admin();
require __DIR__.'/../app/admin-layout.php';
$q=is_string($_GET['q'] ?? null) ? substr(trim($_GET['q']),0,160) : '';
$page=max(1,(int)($_GET['page'] ?? 1)); $rows=[]; $count=0;
try {
    $where=$q!=='' ? ' WHERE name LIKE ? OR email LIKE ?' : '';
    $params=$q!=='' ? ['%'.$q.'%','%'.$q.'%'] : [];
    $count=(int)query('SELECT COUNT(*) FROM users'.$where,$params)->fetchColumn();
    $page=min($page,max(1,(int)ceil($count/25))); $offset=($page-1)*25;
    $rows=query('SELECT id,name,email,phone,member_type,city,created_at FROM users'.$where.' ORDER BY id DESC LIMIT 25 OFFSET '.$offset,$params)->fetchAll();
} catch (Throwable $err) { error_log((string)$err); flash('Não foi possível carregar os usuários. Confira a conexão e a importação de users.sql.','error'); }
page_start('Usuários cadastrados','users'); ?>
<section class="panel"><p>Cadastros recebidos pelo formulário de filiação. Estas contas não têm acesso administrativo.</p><a class="button" href="../../FRONT-AGROLINK/index4%20.html">Abrir cadastro</a></section>
<form class="panel" method="get"><label>Buscar por nome ou e-mail<input name="q" maxlength="160" value="<?= e($q) ?>"></label><button class="button">Buscar</button></form>
<p><?= $count ?> usuário(s) · Página <?= $page ?></p>
<div class="panel" style="overflow:auto"><table><thead><tr><th>Nome</th><th>E-mail</th><th>Telefone</th><th>Filiação</th><th>Município</th><th>Cadastro</th><th>Ficha</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><?php foreach (['name','email','phone','member_type','city','created_at'] as $key): ?><td><?= e($row[$key]) ?></td><?php endforeach; ?><td><a href="user.php?id=<?= (int)$row['id'] ?>">Abrir</a></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="7">Nenhum usuário encontrado.</td></tr><?php endif; ?></tbody></table></div>
<nav aria-label="Paginação"><?php if ($page>1): ?><a href="?<?= e(http_build_query(['q'=>$q,'page'=>$page-1])) ?>">← Anterior</a><?php endif; ?> <?php if ($page*25<$count): ?><a href="?<?= e(http_build_query(['q'=>$q,'page'=>$page+1])) ?>">Próxima →</a><?php endif; ?></nav>
<?php page_end(); ?>
