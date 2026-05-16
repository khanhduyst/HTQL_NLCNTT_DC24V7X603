<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Chặn truy cập hợp lệ!"]);
    exit();
}

require_once '../config/Database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT 
            p.sku, 
            p.product_name AS name, 
            c.category_name AS cat, 
            p.price, 
            p.stock_quantity AS stock,
            p.image_url
          FROM products p
          INNER JOIN categories c ON p.category_id = c.id
          ORDER BY p.id DESC";

try {
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $products_arr = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stockInt = (int)$row['stock'];
        $status = "ok";
        $label = "Còn hàng";
        
        if ($stockInt <= 0) {
            $status = "out";
            $label = "Hết hàng";
        } elseif ($stockInt <= 5) {
            $status = "low";
            $label = "Sắp hết hàng";
        }

        $icon = "📦";
        $color = "#7c3aed";
        
        if ($row['cat'] === 'Laptop') {
            $icon = "💻";
            $color = "#4361ee";
        } elseif ($row['cat'] === 'Điện thoại') {
            $icon = "📱";
            $color = "#10b981";
        } elseif ($row['cat'] === 'Máy tính bảng') {
            $icon = "📟";
            $color = "#f59e0b";
        }

        $product_item = [
            "sku" => $row['sku'],
            "name" => $row['name'],
            "cat" => $row['cat'],
            "price" => number_format($row['price'], 0, ',', '.'),
            "stock" => $stockInt,
            "status" => $status,
            "label" => $label,
            "icon" => $icon,
            "color" => $color,
            "image_url" => $row['image_url']
        ];

        array_push($products_arr, $product_item);
    }

    echo json_encode(["status" => "success", "data" => $products_arr]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Lỗi truy vấn: " . $e->getMessage()]);
}