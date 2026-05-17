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
            <button class="btn btn-outline-secondary wh-btn-export me-2" id="btn-wh-export-excel">
                <i class="fas fa-file-excel me-1"></i> Xuất file
            </button>
            <button class="btn btn-primary wh-btn-add" onclick="clearWarehouseFormForNew()">
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
                        <option value="instock">Còn hàng</option>
                        <option value="low">Sắp hết</option>
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
                <div class="small text-muted">Tổng cộng: <span id="wh-total-count" class="fw-bold">0</span> mặt hàng
                </div>
                <nav id="wh-pagination"></nav>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="wh-detail-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 py-3 pb-0">
                <h6 class="modal-title fw-bold text-secondary"><i class="fas fa-cube me-1"></i> THÔNG TIN TRUY XUẤT KHO
                    HÀNG</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"
                    style="font-size: 12px;"></button>
            </div>
            <div class="modal-body p-4" id="wh-modal-body">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddWarehouse" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white py-3"
                style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <h5 class="modal-title fw-bold"><i class="fas fa-boxes me-2"></i> Khởi Tạo & Nhập Kho Hàng Hóa</h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <form id="form-add-warehouse" class="needs-validation" novalidate>
                <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">

                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2"><i class="fas fa-info-circle me-1"></i>
                            1. Thông tin cơ bản sản phẩm</h6>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-dark">Tên sản phẩm/Hàng hóa <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="wh-add-name" class="form-control border-light-subtle shadow-none"
                                    placeholder="Nhập tên sản phẩm chính xác..." required>
                                <div class="invalid-feedback">Vui lòng điền tên hàng hóa!</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Mã SKU định danh <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" id="wh-sku" class="form-control border-light-subtle shadow-none"
                                        placeholder="Mã SKU..." required>
                                    <button class="btn btn-outline-secondary" type="button" id="btn-wh-gen-sku"
                                        title="Tạo mã ngẫu nhiên"><i class="fas fa-random"></i></button>
                                </div>
                                <div class="invalid-feedback">Vui lòng nhập hoặc tạo mã SKU!</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Danh mục hàng hóa <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select id="wh-add-category" class="form-select border-light-subtle shadow-none"
                                        required>
                                        <option value="">-- Chọn danh mục --</option>
                                    </select>
                                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal"
                                        data-bs-target="#modalAddCategoryQuick" title="Thêm nhanh danh mục"><i
                                            class="fas fa-plus"></i></button>
                                </div>
                                <div class="invalid-feedback">Vui lòng chọn danh mục!</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Thương hiệu / Nhãn hàng</label>
                                <input type="text" id="wh-add-brand"
                                    class="form-control border-light-subtle shadow-none"
                                    placeholder="Ví dụ: Honda, Apple, Vinamilk...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Hình ảnh sản phẩm</label>
                                <input type="file" id="wh-add-image"
                                    class="form-control border-light-subtle shadow-none" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2"><i class="fas fa-cubes me-1"></i> 2.
                            Quản lý số lượng & Hạn dùng</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Số lượng nhập kho <span
                                        class="text-danger">*</span></label>
                                <input type="number" id="wh-add-quantity"
                                    class="form-control border-light-subtle shadow-none" min="1" placeholder="0"
                                    required>
                                <div class="invalid-feedback">Số lượng phải lớn hơn 0!</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Đơn vị tính <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="wh-add-unit" class="form-control border-light-subtle shadow-none"
                                    placeholder="Cái, Chiếc, Thùng..." value="Cái" required>
                                <div class="invalid-feedback">Vui lòng điền ĐVT!</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-secondary">Quy cách / Dung tích</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i
                                            class="fas fa-info-circle"></i></span>
                                    <input type="text" class="form-control" id="wh-add-specification"
                                        placeholder="Ví dụ: 330 ml, 5 kg, 1.5 Lít...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Cảnh báo tối thiểu <span
                                        class="text-danger">*</span></label>
                                <input type="number" id="wh-add-min-alert"
                                    class="form-control border-light-subtle shadow-none" min="0" value="5" required>
                                <small class="text-muted" style="font-size: 11px;">Dưới mức này sẽ báo Sắp hết
                                    hàng</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">Vị trí lưu kho</label>
                                <input type="text" id="wh-add-location"
                                    class="form-control border-light-subtle shadow-none"
                                    placeholder="Ví dụ: Kệ A1-Lộng 2">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Ngày sản xuất (NSX)</label>
                                <input type="date" id="wh-add-mfg-date"
                                    class="form-control border-light-subtle shadow-none">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Hạn sử dụng (HSD)</label>
                                <input type="date" id="wh-add-exp-date"
                                    class="form-control border-light-subtle shadow-none">
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2"><i class="fas fa-wallet me-1"></i> 3.
                            Kế toán giá & Ghi chú</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Giá vốn nhập kho (VNĐ) <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" id="wh-add-price-in"
                                        class="form-control border-light-subtle shadow-none" placeholder="0" required>
                                    <span class="input-group-text bg-light text-muted small">đ</span>
                                </div>
                                <div class="invalid-feedback">Vui lòng điền giá nhập!</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Giá bán dự kiến (VNĐ) <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" id="wh-add-price-out"
                                        class="form-control border-light-subtle shadow-none" placeholder="0" required>
                                    <span class="input-group-text bg-light text-muted small">đ</span>
                                </div>
                                <div class="invalid-feedback">Vui lòng điền giá bán dự kiến!</div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-dark">Ghi chú nhập kho</label>
                                <textarea id="wh-add-note" class="form-control border-light-subtle shadow-none" rows="2"
                                    placeholder="Nhập ghi chú hàng hóa, thông tin nhà cung cấp hoặc trạng thái khi nhận hàng..."></textarea>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer bg-light border-top p-3"
                    style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn btn-light rounded-pill px-4 border" data-bs-dismiss="modal">Hủy
                        bỏ</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i
                            class="fas fa-save me-1"></i> Xác nhận nhập kho</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddCategoryQuick" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg p-2" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fas fa-folder-plus text-primary me-2"></i>Thêm danh mục mới
                </h5>
                <button type="button" class="btn-close shadow-none" onclick="closeQuickCategoryModal()"></button>
            </div>
            <div class="modal-body py-3">
                <div class="mb-3">
                    <label class="form-label small text-muted fw-bold mb-2">Tên danh mục <span
                            class="text-danger">*</span></label>
                    <input type="text" id="wh-quick-cat-name" class="form-control shadow-none py-2 border-light-subtle"
                        placeholder="Ví dụ: Xe máy, Ô tô, Hàng tạp hóa...">
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 py-2 small"
                        onclick="closeQuickCategoryModal()">Hủy bỏ</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold small shadow-sm"
                        onclick="submitQuickCategory()">
                        <i class="fas fa-check-circle me-1"></i>Lưu lại
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>