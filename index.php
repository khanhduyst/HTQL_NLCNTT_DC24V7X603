<?php
session_start();

// Kiểm tra bảo mật: Nếu chưa đăng nhập thì đá về login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$fullname = $_SESSION['fullname'] ?? 'Admin Manager';
$role_session = $_SESSION['role'] ?? 'nhanvien';
$role_name = ($role_session === 'admin') ? 'Quản trị viên' : 'Nhân viên';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Quản Trị Nội Bộ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="https://crm.biglead.live/ic_biglead-yellow.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/global.css">
</head>

<body>

    <div class="main-wrapper">
        <nav id="main-sidebar" class="bg-dark text-white">
            <div class="sidebar-brand p-4">
                <h4 class="text-uppercase fw-bold mb-0">Company ERP</h4>
            </div>
            <ul class="nav flex-column px-3">
                <li class="nav-item mb-2">
                    <a href="javascript:void(0)" class="nav-link text-white active" onclick="navigate('dashboard')">
                        <i class="fas fa-th-large me-2"></i> Tổng quan
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="javascript:void(0)" class="nav-link text-white" onclick="navigate('analytics')">
                        <i class="menu-icon tf-icons fas fa-chart-bar"></i> Báo cáo & Thống kê
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="javascript:void(0)" class="nav-link text-white" onclick="navigate('customers')">
                        <i class="fas fa-user-friends me-2"></i> Khách hàng
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="javascript:void(0)" class="nav-link text-white" onclick="navigate('employees')">
                        <i class="fas fa-id-badge me-2"></i> Nhân viên
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="javascript:void(0)" class="nav-link text-white" onclick="navigate('warehouse')">
                        <i class="fas fa-warehouse me-2"></i> Quản lý kho
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="javascript:void(0)" class="nav-link text-white" onclick="navigate('orders')">
                        <i class="fas fa-shopping-cart me-2"></i> Đơn hàng
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="javascript:void(0)" class="nav-link text-white" onclick="navigate('accounting')">
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        <span>Công nợ</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="javascript:void(0)" class="nav-link text-white" onclick="navigate('settings')">
                        <i class="fas fa-cog me-2"></i>
                        <span>Cài đặt</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div id="main-body" class="w-100">
            <header class="navbar navbar-expand bg-white shadow-sm px-4 py-2">
                <button class="btn btn-light rounded-circle me-3" id="toggle-sidebar">
                    <i class="fas fa-bars text-secondary"></i>
                </button>

                <form class="d-none d-md-flex ms-2">
                    <div class="input-group input-group-merge wh-search-header">
                        <span class="input-group-text border-0 bg-light"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-0 bg-light shadow-none" placeholder="Tìm nhanh..."
                            style="width: 250px;">
                    </div>
                </form>

                <div class="ms-auto d-flex align-items-center">
                    <button class="btn btn-primary rounded-pill px-3 me-3 d-none d-md-flex align-items-center shadow-sm"
                        onclick="navigate('sales')"
                        style="background: linear-gradient(45deg, #4361ee, #4cc9f0); border: none;">
                        <i class="fas fa-cash-register me-2"></i>
                        <span class="fw-bold small">POS Bán hàng</span>
                    </button>

                    <div class="dropdown me-3">
                        <a href="#" class="position-relative text-secondary" data-bs-toggle="dropdown"
                            data-bs-display="static">
                            <i class="fas fa-bell fs-5"></i>
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light"
                                style="font-size: 10px;">3</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-0 overflow-hidden"
                            id="notification-dropdown">
                        </ul>
                    </div>

                    <div class="dropdown me-3">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle"
                            data-bs-toggle="dropdown">
                            <div class="text-end me-2 d-none d-sm-block">
                                <div class="fw-bold text-dark mb-0" style="font-size: 14px;">
                                    <?php echo $_SESSION['fullname'] ?? 'Admin Manager'; ?>
                                </div>

                                <div class="text-muted" style="font-size: 12px;">
                                    <?php
                                    $role = $_SESSION['role'] ?? 'nhanvien';
                                    echo ($role === 'admin') ? 'Quản trị viên' : 'Nhân viên';
                                    ?>
                                </div>
                            </div>

                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['fullname'] ?? 'A'); ?>&background=4361ee&color=fff"
                                class="rounded-circle border border-2 border-white shadow-sm" width="40" height="40">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2 mt-2">
                            <li><a class="dropdown-item rounded-2 py-2" href="javascript:void(0)"
                                    onclick="navigate('profile')"><i class="fas fa-user-cog me-2"></i> Hồ sơ cá nhân</a>
                            </li>
                            <li><a class="dropdown-item rounded-2 py-2" href="#"><i class="fas fa-key me-2"></i> Đổi mật
                                    khẩu</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item rounded-2 py-2 text-danger" href="logout.php"><i
                                        class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <main id="app-container" class="p-4">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>