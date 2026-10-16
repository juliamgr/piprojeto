<?php
require __DIR__.'/../app/bootstrap.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); exit; }
try { csrf_check(); } catch (DomainException $err) { http_response_code(403); exit('Sessão expirada. Recarregue o painel.'); }
$_SESSION=[]; session_destroy(); redirect('login.php');
