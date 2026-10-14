<?php
declare(strict_types=1);
date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', '0');
$config = require __DIR__ . '/config.php';
if (PHP_SAPI !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header('Cache-Control: no-store');
    session_name('agrolink_session');
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params(['httponly'=>true, 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite'=>'Lax', 'path'=>'/']);
    session_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
function db(): PDO {
    global $config;
    static $pdo;
    if (!$pdo) { $pdo = new PDO($config['dsn'], $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES=>false,
    ]);
        $pdo->exec("SET time_zone = '-03:00'");
    }
    return $pdo;
}
function query(string $sql, array $params=[]): PDOStatement {
    $stmt = db()->prepare($sql); $stmt->execute($params); return $stmt;
}
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function brl($cents): string { return 'R$ ' . number_format((int)$cents / 100, 2, ',', '.'); }
function csrf_field(): void { echo '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">'; }
function csrf_check(): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) throw new DomainException('Sua sessão expirou. Recarregue a página e tente novamente.');
}
function json_out(array $data, int $status=200): void {
    http_response_code($status); header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit;
}
function api_run(string $method, callable $handler): void {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== $method) { header('Allow: '.$method); json_out(['message'=>'Método não permitido.'],405); }
        if ($method !== 'GET') csrf_check();
        $handler();
    } catch (DomainException $err) {
        try { if (db()->inTransaction()) db()->rollBack(); } catch (Throwable $ignored) {}
        json_out(['message'=>$err->getMessage()],422);
    } catch (Throwable $err) {
        try { if (db()->inTransaction()) db()->rollBack(); } catch (Throwable $ignored) {}
        error_log((string)$err); json_out(['message'=>'Serviço indisponível. Confira a configuração do banco ou tente novamente.'],500);
    }
}
function input_json(): array {
    if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) throw new DomainException('Envie JSON.');
    $raw = file_get_contents('php://input', false, null, 0, 32769);
    if (strlen($raw)>32768) throw new DomainException('Solicitação muito grande.');
    try { $data = json_decode($raw, true, 32, JSON_THROW_ON_ERROR); }
    catch (JsonException $err) { throw new DomainException('JSON inválido.'); }
    if (!is_array($data)) throw new DomainException('Dados inválidos.');
    return $data;
}
function field(array $data, string $key, int $min, int $max): string {
    if (!isset($data[$key]) || !is_string($data[$key])) throw new DomainException('Confira o campo: '.$key.'.');
    $s = trim($data[$key]);
    if (strlen($s)<$min || strlen($s)>$max) throw new DomainException('Confira o tamanho do campo: '.$key.'.');
    return $s;
}
function integer($value, int $min, int $max): int {
    if (is_bool($value) || filter_var($value, FILTER_VALIDATE_INT) === false || $value<$min || $value>$max) throw new DomainException('Quantidade ou identificador inválido.');
    return (int)$value;
}
function require_admin(): void {
    if (empty($_SESSION['admin_id']) || time()-($_SESSION['last_active'] ?? 0)>1800) {
        unset($_SESSION['admin_id']); header('Location: login.php'); exit;
    }
    $_SESSION['last_active'] = time();
}
function flash(string $message, string $kind='success'): void { $_SESSION['flash'] = [$message,$kind]; }
function redirect(string $to): void { header('Location: '.$to, true,303); exit; }
function statuses(): array { return ['recebido'=>'Recebido','confirmado'=>'Confirmado','separacao'=>'Em separação','enviado'=>'Enviado','concluido'=>'Concluído','cancelado'=>'Cancelado']; }
function next_statuses(string $status): array {
    return ['recebido'=>['confirmado','cancelado'],'confirmado'=>['separacao','cancelado'],'separacao'=>['enviado','cancelado'],'enviado'=>['concluido'],'concluido'=>[],'cancelado'=>[]][$status] ?? [];
}
