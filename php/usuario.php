<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/db.php";

if (empty($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["message" => "Não autenticado."]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, name, email, phone, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch();

if (!$user) {
    unset($_SESSION["user_id"]);
    http_response_code(401);
    echo json_encode(["message" => "Sessão expirada."]);
    exit;
}

echo json_encode(["user" => $user]);
