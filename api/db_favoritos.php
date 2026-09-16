<?php
$FAV_DB_HOST = "localhost";
$FAV_DB_NAME = "favoritos_agrolink";
$FAV_DB_USER = "root";
$FAV_DB_PASS = "";
 
try {
    $pdoFav = new PDO(
        "mysql:host={$FAV_DB_HOST};dbname={$FAV_DB_NAME};charset=utf8mb4",
        $FAV_DB_USER,
        $FAV_DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode(["message" => "Não foi possível conectar ao banco de favoritos. Verifique se o banco 'favoritos_agrolink' foi criado (veja sql/favoritos.sql)."]);
    exit;
}