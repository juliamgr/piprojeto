<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

const DATA_DIR = __DIR__ . '/../data';

function respond(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function require_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        header('Allow: ' . $method);
        respond(['success' => false, 'message' => 'Método não permitido.'], 405);
    }
}

function request_json(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);
    if (!is_array($data)) {
        respond(['success' => false, 'message' => 'JSON inválido.'], 400);
    }
    return $data;
}

function read_json(string $filename): array
{
    $path = DATA_DIR . '/' . $filename;
    $handle = fopen($path, 'rb');
    if (!$handle) respond(['success' => false, 'message' => 'Falha ao ler os dados.'], 500);
    flock($handle, LOCK_SH);
    $contents = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    $data = json_decode($contents ?: '[]', true);
    return is_array($data) ? $data : [];
}

function update_json(string $filename, callable $callback): mixed
{
    $path = DATA_DIR . '/' . $filename;
    $handle = fopen($path, 'c+');
    if (!$handle || !flock($handle, LOCK_EX)) {
        respond(['success' => false, 'message' => 'Falha ao salvar os dados.'], 500);
    }
    rewind($handle);
    $data = json_decode(stream_get_contents($handle) ?: '[]', true);
    if (!is_array($data)) $data = [];
    $result = $callback($data);
    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    return $result;
}

function clean_text(mixed $value, int $max): string
{
    return mb_substr(trim((string)$value), 0, $max);
}
