<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/db.php";

$input = json_decode(file_get_contents("php://input"), true) ?? [];

$email = trim(strtolower($input["email"] ?? ""));
$password = (string) ($input["password"] ?? "");

if (!$email || !$password) {
    http_response_code(422);
    echo json_encode(["message" => "Informe e-mail e senha."]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, name, email, phone, password_hash FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user["password_hash"])) {
    http_response_code(401);
    echo json_encode(["message" => "E-mail ou senha incorretos."]);
    exit;
}

$_SESSION["user_id"] = $user["id"];

echo json_encode([
    "message" => "Login realizado com sucesso.",
    "user" => [
        "id" => $user["id"],
        "name" => $user["name"],
        "email" => $user["email"],
        "phone" => $user["phone"],
    ],
]);
