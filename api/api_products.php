<?php

error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json; charset=UTF-8");
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Chặn truy cập!"]);
    exit();
}

require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/controller/ProductController.php';

$database = new Database();
$db = $database->getConnection();

$controller = new ProductController($db);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $controller->handleGet();
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $controller->handlePost($data);
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $controller->handlePut($data);
}
elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $controller->handleDelete($data);
}