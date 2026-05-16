<?php
require_once '../config/Database.php';
require_once '../models/Employee.php';

class AuthController
{
    public function login($username, $password)
    {
        $database = new Database();
        $db = $database->getConnection();
        $employee = new Employee($db);

        $user = $employee->findByUsername($username);

        if (!$user) {
            return ["status" => "error", "message" => "Tài khoản không tồn tại!"];
        }
    
        if (password_verify(trim($password), trim($user['password']))) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname']; 
            $_SESSION['role'] = $user['role'] ?? 'nhanvien'; 

            return ["status" => "success", "user_data" => $user];
        } else {
            return ["status" => "error", "message" => "Mật khẩu không chính xác!"];
        }
    }
}