<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Database connection
require "config.php";

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
$path = explode("/", trim($_SERVER['PATH_INFO'] ?? '', "/"));

if ($method === 'GET' && $path[0] === "users") {
    // GET ALL USERS
    $stmt = $pdo->query("SELECT token, name, online_status, user_type, app_version, last_seen, device_os, profile_image, about_status, email, aktifitas FROM user_list");
    $recx = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "data" => $recx]);
} elseif ($method === 'POST' && $path[0] === "users") {
    // INSERT NEW USER
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['token'], $data['name'], $data['email'])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO user_list (token, name, online_status, user_type, app_version, last_seen, device_os, profile_image, about_status, email, aktifitas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([
        $data['token'], $data['name'], $data['online_status'] ?? null, $data['user_type'] ?? null,
        $data['app_version'] ?? null, $data['last_seen'] ?? null, $data['device_os'] ?? '',
        $data['profile_image'] ?? '', $data['about_status'] ?? '', $data['email'], $data['aktifitas'] ?? ''
    ]);

    if ($result) {
        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "User created"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to create user"]);
    }
} elseif ($method === 'PUT' && $path[0] === "users" && isset($path[1])) {
    // UPDATE USER
    $token = $path[1];
    $data = json_decode(file_get_contents("php://input"), true);

    $fields = [];
    $values = [];
    foreach ($data as $key => $value) {
        $fields[] = "$key = ?";
        $values[] = $value;
    }
    $values[] = $token;

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "No fields to update"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE user_list SET " . implode(", ", $fields) . " WHERE token = ?");
    $result = $stmt->execute($values);

    if ($result) {
        echo json_encode(["status" => "success", "message" => "User updated"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to update user"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}
?>
