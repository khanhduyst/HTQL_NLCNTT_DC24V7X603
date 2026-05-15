<?php
header("Content-Type: text/html; charset=UTF-8");

// 1. Giả lập mật khẩu em muốn dùng
$password_nhap_vao = "admin123";

// 2. Tạo một chuỗi Hash mới tinh từ mật khẩu đó
$hash_moi_tao = password_hash($password_nhap_vao, PASSWORD_DEFAULT);

echo "<h3>1. Kiểm tra tính năng Hash của Server</h3>";
echo "Mật khẩu gốc: <b>$password_nhap_vao</b><br>";
echo "Chuỗi Hash mới tạo: <code style='background:#eee; padding:2px 5px;'>$hash_moi_tao</code><br>";
echo "Độ dài chuỗi Hash: " . strlen($hash_moi_tao) . " ký tự<br>";

// 3. Thử Verify ngay lập tức với chuỗi vừa tạo
if (password_verify($password_nhap_vao, $hash_moi_tao)) {
    echo "<b style='color:green'>=> KẾT QUẢ: Hàm password_verify hoạt động BÌNH THƯỜNG trên server này.</b><br>";
} else {
    echo "<b style='color:red'>=> KẾT QUẢ: Hàm password_verify bị LỖI trên server này (Hiếm gặp).</b><br>";
}

echo "<hr>";

// 4. Kiểm tra dữ liệu từ Database (Em điền thủ công chuỗi đang có trong Aiven vào đây)
$hash_dang_co_trong_db = '$2y$10$7R7iIOW.6pX9W2zS9S3P/.fH9Wq9VvG9k9G9k9G9k9G9k9G9k9G9k'; // Thay bằng chuỗi trong Aiven của em

echo "<h3>2. Đối soát với Database</h3>";
echo "Hash đang lấy từ DB: <code style='background:#eee; padding:2px 5px;'>$hash_dang_co_trong_db</code><br>";

if (password_verify($password_nhap_vao, $hash_dang_co_trong_db)) {
    echo "<b style='color:green'>=> ĐỐI SOÁT: Khớp! Nếu chạy file này ok mà đăng nhập lỗi thì do dữ liệu gửi từ JS hoặc Model fetch sai.</b>";
} else {
    echo "<b style='color:red'>=> ĐỐI SOÁT: Không khớp! Chuỗi trong DB của em đang bị sai hoặc có ký tự lạ/khoảng trắng.</b>";
}