<?php
declare(strict_types=1);

require __DIR__ . '/BACKEND/app/bootstrap.php';

require __DIR__ . '/BACKEND/app/profile.php';
$error = '';
$account = null;
try { $account = current_customer(); } catch (Throwable $err) {
    error_log((string)$err);
    $error = 'Não foi possível carregar sua conta. Tente novamente.';
}

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

        if (($_POST['action'] ?? '') === 'profile') {
            update_customer($_POST);
            $_SESSION['profile_saved'] = true;
            redirect('conta.php');
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

        redirect('conta.php');
    } catch (Throwable $err) {
        try {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
        } catch (Throwable $ignored) {
        }

        $error = $err instanceof DomainException
            ? $err->getMessage()
            : 'Não foi possível concluir a operação. Tente novamente.';

        if (!($err instanceof DomainException)) {
            error_log((string) $err);
        }
    }
}

$loggedIn = $account !== null;
$saved = !empty($_SESSION['profile_saved']);
unset($_SESSION['profile_saved']);
$profile = $account ?? [];
if ($loggedIn && $error && ($_POST['action'] ?? '') === 'profile') {
    foreach (array_keys($profile) as $key) {
        if ($key !== 'id' && isset($_POST[$key]) && is_string($_POST[$key])) $profile[$key] = $_POST[$key];
    }
}

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
    <main class="conta-card <?= $loggedIn ? 'conta-perfil' : '' ?>">
        <a class="conta-marca" href="index.html">AGROLINK</a>

        <?php if ($error): ?>
            <p class="conta-erro" role="alert"><?= e($error) ?></p>
        <?php endif; ?>

        <?php if ($loggedIn): ?>
            <h1>Olá, <?= e($_SESSION['user_name'] ?? '') ?>!</h1>

            <a class="conta-voltar" href="pedidos.php"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6Z"/><path d="M9 3v15M15 6v15"/></svg> Acompanhar minhas entregas</a>
            <p>Atualize seus dados. Eles ficam salvos na sua conta e preenchem sua próxima compra.</p>
            <?php if ($saved): ?><p class="conta-sucesso" role="status">Informações salvas com sucesso!</p><?php endif; ?>
            <form method="post" class="perfil-form">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="profile">
                <div class="perfil-grid">
                <label>Nome completo *<input name="name" type="text" maxlength="90" autocomplete="name" value="<?= e($profile['name'] ?? '') ?>" required></label>
                <label>E-mail *<input name="email" type="email" maxlength="160" autocomplete="email" value="<?= e($profile['email'] ?? '') ?>" required></label>
                <label>Telefone / WhatsApp *<input name="phone" type="tel" maxlength="30" autocomplete="tel" value="<?= e($profile['phone'] ?? '') ?>" required></label>
                <label>CPF<input name="cpf" type="text" maxlength="14" autocomplete="off" value="<?= e($profile['cpf'] ?? '') ?>" ></label>
                <label>Data de nascimento<input name="birth_date" type="date" maxlength="10" autocomplete="bday" value="<?= e($profile['birth_date'] ?? '') ?>" ></label>
                <label>Município *<input name="city" type="text" maxlength="100" autocomplete="address-level2" value="<?= e($profile['city'] ?? '') ?>" required></label>
                <label>Rua, número e complemento *<input name="address" type="text" maxlength="220" autocomplete="street-address" value="<?= e($profile['address'] ?? '') ?>" required></label>
                <label>Bairro<input name="neighborhood" type="text" maxlength="100" autocomplete="address-level3" value="<?= e($profile['neighborhood'] ?? '') ?>" ></label>
                <label>CEP<input name="zip_code" type="text" maxlength="9" autocomplete="postal-code" value="<?= e($profile['zip_code'] ?? '') ?>" ></label>
                <label>Tipo de filiação *<select name="member_type" required>
                <?php foreach (['Produtor(a) rural','Agricultor(a) familiar','Consumidor(a)','Organização / entidade','Outro'] as $type): ?>
                    <option value="<?= e($type) ?>" <?= ($profile['member_type'] ?? '') === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                <?php endforeach; ?>
                </select></label>
                </div>
                <label>O que você produz ou deseja oferecer?<textarea name="production" rows="2" maxlength="4000"><?= e($profile['production'] ?? '') ?></textarea></label>
                <label>Observações<textarea name="notes" rows="2" maxlength="4000"><?= e($profile['notes'] ?? '') ?></textarea></label>
                <label>Senha atual (somente para alterar o e-mail)<input type="password" name="current_password" autocomplete="current-password" maxlength="72" aria-describedby="email-hint"></label>
                <p id="email-hint">Se mudar seu e-mail, use o novo endereço no próximo login. Sua senha permanece a mesma.</p>
                <button class="conta-botao" type="submit">Salvar alterações</button>
            </form>
            <a class="conta-voltar" href="index.html">← Voltar para a feira e minha sacola</a>

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