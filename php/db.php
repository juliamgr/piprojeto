<?php
$DB_HOST = "localhost";
$DB_NAME = "agrolink";
$DB_USER = "root";
$DB_PASS = "";

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode(["message" => "Não foi possível conectar ao banco de dados. Verifique se o MySQL está ativo e se o banco 'agrolink' foi criado (veja sql/schema.sql)."]);
    exit;
}
