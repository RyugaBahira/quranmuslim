<?php
$host     = "127.0.0.1";
$username = "baguss";            // Change if necessary
$password = "b46usscodespace20"; // Change if necessary
$dbname   = "db_quranmuslim";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}
