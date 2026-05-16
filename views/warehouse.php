<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Kiểm tra quyền truy cập nhanh tại module
if (!isset($_SESSION['user_id'])) {
    exit('Chặn truy cập hợp lệ!');
}
?>
<div class="wh-container">
    <div class="wh-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="wh-title text-dark fw-bold">Quản Lý Kho Hàng</h3>
            <p class="wh-subtitle text-muted mb-0">Theo dõi tồn kho và luân chuyển hàng hóa</p>
        </div>
        <div class="wh-actions">
            <button class="btn btn-outline-secondary wh-btn-export me-2">
                <i class="fas fa-file-excel me-1"></i> Xuất file
            </button>
            <button class="btn btn-primary wh-btn-add" data-bs-toggle="modal" data-bs-target="#modalAddWarehouse">
                <i class="fas fa-plus me-1"></i> Nhập hàng mới
            </button>
        </div>
    </div>

    <div class="card wh-card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-light"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="wh-search-input" class="form-control border-0 bg-light shadow-none"
                            placeholder="Tìm tên SP, SKU...">
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <select id="wh-category-filter" class="form-select border-0 bg-light shadow-none">
                        <option value="">Tất cả danh mục</option>
                        <option value="Laptop">Laptop</option>
                        <option value="Điện thoại">Điện thoại</option>
                        <option value="Máy tính bảng">Máy tính bảng</option>
                        <option value="Phụ kiện">Phụ kiện</option>
                    </select>
                </div>
                <div class="col-md-3 col-6">
                    <select id="wh-status-filter" class="form-select border-0 bg-light shadow-none">
                        <option value="">Tất cả trạng thái</option>
                        <option value="ok">Còn hàng</option>
                        <option value="low">Sắp hết hàng</option>
                        <option value="out">Hết hàng</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div id="wh-content-render"></div>
        </div>

        <div class="card-footer bg-white py-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div class="small text-muted">Tổng cộng: <span id="wh-total-count" class="fw-bold">0</span> mặt hàng</div>
                <nav id="wh-pagination"></nav>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="wh-detail-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Thông tin sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2" id="wh-modal-body">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddWarehouse" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fas fa-warehouse text-primary me-2"></i>Nhập kho sản phẩm mới
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <form id="form-add-warehouse" class="row g-4 needs-validation" novalidate>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="position-relative d-inline-block">
                                <img id="wh-img-preview"
                                    src="https://ui-avatars.com/api/?name=WH&background=f8f9fa&color=a1acb8&size=150"
                                    class="rounded-3 border p-1 mb-2" width="150" height="150"
                                    style="object-fit: cover;">
                                <label for="wh-upload-img"
                                    class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 p-2"
                                    style="width: 38px; height: 38px; cursor: pointer; border: 3px solid #fff;">
                                    <i class="fas fa-camera"></i>
                                    <input type="file" id="wh-upload-img" accept="image/*" hidden>
                                </label>
                            </div>
                            <p class="small text-muted mt-2">Định dạng: JPG, PNG<br>Dung lượng tối đa 2MB</p>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Tên hàng hóa <span class="text-danger">*</span></label>
                                <input type="text" id="wh-add-name" class="form-control border-light-subtle shadow-none py-2"
                                    placeholder="Ví dụ: Thùng sơn Dulux 5L" required>
                            </div>

                            <div class="col-md-7">
                                <label class="form-label small fw-bold text-muted">Mã SKU <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" id="wh-sku" class="form-control border-light-subtle shadow-none"
                                        placeholder="WH-001" required>
                                    <button class="btn btn-outline-primary shadow-none px-3" type="button" id="btn-wh-gen-sku">
                                        <i class="fas fa-barcode"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-muted">Đơn vị</label>
                                <select id="wh-add-unit" class="form-select border-light-subtle shadow-none">
                                    <option value="Cái">Cái</option>
                                    <option value="Thùng">Thùng</option>
                                    <option value="Bao">Bao</option>
                                    <option value="Kg">Kg</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Số lượng nhập <span class="text-danger">*</span></label>
                                <input type="number" id="wh-add-quantity" class="form-control border-light-subtle shadow-none" value="1"
                                    min="1" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Danh mục <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select id="wh-add-category" class="form-select border-light-subtle shadow-none" required>
                                        <option value="">-- Chọn danh mục --</option>
                                    </select>
                                    <button class="btn btn-outline-primary shadow-none" type="button" data-bs-toggle="modal" data-bs-target="#modalAddCategoryQuick">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Vui lòng chọn danh mục!</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Giá nhập</label>
                                <div class="input-group">
                                    <input type="number" id="wh-add-price-in" class="form-control border-light-subtle shadow-none"
                                        placeholder="0">
                                    <span class="input-group-text bg-light border-light-subtle">đ</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Giá bán dự kiến</label>
                                <div class="input-group">
                                    <input type="number" id="wh-add-price-out" class="form-control border-light-subtle shadow-none"
                                        placeholder="0">
                                    <span class="input-group-text bg-light border-light-subtle">đ</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <label class="form-label small fw-bold text-muted">Ghi chú nhập kho</label>
                        <textarea id="wh-add-note" class="form-control border-light-subtle shadow-none" rows="2"
                            placeholder="Nhập ghi chú hoặc thông tin nhà cung cấp..."></textarea>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 me-2 py-2"
                            data-bs-dismiss="modal">Hủy bỏ</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                            <i class="fas fa-check-circle me-2"></i>Xác nhận nhập kho
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddCategoryQuick" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered"> <div class="modal-content border-0 shadow-lg p-2" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fas fa-folder-plus text-primary me-2"></i>Thêm danh mục mới
                </h5>
                <button type="button" class="btn-close shadow-none" onclick="closeQuickCategoryModal()"></button>
            </div>
            <div class="modal-body py-3">
                <div class="mb-3">
                    <label class="form-label small text-muted fw-bold mb-2">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" id="wh-quick-cat-name" class="form-control shadow-none py-2 border-light-subtle" placeholder="Ví dụ: Xe máy, Ô tô, Hàng tạp hóa...">
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 py-2 small" onclick="closeQuickCategoryModal()">Hủy bỏ</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold small shadow-sm" onclick="submitQuickCategory()">
                        <i class="fas fa-check-circle me-1"></i>Lưu lại
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>