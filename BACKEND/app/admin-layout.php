<?php
function page_start(string $title,string $active): void { ?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($title) ?> — Agrolink</title><link rel="stylesheet" href="../assets/admin.css"><link rel="stylesheet" href="../assets/produtores.css"><link rel="icon" type="image/svg+xml" href="../img/LOGO-AGROLINK-DARK.svg"></head><body>
<a class="skip" href="#main">Pular para o conteúdo</a>
<aside class="sidebar"><a class="brand" href="../../index.html"><img src="../img/LOGO-AGROLINK-DARK.svg" alt="Agrolink — abrir a loja" style="display:block;width:170px;max-width:100%;height:auto"></a><p class="eyebrow">ADMINISTRAÇÃO</p>
<nav aria-label="Menu administrativo"><?php foreach (['index'=>'Visão geral','orders'=>'Pedidos','inventory'=>'Estoque','produtores'=>'Produtores RS','users'=>'Usuários'] as $key=>$label): ?><a class="<?= $active===$key?'active':'' ?>" href="<?= $key ?>.php" <?= $active===$key?'aria-current="page"':'' ?>><?= $label ?><span aria-hidden="true">↗</span></a><?php endforeach; ?></nav>
<div class="sidebar-bottom"><small><a href="../../index.html" target="_blank" rel="noopener">Abrir a loja ↗</a><form action="logout.php" method="post"><?php csrf_field(); ?><button class="logout">Sair da conta</button></form></div></aside>
<div class="workspace"><header class="topbar"><span>Área da equipe</span><span><?= e($_SESSION['admin_name'] ?? '') ?> <span class="avatar" aria-hidden="true">A</span></span></header>
<main id="main"><div class="page-heading"><div><p class="eyebrow">AGROLINK / GESTÃO</p><h1><?= e($title) ?></h1></div><time><?= date('d/m/Y') ?></time></div>
<?php if (!empty($_SESSION['flash'])): [$message,$kind]=$_SESSION['flash']; unset($_SESSION['flash']); ?><div class="notice <?= e($kind) ?>" role="status"><?= e($message) ?></div><?php endif; ?>
<?php }
function page_end(): void { ?><footer class="page-footer">Agrolink · Agricultura local, gestão próxima.</footer></main></div></body></html><?php }
function pill(string $status): void { ?><span class="pill <?= e($status) ?>"><?= e(statuses()[$status] ?? $status) ?></span><?php }
function admin_error(Throwable $err): void {
    try { if (db()->inTransaction()) db()->rollBack(); } catch (Throwable $ignored) {}
    if ($err instanceof DomainException) flash($err->getMessage(),'error');
    else { error_log((string)$err); flash('Não foi possível salvar. Confira a conexão e tente novamente.','error'); }
}
