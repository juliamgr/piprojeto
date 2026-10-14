<?php
require __DIR__.'/../app/bootstrap.php';
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        csrf_check();
        $email=field($_POST,'email',3,160);
        $password=$_POST['password'] ?? '';
        if (!is_string($password) || strlen($password)<1 || strlen($password)>200) throw new DomainException('E-mail ou senha incorretos.');
        $bucket=hash('sha256',$_SERVER['REMOTE_ADDR'] ?? 'local');
        db()->beginTransaction();
        query('INSERT INTO login_attempts(bucket) VALUES(?) ON DUPLICATE KEY UPDATE bucket=VALUES(bucket)',[$bucket]);
        $attempt=query('SELECT *, (locked_until>CURRENT_TIMESTAMP) AS locked FROM login_attempts WHERE bucket=? FOR UPDATE',[$bucket])->fetch();
        if ($attempt['locked']) { db()->commit(); throw new DomainException('Muitas tentativas. Aguarde 15 minutos.'); }
        if ($attempt['locked_until']) query('UPDATE login_attempts SET failures=0,locked_until=NULL WHERE bucket=?',[$bucket]);
        $admin=query('SELECT * FROM admins WHERE email=?',[$email])->fetch();
        $dummy='$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        $valid=password_verify($password,$admin['password_hash'] ?? $dummy);
        if (!$admin || !$valid) {
            query('UPDATE login_attempts SET failures=failures+1 WHERE bucket=?',[$bucket]);
            query('UPDATE login_attempts SET locked_until=DATE_ADD(CURRENT_TIMESTAMP,INTERVAL 15 MINUTE) WHERE bucket=? AND failures>=5',[$bucket]);
            db()->commit(); throw new DomainException('E-mail ou senha incorretos.');
        }
        query('DELETE FROM login_attempts WHERE bucket=?',[$bucket]); db()->commit();
        session_regenerate_id(true); $_SESSION['admin_id']=(int)$admin['id']; $_SESSION['admin_name']=$admin['name'];
        $_SESSION['last_active']=time(); $_SESSION['csrf']=bin2hex(random_bytes(32)); redirect('index.php');
    } catch (Throwable $err) {
        try { if (db()->inTransaction()) db()->rollBack(); } catch (Throwable $ignored) {}
        $error=$err instanceof DomainException ? $err->getMessage() : 'Não foi possível acessar. Confira a instalação do banco de dados.';
        if (!($err instanceof DomainException)) error_log((string)$err);
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Entrar — Agrolink</title><link rel="stylesheet" href="../assets/admin.css">
</head>
<body class="login-page">
    <main class="login-card">
    <a href="../FRONT-AGROLINK/index.html" class="brand">
    <img src="../img/agrolink-logo.png" alt="Agrolink">
         </a>
            <p class="eyebrow">ÁREA ADMINISTRATIVA</p>

<?php if ($error): ?>
    <div class="notice error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post">
    <?php csrf_field(); ?>
    <label>E-mail
      <input type="email" name="email" autocomplete="username" required maxlength="160" value="<?= e($_POST['email'] ?? '') ?>">
      </label>
      <label>Senha<input type="password" name="password" autocomplete="current-password" required maxlength="200">
      </label>
      <button class="button full">Entrar</button>
    </form>
</main>
</body></html>
