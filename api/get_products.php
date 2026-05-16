<?php
// api/get_products.php
header("Content-Type: application/json; charset=UTF-8");
session_start();

// Kiểm tra quyền đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Chặn truy cập hợp lệ!"]);
    exit();
}

require_once '../config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Dùng INNER JOIN để lấy cột category_name từ bảng categories thông qua category_id
$query = "SELECT 
            p.sku, 
            p.product_name AS name, 
            c.category_name AS cat, 
            p.price, 
            p.stock_quantity AS stock
          FROM products p
          INNER JOIN categories c ON p.category_id = c.id
          ORDER BY p.id DESC";

try {
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $products_arr = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Tự động tính toán status và label dựa trên dữ liệu tồn kho thật (stock_quantity)
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

        // Định nghĩa các icon và màu sắc động theo Danh mục dựa trên giao diện của em
        $icon = "📦";
        $color = "#7c3aed"; // Màu mặc định (Phụ kiện / Khác)
        
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
            "price" => number_format($row['price'], 0, ',', '.'), // Đổi từ decimal 45000000.00 sang chuỗi hiển thị "45.000.000"
            "stock" => $stockInt,
            "status" => $status,
            "label" => $label,
            "icon" => $icon,
            "color" => $color
        ];

        array_push($products_arr, $product_item);
    }

    echo json_encode(["status" => "success", "data" => $products_arr]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Lỗi truy vấn: " . $e->getMessage()]);
}