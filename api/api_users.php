<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json; charset=UTF-8");
session_start();

// 1. Kiểm tra quyền Admin mới được tạo nhân viên
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Bạn không có thẩm quyền thực hiện thao tác này!"]);
    exit();
}

require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/services/MailService.php';

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// --- LẤY DANH SÁCH NHÂN VIÊN ---
if ($method === 'GET') {
    $query = "SELECT id, username, full_name, email, phone, role, status FROM users WHERE is_deleted = 0 ORDER BY id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $users = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $users[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $users]);
    exit();
}

// --- THÊM MỚI NHÂN VIÊN + GỬI MAIL TỰ ĐỘNG ---
if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $username = trim($data['username']);
    $full_name = trim($data['full_name']);
    $email = trim($data['email']);
    $phone = trim($data['phone']);
    $role = $data['role'];

    if (empty($username) || empty($full_name) || empty($email)) {
        echo json_encode(["status" => "error", "message" => "Vui lòng nhập đầy đủ các trường bắt buộc!"]);
        exit();
    }

    // Kiểm tra trùng lặp tài khoản hoặc email
    $check_query = "SELECT COUNT(*) FROM users WHERE username = :username OR email = :email";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([':username' => $username, ':email' => $email]);
    if ($check_stmt->fetchColumn() > 0) {
        echo json_encode(["status" => "error", "message" => "Tên tài khoản hoặc Email này đã tồn tại trên hệ thống!"]);
        exit();
    }

    // Sinh mật khẩu khởi tạo ngẫu nhiên gồm 8 ký tự cho nhân viên
    $plain_password = bin2hex(random_bytes(4)); 
    // Mã hóa bảo mật cao chuẩn PHP trước khi ném vào DB
    $hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

    // Chèn dữ liệu vào bảng users
    $query = "INSERT INTO users (username, password, full_name, email, phone, role, status) 
              VALUES (:username, :password, :full_name, :email, :phone, :role, 1)";
    
    $stmt = $db->prepare($query);
    $insert_success = $stmt->execute([
        ':username' => $username,
        ':password' => $hashed_password,
        ':full_name' => $full_name,
        ':email' => $email,
        ':phone' => $phone,
        ':role' => $role
    ]);

    if ($insert_success) {
        // Kích hoạt tiến trình gửi mail bằng Mail Hosting
        $mailer = new MailService();
        $mail_sent = $mailer->sendWelcomeEmail($email, $full_name, $username, $plain_password);

        if ($mail_sent) {
            echo json_encode([
                "status" => "success", 
                "message" => "Tạo nhân viên thành công! Mật khẩu khởi tạo đã được gửi tới email của nhân sự."
            ]);
        } else {
            echo json_encode([
                "status" => "success", 
                "message" => "Tạo tài khoản thành công nhưng hệ thống Mail Hosting bị nghẽn, hãy cấp mật khẩu thủ công: " . $plain_password
            ]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Lỗi lưu trữ dữ liệu Database!"]);
    }
    exit();
}