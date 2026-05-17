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

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // 1. Đón nhận các tham số lọc gửi từ Javascript lên qua URL
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;

    $limit = 5; // Số dòng trên 1 trang
    $offset = ($page - 1) * $limit;

    // 2. Xây dựng câu lệnh SQL lọc động thuần PHP
    $conditions = ["p.is_deleted = 0"];
    $params = [];

    if ($search !== '') {
        $conditions[] = "(p.product_name LIKE :search_name OR p.sku LIKE :search_sku)";
        $params[':search_name'] = "%$search%";
        $params[':search_sku'] = "%$search%";
    }

    if ($category !== '') {
        $conditions[] = "c.category_name = :category";
        $params[':category'] = $category;
    }

    if ($status !== '') {
        if ($status === 'instock') {
            $conditions[] = "p.stock_quantity > 10";
        } elseif ($status === 'low') {
            $conditions[] = "p.stock_quantity > 0 AND p.stock_quantity <= 10";
        } elseif ($status === 'out') {
            $conditions[] = "p.stock_quantity = 0";
        }
    }

    $where_sql = implode(" AND ", $conditions);

    // 3. Đếm tổng số dòng sau khi lọc để tính tổng số trang
    $count_query = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $where_sql";
    $count_stmt = $db->prepare($count_query);
    foreach ($params as $key => $val) {
        $count_stmt->bindValue($key, $val);
    }
    $count_stmt->execute();
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $limit);

    // 4. Lấy dữ liệu phân trang bằng LIMIT và OFFSET trong SQL
    $query = "SELECT p.sku, p.product_name AS name, c.category_name AS cat, 
                     p.price, p.stock_quantity AS stock, p.image_url, p.brand, 
                     p.price_out, p.unit, p.location, p.mfg_date, p.exp_date, p.description, p.specification
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.id
              WHERE $where_sql
              ORDER BY p.sku DESC
              LIMIT :limit OFFSET :offset";

    $stmt = $db->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $products_arr = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $products_arr[] = [
            "sku" => $sku,
            "name" => $name,
            "cat" => $cat,
            "price" => number_format($price, 0, ',', '.'),
            "price_out" => $price_out,
            "stock" => $stock,
            "image_url" => $image_url,
            "brand" => $brand,
            "unit" => $unit,
            "location" => $location,
            "mfg_date" => $mfg_date,
            "exp_date" => $exp_date,
            "description" => $description,
            "specification" => $specification,
            "status" => ($stock > 10) ? "instock" : (($stock > 0) ? "low" : "out"),
            "label" => ($stock > 10) ? "Còn hàng" : (($stock > 0) ? "Sắp hết" : "Hết hàng"),
            "color" => ($stock > 10) ? "#10b981" : (($stock > 0) ? "#f59e0b" : "#ef4444")
        ];
    }

    // Trả về kết quả phân trang cho giao diện nhận diện
    echo json_encode([
        "status" => "success",
        "data" => $products_arr,
        "total_pages" => $total_pages,
        "current_page" => $page,
        "total_items" => $total_items
    ]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    require_once dirname(__DIR__) . '/controller/ProductController.php';
    (new ProductController($db))->handlePost($data);
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    require_once dirname(__DIR__) . '/controller/ProductController.php';
    (new ProductController($db))->handlePut($data);
} elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    require_once dirname(__DIR__) . '/controller/ProductController.php';
    (new ProductController($db))->handleDelete($data);
}
