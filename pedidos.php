<?php
declare(strict_types=1);
require __DIR__.'/BACKEND/app/bootstrap.php';
require __DIR__.'/BACKEND/app/profile.php';
try { $customer = current_customer(); }
catch (Throwable $err) { error_log((string)$err); http_response_code(503); exit('Não foi possível carregar sua conta. Tente novamente.'); }
if (!$customer) redirect('conta.php');
?>
<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Minhas entregas — Agrolink</title>
<link rel="stylesheet" href="entregas.css">
<script src="js/entregas-model.js" defer></script><script src="js/entregas.js" defer></script>
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="favicon-16x16.png">
<link rel="icon" type="image/png" sizes="16x16" href="favicon-32x32.png">
<link rel="manifest" href="/site.webmanifest">
</head>
<body><main class="delivery-shell">
<header><a class="brand" href="index.html"><img src="img/agrolink-logo.png" width="200px">
</a>
<nav>
    <a href="conta.php" class="sapo">Minha conta</a>
    <a href="index.html" class="sapo">Voltar à feira</a>
</nav></header>
<p class="eyebrow">DA FEIRA ATÉ VOCÊ</p><h1>Histórico de entregas</h1>
<p>Olá, <?= e($customer['name']) ?>. Acompanhe o preparo e a chegada dos seus pedidos.</p>
<div class="refresh-bar"><p id="update-message" role="status">Carregando pedidos…</p><button id="refresh" type="button">Atualizar agora</button></div>
<div id="orders" aria-busy="true"></div>
<div class="pagination"><button id="newest" type="button" hidden>Voltar aos mais recentes</button><button id="older" type="button" hidden>Pedidos anteriores</button></div>
<noscript>Ative o JavaScript para consultar o status das entregas.</noscript>
</main>
<dialog id="delivery-dialog" aria-labelledby="delivery-title">
<div class="dialog-top"><h2 id="delivery-title">Acompanhar entrega</h2><button id="close-dialog" type="button" aria-label="Fechar acompanhamento">✕</button></div>
<div id="delivery-details"></div>
</dialog></body></html>
