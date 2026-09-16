<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/db_favoritos.php";

if (empty($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["message" => "Entre na sua conta para favoritar produtos."]);
    exit;
}

$userId = (int) $_SESSION["user_id"];

function listarFavoritos(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id = ?");
    $stmt->execute([$userId]);
    return array_map("intval", $stmt->fetchAll(PDO::FETCH_COLUMN));
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["favorites" => listarFavoritos($pdoFav, $userId)]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true) ?? [];
$productId = (int) ($input["productId"] ?? 0);

if ($productId <= 0) {
    http_response_code(422);
    echo json_encode(["message" => "Produto inválido."]);
    exit;
}

$stmt = $pdoFav->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
$stmt->execute([$userId, $productId]);
$existente = $stmt->fetch();

if ($existente) {
    $stmt = $pdoFav->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $favorited = false;
} else {
    $stmt = $pdoFav->prepare("INSERT INTO favorites (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$userId, $productId]);
    $favorited = true;
}

echo json_encode([
    "favorited" => $favorited,
    "favorites" => listarFavoritos($pdoFav, $userId),
]);