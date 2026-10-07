<?php
require __DIR__.'/../BACKEND/app/bootstrap.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); header('Allow: POST'); exit; }
try { csrf_check(); } catch (DomainException $err) { http_response_code(403); exit('Sessão expirada. Volte e recarregue a página.'); }
unset($_SESSION['user_id'],$_SESSION['user_name'],$_SESSION['user_last_active']);
session_regenerate_id(true); $_SESSION['csrf']=bin2hex(random_bytes(32)); redirect('login.php');
