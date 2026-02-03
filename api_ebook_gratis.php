<?php
/**
 * REST API untuk Manajemen Ebook dengan Basic Authentication
 * 
 * Fitur:
 * - Mendukung GET, POST, PUT, DELETE
 * - Basic Auth untuk semua endpoint
 * - Validasi duplikat judul
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// === Konfigurasi Auth ===
$env = require __DIR__ . '/image/env.php';
define('AUTH_USERNAME', getenv('AUTH_USERNAME') ?: '');
define('AUTH_PASSWORD', getenv('AUTH_PASSWORD') ?: '');

// === File data ===
$dataFile = 'ebook_gratis.json';

// === Fungsi dasar ===
function authenticate() {
    return isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) 
        && $_SERVER['PHP_AUTH_USER'] === AUTH_USERNAME 
        && $_SERVER['PHP_AUTH_PW'] === AUTH_PASSWORD;
}
function sendAuthError() {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Autentikasi gagal. Username atau password salah.']);
    exit();
}
function readData($file) {
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?: [];
}
function writeData($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}
function sendResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

// === Auth check ===
if (!authenticate()) sendAuthError();

// === Routing berdasarkan action ===
$action = $_GET['action'] ?? '';
$data = readData($dataFile);

switch ($action) {
    case 'get':
        sendResponse(['success' => true, 'data' => $data]);
        break;

    case 'post':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty(trim($input['title'] ?? ''))) {
            sendResponse(['success' => false, 'message' => 'Field title wajib diisi'], 400);
        }

        // Cek duplikat judul
        foreach ($data as $ebook) {
            if (strtolower($ebook['title']) === strtolower($input['title'])) {
                sendResponse(['success' => false, 'message' => 'Judul sudah ada'], 409);
            }
        }

        $newId = count($data) ? max(array_column($data, 'id')) + 1 : 1;
        $newEbook = [
            'id' => $newId,
            'url_desc' => htmlspecialchars($input['url_desc'] ?? ''),
            'title' => htmlspecialchars(trim($input['title'])),
            'url_img' => htmlspecialchars($input['url_img'] ?? ''),
            'url_pdf' => htmlspecialchars($input['url_pdf'] ?? ''),
            'createdAt' => time(),
            'updatedAt' => time()
        ];

        $data[] = $newEbook;
        writeData($dataFile, $data);
        sendResponse(['success' => true, 'message' => 'Ebook berhasil ditambahkan', 'data' => $newEbook], 201);
        break;

    case 'update':
        $id = $_GET['id'] ?? null;
        if (!$id) sendResponse(['success' => false, 'message' => 'ID wajib disertakan'], 400);

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) sendResponse(['success' => false, 'message' => 'Data tidak valid'], 400);

        $found = false;
        foreach ($data as $key => $ebook) {
            if ($ebook['id'] == $id) {
                $data[$key] = [
                    'id' => $ebook['id'],
                    'url_desc' => $input['url_desc'] ?? $ebook['url_desc'],
                    'title' => $input['title'] ?? $ebook['title'],
                    'url_img' => $input['url_img'] ?? $ebook['url_img'],
                    'url_pdf' => $input['url_pdf'] ?? $ebook['url_pdf'],
                    'createdAt' => $ebook['createdAt'],
                    'updatedAt' => time()
                ];
                $found = true;
                break;
            }
        }

        if (!$found) sendResponse(['success' => false, 'message' => 'Ebook tidak ditemukan'], 404);

        writeData($dataFile, $data);
        sendResponse(['success' => true, 'message' => 'Ebook berhasil diperbarui', 'data' => $data[$key]]);
        break;

    case 'delete':
        $id = $_GET['id'] ?? null;
        if (!$id) sendResponse(['success' => false, 'message' => 'ID wajib disertakan'], 400);

        $data = array_values(array_filter($data, fn($ebook) => $ebook['id'] != $id));
        writeData($dataFile, $data);
        sendResponse(['success' => true, 'message' => 'Ebook berhasil dihapus']);
        break;

    default:
        sendResponse(['success' => false, 'message' => 'Action tidak valid. Gunakan: get, post, update, atau delete'], 400);
}
?>