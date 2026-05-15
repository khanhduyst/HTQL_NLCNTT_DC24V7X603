<?php
session_start();
// Lấy tên từ session để hiển thị câu chào cá nhân hóa
$fullname = $_SESSION['fullname'] ?? 'Admin';
?>
<div class="dash-container">
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted d-block mb-1">Tổng doanh thu</small>
                <h4 class="fw-bold text-primary mb-0">3.2B</h4>
                <small class="text-success fw-bold"><i class="fas fa-arrow-up"></i> 15%</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted d-block mb-1">Tổng đơn hàng</small>
                <h4 class="fw-bold text-dark mb-0">1,250</h4>
                <small class="text-success fw-bold"><i class="fas fa-arrow-up"></i> 8%</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted d-block mb-1">Khách hàng mới</small>
                <h4 class="fw-bold text-dark mb-0">+450</h4>
                <small class="text-danger fw-bold"><i class="fas fa-arrow-down"></i> 2%</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted d-block mb-1">Tỷ lệ hoàn hàng</small>
                <h4 class="fw-bold text-dark mb-0">1.2%</h4>
                <small class="text-success fw-bold">Ổn định</small>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card dash-card border-0 shadow-sm dash-welcome-card" style="background: #eef2ff;">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div class="dash-welcome-text">
                        <h4 class="fw-bold text-primary mb-2">Chúc mừng <?php echo $fullname; ?>! 🎉</h4>
                        <p class="text-muted mb-3">Bạn đã đạt <span class="fw-bold">78%</span> mục tiêu doanh số tháng
                            này.<br>Hãy kiểm tra các đơn hàng mới ngay.</p>
                        <button class="btn btn-primary btn-sm rounded-pill px-3" onclick="navigate('orders')">Xem đơn
                            hàng</button>
                    </div>
                    <div class="dash-welcome-img d-none d-md-block">
                        <i class="fas fa-rocket text-primary" style="font-size: 80px; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="row g-4">
                <div class="col-6">
                    <div class="card dash-card border-0 shadow-sm p-3">
                        <div class="dash-icon-box bg-success-subtle text-success mb-2">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <small class="text-muted d-block">Doanh số</small>
                        <h5 class="fw-bold mb-0">125.4M</h5>
                        <small class="text-success fw-bold"><i class="fas fa-arrow-up me-1"></i>28.4%</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card dash-card border-0 shadow-sm p-3">
                        <div class="dash-icon-box bg-info-subtle text-info mb-2">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <small class="text-muted d-block">Lợi nhuận</small>
                        <h5 class="fw-bold mb-0">42.8M</h5>
                        <small class="text-info fw-bold"><i class="fas fa-arrow-up me-1"></i>12.1%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card dash-card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between">
                    <h5 class="fw-bold mb-0">Biến động Doanh thu & Lợi nhuận</h5>
                    <small class="text-muted">Báo cáo năm 2026</small>
                </div>
                <div class="card-body">
                    <div id="dash-revenue-chart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card dash-card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0">Giao dịch gần đây</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" id="dash-recent-trans"></ul>
                </div>
                <div class="card-footer bg-white border-0 text-center pb-3">
                    <a href="javascript:void(0)" class="small text-primary text-decoration-none fw-bold"
                        onclick="navigate('orders')">Xem tất cả giao dịch</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0">Doanh thu theo Danh mục</h5>
                </div>
                <div class="card-body">
                    <div id="dash-category-chart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card dash-card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0">Sản phẩm bán chạy nhất</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 dash-table">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Sản phẩm</th>
                                    <th>Danh mục</th>
                                    <th>Giá bán</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end pe-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="dash-top-products"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>