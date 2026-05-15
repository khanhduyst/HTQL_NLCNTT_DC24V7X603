<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập hệ thống - ERP Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    body {
        background: #f4f7fe;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Public Sans', sans-serif;
    }

    .login-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        width: 100%;
        max-width: 420px;
        background: #fff;
    }

    .login-header {
        background: linear-gradient(45deg, #4361ee, #4cc9f0);
        padding: 50px 20px;
        text-align: center;
        color: white;
    }

    .form-label {
        font-size: 13px;
        color: #697a8d;
    }

    .form-control {
        border-radius: 10px;
        padding: 12px 15px;
        border: 1px solid #d9dee3;
        transition: all 0.2s;
    }

    .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.1);
        border-color: #4361ee;
    }

    .btn-login {
        background: #4361ee;
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-weight: 700;
        font-size: 15px;
        transition: all 0.3s;
        color: white;
    }

    .btn-login:hover {
        background: #374fc7;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }

    .input-group-text {
        border-radius: 10px 0 0 10px;
        background: transparent;
        border-right: none;
        color: #a1acb8;
    }

    .form-control.border-start-0 {
        border-radius: 0 10px 10px 0;
    }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-shield-halved fa-4x mb-3"></i>
            <h3 class="fw-bold mb-1">ERP Pro System</h3>
            <p class="small mb-0 opacity-75">Hệ thống quản trị doanh nghiệp nội bộ</p>
        </div>
        <div class="card-body p-4 p-md-5">
            <form id="login-form">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tài khoản</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-circle"></i></span>
                        <input type="text" id="login-id" class="form-control border-start-0 shadow-none"
                            placeholder="Nhập mã NV (ví dụ: admin)" required>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label class="form-label fw-bold">Mật khẩu</label>
                        <a href="#" class="text-decoration-none small text-primary fw-bold">Quên?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" id="login-pass" class="form-control border-start-0 shadow-none"
                            placeholder="" required>
                    </div>
                </div>
                <button type="submit" id="btn-submit-login" class="btn btn-primary w-100 btn-login mb-4">
                    ĐĂNG NHẬP NGAY
                </button>
                <div class="text-center border-top pt-3">
                    <p class="small text-muted mb-0">Hỗ trợ: <span class="fw-bold">0354 123 456</span></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/login.js"></script>
</body>

</html>