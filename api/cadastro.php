<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/db.php";

$input = json_decode(file_get_contents("php://input"), true) ?? [];

$name = trim($input["name"] ?? "");
$email = trim(strtolower($input["email"] ?? ""));
$phone = trim($input["phone"] ?? "");
$password = (string) ($input["password"] ?? "");
$passwordConfirm = (string) ($input["passwordConfirm"] ?? "");

if (mb_strlen($name) < 2) {
    http_response_code(422);
    echo json_encode(["message" => "Informe seu nome completo."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(["message" => "Informe um e-mail válido."]);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(422);
    echo json_encode(["message" => "A senha precisa ter ao menos 6 caracteres."]);
    exit;
}

if ($password !== $passwordConfirm) {
    http_response_code(422);
    echo json_encode(["message" => "As senhas não coincidem."]);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(["message" => "Já existe uma conta cadastrada com este e-mail."]);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "INSERT INTO users (name, email, phone, password_hash, created_at) VALUES (?, ?, ?, ?, NOW())"
);
$stmt->execute([$name, $email, $phone, $hash]);

$userId = (int) $pdo->lastInsertId();

$_SESSION["user_id"] = $userId;

echo json_encode([
    "message" => "Conta criada com sucesso.",
    "user" => [
        "id" => $userId,
        "name" => $name,
        "email" => $email,
        "phone" => $phone,
    ],
]);
