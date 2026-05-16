<?php
// api/categories.php
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json; charset=UTF-8");
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Chặn truy cập!"]);
    exit();
}

require_once '../config/Database.php';
$database = new Database();
$db = $database->getConnection();

// Lấy phương thức gửi dữ liệu từ phía giao diện (GET hoặc POST)
$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// THÀNH PHẦN 1: NẾU LÀ YÊU CẦU LẤY DỮ LIỆU (GET)
// ==========================================
if ($method === 'GET') {
    $query = "SELECT id, category_name AS name FROM categories ORDER BY id ASC";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute();
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $categories]);
        exit();
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        exit();
    }
}

// ==========================================
// THÀNH PHẦN 2: NẾU LÀ YÊU CẦU THÊM MỚI (POST)
// ==========================================
if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data['category_name'])) {
        $query = "INSERT INTO categories SET category_name = :name, description = :desc";
        try {
            $stmt = $db->prepare($query);
            $name = htmlspecialchars(strip_tags($data['category_name']));
            $desc = htmlspecialchars(strip_tags($data['description'] ?? ''));
            
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":desc", $desc);

            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Thêm danh mục thành công!"]);
                exit();
            } else {
                echo json_encode(["status" => "error", "message" => "Không thể lưu vào CSDL."]);
                exit();
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Danh mục này đã tồn tại rồi!"]);
            exit();
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Tên danh mục không được để trống!"]);
        exit();
    }
}