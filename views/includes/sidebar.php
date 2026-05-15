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
            <a href="javascript:void(0)" class="nav-link text-white" onclick="navigate('warehouse')">
                <i class="fas fa-warehouse me-2"></i> Quản lý kho
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="javascript:void(0)" class="nav-link text-white" onclick="navigate('customers')">
                <i class="fas fa-user-friends me-2"></i> Khách hàng
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="javascript:void(0)" class="nav-link text-white text-danger" onclick="logout()">
                <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
            </a>
        </li>
    </ul>
</nav>