<?php
error_reporting(0);
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Từ chối truy cập!");
}

require_once dirname(__DIR__) . '/config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Lấy toàn bộ tham số lọc để xuất đúng file Excel người dùng đang tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

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
    if ($status === 'instock') $conditions[] = "p.stock_quantity > 10";
    elseif ($status === 'low') $conditions[] = "p.stock_quantity > 0 AND p.stock_quantity <= 10";
    elseif ($status === 'out') $conditions[] = "p.stock_quantity = 0";
}

$where_sql = implode(" AND ", $conditions);

$query = "SELECT p.sku, p.product_name, c.category_name, p.brand, p.location, p.price, p.stock_quantity, p.unit
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          WHERE $where_sql
          ORDER BY p.sku DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();

// Ép trình duyệt nhận diện luồng dữ liệu này là một File Excel (.xls)
$filename = "Bao_Cao_Kho_Hang_" . date('Y-m-d') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Xuất bảng HTML Header - Excel tự động chuyển bảng này thành các ô tính gọn gàng
echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" />';
echo '<table border="1">';
echo '<tr><th colspan="9" style="font-size:16px; font-weight:bold; background-color:#4361ee; color:#ffffff; height:40px;">BÁO CÁO CHI TIẾT HÀNG HÓA TỒN KHO</th></tr>';
echo '<tr>
        <th style="background-color:#1f2937; color:#ffffff;">STT</th>
        <th style="background-color:#1f2937; color:#ffffff;">Mã SKU</th>
        <th style="background-color:#1f2937; color:#ffffff;">Tên Sản Phẩm</th>
        <th style="background-color:#1f2937; color:#ffffff;">Danh Mục</th>
        <th style="background-color:#1f2937; color:#ffffff;">Thương Hiệu</th>
        <th style="background-color:#1f2937; color:#ffffff;">Vị Trí Kho</th>
        <th style="background-color:#1f2937; color:#ffffff;">Giá Nhập (VND)</th>
        <th style="background-color:#1f2937; color:#ffffff;">Số Lượng</th>
        <th style="background-color:#1f2937; color:#ffffff;">Đơn Vị Tính</th>
      </tr>';

$stt = 1;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo '<tr>';
    echo '<td align="center">' . $stt++ . '</td>';
    echo '<td align="center">' . $row['sku'] . '</td>';
    echo '<td>' . $row['product_name'] . '</td>';
    echo '<td>' . $row['category_name'] . '</td>';
    echo '<td>' . ($row['brand'] ? $row['brand'] : '-') . '</td>';
    echo '<td align="center">' . ($row['location'] ? $row['location'] : '-') . '</td>';
    echo '<td align="right">' . number_format($row['price'], 0, '', '') . '</td>';
    echo '<td align="right">' . $row['stock_quantity'] . '</td>';
    echo '<td align="center">' . $row['unit'] . '</td>';
    echo '</tr>';
}
echo '</table>';
exit();