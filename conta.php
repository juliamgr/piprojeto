<?php
declare(strict_types=1);

require __DIR__ . '/../BACKEND/app/bootstrap.php';

$error = '';

if (
    !empty($_SESSION['user_id']) &&
    time() - ($_SESSION['user_last_active'] ?? 0) > 1800
) {
    unset(
        $_SESSION['user_id'],
        $_SESSION['user_name'],
        $_SESSION['user_last_active']
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();

        if (($_POST['action'] ?? '') === 'logout') {
            unset(
                $_SESSION['user_id'],
                $_SESSION['user_name'],
                $_SESSION['user_last_active']
            );

            session_regenerate_id(true);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));

            redirect('index.html');
        }

        if (($_POST['action'] ?? '') !== 'login') {
            throw new DomainException('Operação inválida.');
        }

        $email = strtolower(field($_POST, 'email', 3, 160));
        $password = $_POST['password'] ?? '';

        if (
            !is_string($password) ||
            strlen($password) < 1 ||
            strlen($password) > 72
        ) {
            throw new DomainException('E-mail ou senha incorretos.');
        }

        $bucket = hash(
            'sha256',
            'cliente:' . ($_SERVER['REMOTE_ADDR'] ?? 'local')
        );

        db()->beginTransaction();

        query(
            'INSERT INTO user_login_attempts (bucket)
             VALUES (?)
             ON DUPLICATE KEY UPDATE bucket = VALUES(bucket)',
            [$bucket]
        );

        $attempt = query(
            'SELECT *,
                    (locked_until > CURRENT_TIMESTAMP) AS locked
             FROM user_login_attempts
             WHERE bucket = ?
             FOR UPDATE',
            [$bucket]
        )->fetch();

        if ($attempt['locked']) {
            db()->commit();

            throw new DomainException(
                'Muitas tentativas. Aguarde 15 minutos.'
            );
        }

        if ($attempt['locked_until']) {
            query(
                'UPDATE user_login_attempts
                 SET failures = 0, locked_until = NULL
                 WHERE bucket = ?',
                [$bucket]
            );
        }

        $user = query(
            'SELECT id, name, password_hash
             FROM users
             WHERE email = ?',
            [$email]
        )->fetch();

        $dummyHash =
            '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

        $valid = password_verify(
            $password,
            $user['password_hash'] ?? $dummyHash
        );

        if (!$user || !$valid) {
            query(
                'UPDATE user_login_attempts
                 SET failures = failures + 1
                 WHERE bucket = ?',
                [$bucket]
            );

            query(
                'UPDATE user_login_attempts
                 SET locked_until =
                     DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 15 MINUTE)
                 WHERE bucket = ? AND failures >= 5',
                [$bucket]
            );

            db()->commit();

            throw new DomainException('E-mail ou senha incorretos.');
        }

        query(
            'DELETE FROM user_login_attempts WHERE bucket = ?',
            [$bucket]
        );

        db()->commit();

        session_regenerate_id(true);

        // Autenticação de cliente: não cria nenhuma permissão de admin.
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_last_active'] = time();
        $_SESSION['csrf'] = bin2hex(random_bytes(32));

        redirect('index.html');
    } catch (Throwable $err) {
        try {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
        } catch (Throwable $ignored) {
        }

        $error = $err instanceof DomainException
            ? $err->getMessage()
            : 'Não foi possível entrar. Confira a conexão com o banco.';

        if (!($err instanceof DomainException)) {
            error_log((string) $err);
        }
    }
}

$loggedIn = !empty($_SESSION['user_id']);

if ($loggedIn) {
    $_SESSION['user_last_active'] = time();
}

$emailValue = is_string($_POST['email'] ?? null)
    ? $_POST['email']
    : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Minha conta — Agrolink</title>
    <link rel="stylesheet" href="conta.css">
</head>
<body>
    <main class="conta-card">
        <a class="conta-marca" href="index.html">AGROLINK</a>

        <?php if ($error): ?>
            <p class="conta-erro" role="alert"><?= e($error) ?></p>
        <?php endif; ?>

        <?php if ($loggedIn): ?>
            <h1>Olá, <?= e($_SESSION['user_name'] ?? '') ?>!</h1>

            <p>Você está conectado à sua conta de cliente.</p>

            <a class="conta-botao" href="index.html">
                Continuar comprando
            </a>

            <form method="post">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="logout">

                <button class="conta-sair" type="submit">
                    Sair da minha conta
                </button>
            </form>
        <?php else: ?>
            <h1>Entre na sua conta</h1>

            <p>Use o e-mail e a senha do seu cadastro no Agrolink.</p>

            <form method="post">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="login">

                <label>
                    E-mail
                    <input
                        type="email"
                        name="email"
                        autocomplete="username"
                        maxlength="160"
                        value="<?= e($emailValue) ?>"
                        required
                    >
                </label>

                <label>
                    Senha
                    <input
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        maxlength="72"
                        required
                    >
                </label>

                <button class="conta-botao" type="submit">
                    Entrar
                </button>
            </form>

            <p>
                Ainda não tem cadastro?
                <a href="index.html">Volte à loja e clique em Cadastre-se.</a>
            </p>
        <?php endif; ?>
    </main>
</body>
</html>