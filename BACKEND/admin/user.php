<?php
require __DIR__.'/../app/bootstrap.php'; require_admin();
require __DIR__.'/../app/admin-layout.php';
try {
    $id=integer($_GET['id'] ?? 0,1,PHP_INT_MAX);
    $user=query('SELECT id,name,email,phone,cpf,birth_date,member_type,city,address,neighborhood,zip_code,production,notes,privacy_accepted_at,created_at FROM users WHERE id=?',[$id])->fetch();
    if (!$user) { http_response_code(404); page_start('Usuário não encontrado','users'); echo '<a href="users.php">Voltar</a>'; page_end(); exit; }
} catch (Throwable $err) { admin_error($err); redirect('users.php'); }
page_start('Ficha do usuário','users'); ?>
<p><a href="users.php">← Voltar à lista</a></p><section class="panel"><dl>
<?php foreach (['name'=>'Nome','email'=>'E-mail','phone'=>'Telefone','member_type'=>'Tipo de filiação','cpf'=>'CPF','birth_date'=>'Nascimento','city'=>'Município','address'=>'Endereço','neighborhood'=>'Bairro','zip_code'=>'CEP','production'=>'Produção / oferta','notes'=>'Observações','privacy_accepted_at'=>'Consentimento registrado em','created_at'=>'Cadastro criado em'] as $key=>$label): ?><dt><strong><?= e($label) ?></strong></dt><dd style="white-space:pre-wrap;overflow-wrap:anywhere;margin:0 0 1rem"><?= e($user[$key] ?: 'Não informado') ?></dd><?php endforeach; ?>
</dl></section><?php page_end(); ?>
