<?php
// api/api_products.php
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json; charset=UTF-8");
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Chặn truy cập hợp lệ!"]);
    exit();
}

require_once '../config/Database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"), true);

if (
    !empty($data['sku']) &&
    !empty($data['name']) &&
    !empty($data['category_id']) &&
    isset($data['stock'])
) {
    // Sửa cột lưu thành image_url theo đúng CSDL của em
    $query = "INSERT INTO products 
              SET sku = :sku, 
                  product_name = :name, 
                  category_id = :category_id, 
                  brand = :brand,
                  price = :price_in, 
                  price_out = :price_out,
                  stock_quantity = :stock, 
                  min_stock = :min_alert,
                  unit = :unit, 
                  location = :location, 
                  mfg_date = :mfg_date,
                  exp_date = :exp_date,
                  image_url = :image_url,
                  description = :description";

    try {
        $stmt = $db->prepare($query);

        $sku = htmlspecialchars(strip_tags($data['sku']));
        $name = htmlspecialchars(strip_tags($data['name']));
        $category_id = (int)$data['category_id'];
        $brand = htmlspecialchars(strip_tags($data['brand'] ?? ''));
        $price_in = (float)$data['price_in'];
        $price_out = (float)$data['price_out'];
        $stock = (int)$data['stock'];
        $min_alert = (int)($data['min_alert'] ?? 5);
        $unit = htmlspecialchars(strip_tags($data['unit'] ?? 'Cái'));
        $location = htmlspecialchars(strip_tags($data['location'] ?? ''));
        $description = htmlspecialchars(strip_tags($data['description'] ?? ''));
        $image_url = htmlspecialchars(strip_tags($data['image_url'] ?? 'default.png'));

        $mfg_date = !empty($data['mfg_date']) ? $data['mfg_date'] : null;
        $exp_date = !empty($data['exp_date']) ? $data['exp_date'] : null;

        $stmt->bindParam(":sku", $sku);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":brand", $brand);
        $stmt->bindParam(":price_in", $price_in);
        $stmt->bindParam(":price_out", $price_out);
        $stmt->bindParam(":stock", $stock);
        $stmt->bindParam(":min_alert", $min_alert);
        $stmt->bindParam(":unit", $unit);
        $stmt->bindParam(":location", $location);
        $stmt->bindParam(":mfg_date", $mfg_date, $mfg_date ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(":exp_date", $exp_date, $exp_date ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(":image_url", $image_url);
        $stmt->bindParam(":description", $description);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Nhập kho thành công!"]);
            exit();
        } else {
            echo json_encode(["status" => "error", "message" => "Không thể lưu sản phẩm."]);
            exit();
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Lỗi hệ thống: " . $e->getMessage()]);
        exit();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Thiếu thông tin bắt buộc!"]);
    exit();
}