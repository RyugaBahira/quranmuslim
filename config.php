<?php
$host = "sql303.byethost32.com";
$username = "b32_38508324"; // Change if necessary
$password = "$#b46ussbyethost20#$"; // Change if necessary
$dbname = "b32_38508324_quranmuslim";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}
?>