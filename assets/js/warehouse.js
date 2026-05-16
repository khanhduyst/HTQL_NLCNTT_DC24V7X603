(function () {
  let products = [];

  function loadProductsFromDB() {
    const area = document.getElementById("wh-content-render");
    if (!area) return;

    fetch("api/get_products.php")
      .then((res) => res.json())
      .then((response) => {
        if (response.status === "success") {
          products = response.data;
          const countEl = document.getElementById("wh-total-count");
          if (countEl) countEl.innerText = products.length;
          renderWarehouse();
        } else {
          area.innerHTML = `<div class="alert alert-danger m-3">Không thể tải dữ liệu: ${response.message}</div>`;
        }
      })
      .catch((err) => {
        area.innerHTML = `<div class="alert alert-danger m-3">Lỗi kết nối đến máy chủ!</div>`;
      });
  }

  function loadCategoriesFromDB() {
    fetch("api/api_categories.php")
      .then((res) => res.json())
      .then((response) => {
        if (response.status === "success") {
          const filterSelect = document.getElementById("wh-category-filter");
          const addSelect = document.getElementById("wh-add-category");

          if (filterSelect) filterSelect.innerHTML = '<option value="">Tất cả danh mục</option>';
          if (addSelect) addSelect.innerHTML = '<option value="">-- Chọn danh mục --</option>';

          response.data.forEach((cat) => {
            if (filterSelect) {
              filterSelect.innerHTML += `<option value="${cat.name}">${cat.name}</option>`;
            }
            if (addSelect) {
              addSelect.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
            }
          });
        }
      })
      .catch((err) => console.error("Lỗi nạp danh mục:", err));
  }

  function renderWarehouse() {
    const area = document.getElementById("wh-content-render");
    if (!area) return;

    if (products.length === 0) {
      area.innerHTML = `<div class="text-center py-5 text-muted">Kho hàng trống hoặc chưa có sản phẩm!</div>`;
      return;
    }

    if (window.innerWidth <= 991.98) {
      area.innerHTML = `
                <div class="wh-card-list">
                    ${products
                      .map(
                        (p, index) => `
                        <div class="wh-list-item shadow-sm">
                            <div class="wh-item-header">
                                <div class="wh-img-box text-white d-flex align-items-center justify-content-center rounded" style="background: ${p.color}; width: 45px; height: 45px; min-width: 45px;">
                                    ${p.icon}
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <div class="fw-bold text-dark">${p.name}</div>
                                    <small class="text-muted">SKU: ${p.sku}</small>
                                </div>
                                <span class="wh-badge stock-${p.status}">${p.stock}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="fw-bold text-primary">${p.price} đ</span>
                                <button class="btn btn-light btn-sm rounded-pill px-3 border" onclick="showWhDetail(${index})">Chi tiết</button>
                            </div>
                        </div>
                    `,
                      )
                      .join("")}
                </div>
            `;
    } else {
      area.innerHTML = `
                <table class="table table-hover align-middle mb-0 wh-table">
                    <thead>
                        <tr>
                            <th class="ps-4">Sản phẩm</th>
                            <th>Mã SKU</th>
                            <th>Loại</th>
                            <th>Giá nhập</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${products
                          .map(
                            (p, index) => `
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="wh-img-box me-3 text-white d-flex align-items-center justify-content-center" 
                                             style="background: ${p.color}; width: 35px; height: 35px; min-width: 35px; font-size: 14px; border-radius: 6px;">
                                            ${p.icon}
                                        </div>
                                        <div class="fw-bold text-dark">${p.name}</div>
                                    </div>
                                </td>
                                <td><code class="text-pink fw-bold" style="color: #d63384;">${p.sku}</code></td>
                                <td>${p.cat}</td>
                                <td>${p.price} đ</td>
                                <td class="fw-bold">${p.stock}</td>
                                <td><span class="wh-badge stock-${p.status}">${p.label}</span></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-light btn-sm rounded-circle me-1" onclick="showWhDetail(${index})"><i class="fas fa-eye text-dark"></i></button>
                                    <button class="btn btn-light btn-sm rounded-circle text-danger"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `,
                          )
                          .join("")}
                    </tbody>
                </table>
            `;
    }
    renderWhPagination();
  }

  function renderWhPagination() {
    const pagin = document.getElementById("wh-pagination");
    if (!pagin) return;

    pagin.innerHTML = `
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled">
                    <a class="page-link border-0 bg-light rounded-2 me-1" href="#"><i class="fas fa-chevron-left small"></i></a>
                </li>
                <li class="page-item active">
                    <a class="page-link border-0 rounded-2 me-1 shadow-sm" href="#" style="background: #4361ee; width: 30px; text-align: center;">1</a>
                </li>
                <li class="page-item">
                    <a class="page-link border-0 bg-light text-dark rounded-2 me-1" href="#" style="width: 30px; text-align: center;">2</a>
                </li>
                <li class="page-item">
                    <a class="page-link border-0 bg-light rounded-2" href="#"><i class="fas fa-chevron-right small text-dark"></i></a>
                </li>
            </ul>
        `;
  }

  window.showWhDetail = function (index) {
    const p = products[index];
    const modalBody = document.getElementById("wh-modal-body");
    modalBody.innerHTML = `
            <div class="text-center mb-4">
                <div class="wh-img-box mx-auto mb-3 d-flex align-items-center justify-content-center text-white shadow-sm" 
                     style="background: ${p.color}; width: 70px; height: 70px; border-radius: 12px; font-size: 30px;">
                    ${p.icon}
                </div>
                <h5 class="fw-bold mb-1 text-dark">${p.name}</h5>
                <span class="badge bg-light text-primary border">${p.sku}</span>
            </div>
            <ul class="list-group list-group-flush border-top">
                <li class="list-group-item d-flex justify-content-between py-3 small">
                    <span class="text-muted">Loại sản phẩm:</span>
                    <span class="fw-bold text-dark">${p.cat}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between py-3 small">
                    <span class="text-muted">Giá nhập kho:</span>
                    <span class="fw-bold text-primary">${p.price} đ</span>
                </li>
                <li class="list-group-item d-flex justify-content-between py-3 small">
                    <span class="text-muted">Số lượng tồn:</span>
                    <span class="fw-bold text-dark">${p.stock} cái</span>
                </li>
                <li class="list-group-item d-flex justify-content-between py-3 small border-0">
                    <span class="text-muted">Trạng thái:</span>
                    <span class="wh-badge stock-${p.status}">${p.label}</span>
                </li>
            </ul>
            <div class="d-grid mt-2">
                <button type="button" class="btn btn-primary rounded-pill py-2" data-bs-dismiss="modal">Đóng</button>
            </div>
        `;
    new bootstrap.Modal(document.getElementById("wh-detail-modal")).show();
  };

  window.triggerReloadCategories = function() {
    loadCategoriesFromDB();
  };

  loadProductsFromDB();
  loadCategoriesFromDB();
  window.onresize = renderWarehouse;
})();

window.closeQuickCategoryModal = function () {
  const catModalEl = document.getElementById("modalAddCategoryQuick");
  const modalInstance = bootstrap.Modal.getInstance(catModalEl);
  if (modalInstance) modalInstance.hide();
};

window.submitQuickCategory = function () {
  const catNameInput = document.getElementById("wh-quick-cat-name");
  const catName = catNameInput ? catNameInput.value.trim() : "";

  if (!catName) {
    Swal.fire("Cảnh báo!", "Vui lòng nhập tên danh mục!", "warning");
    return;
  }

  fetch("api/api_categories.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ category_name: catName }),
  })
    .then((res) => res.text())
    .then((text) => {
      try {
        const response = JSON.parse(text.trim());

        if (response.status === "success") {
          closeQuickCategoryModal();
          if (catNameInput) catNameInput.value = "";

          if (typeof window.showFire === "function") {
            window.showFire("Đã thêm danh mục mới vào hệ thống!");
          } else {
            Swal.fire("Thành công!", "Đã thêm danh mục mới.", "success");
          }
          if (typeof window.triggerReloadCategories === "function") {
            window.triggerReloadCategories();
          }
        } else {
          Swal.fire("Lỗi từ DB!", response.message, "error");
        }
      } catch (jsonErr) {
        if (text.includes("success") || text.trim() === "") {
          closeQuickCategoryModal();
          if (catNameInput) catNameInput.value = "";
          if (typeof window.triggerReloadCategories === "function") {
            window.triggerReloadCategories();
          }
        } else {
          console.error("Chi tiết chuỗi lỗi trả về:", text);
          Swal.fire("Lỗi xử lý JSON!", "Backend trả về dữ liệu không chuẩn mã hóa.", "error");
        }
      }
    })
    .catch((err) => {
      console.error("Lỗi kết nối gốc:", err);
      Swal.fire("Lỗi!", "Không thể kết nối đến máy chủ API!", "error");
    });
};

(function () {
  const whForm = document.getElementById("form-add-warehouse");
  const whModal = document.getElementById("modalAddWarehouse");
  const btnGenSku = document.getElementById("btn-wh-gen-sku");
  const inputSku = document.getElementById("wh-sku");
  const btnSubmit = whForm?.querySelector('button[type="submit"]');

  if (whForm && whModal) {
    const originalText = btnSubmit.innerHTML;

    if (btnGenSku) {
      btnGenSku.onclick = function () {
        const random = Math.floor(10000 + Math.random() * 90000);
        inputSku.value = "WH-" + random;
        inputSku.classList.add("is-valid");
        setTimeout(() => inputSku.classList.remove("is-valid"), 500);
      };
    }

    whForm.onsubmit = function (e) {
      e.preventDefault();

      if (!whForm.checkValidity()) {
        e.stopPropagation();
        whForm.classList.add("was-validated");
        return;
      }

      btnSubmit.disabled = true;
      btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang nạp kho...';

      const productData = {
        name: document.getElementById("wh-add-name").value,
        sku: document.getElementById("wh-sku").value,
        category_id: document.getElementById("wh-add-category").value,
        stock: document.getElementById("wh-add-quantity").value,
        location: document.getElementById("wh-add-location").value,
        price: document.getElementById("wh-add-price-in").value || 0,
        unit: "Cái",
        description: document.getElementById("wh-add-note").value,
      };

      fetch("api/add_product.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(productData),
      })
        .then((res) => res.text())
        .then((text) => {
          try {
            const response = JSON.parse(text.trim());

            if (response.status === "success") {
              btnSubmit.innerHTML = '<i class="fas fa-check me-2"></i> Đã xong!';
              btnSubmit.classList.replace("btn-primary", "btn-success");

              setTimeout(() => {
                Swal.fire({
                  icon: "success",
                  title: "Nhập kho thành công!",
                  text: "Sản phẩm đã được cập nhật vào hệ thống Aiven.",
                  timer: 2000,
                  showConfirmButton: false,
                });

                const modalInstance = bootstrap.Modal.getInstance(whModal);
                if (modalInstance) modalInstance.hide();
                location.reload();
              }, 800);
            } else {
              btnSubmit.innerHTML = originalText;
              btnSubmit.disabled = false;
              Swal.fire("Lỗi!", response.message, "error");
            }
          } catch (jsonErr) {
            if (text.includes("success") || text.trim() === "") {
              btnSubmit.innerHTML = '<i class="fas fa-check me-2"></i> Đã xong!';
              btnSubmit.classList.replace("btn-primary", "btn-success");

              setTimeout(() => {
                Swal.fire({
                  icon: "success",
                  title: "Nhập kho thành công!",
                  text: "Sản phẩm đã được cập nhật vào hệ thống Aiven.",
                  timer: 2000,
                  showConfirmButton: false,
                });

                const modalInstance = bootstrap.Modal.getInstance(whModal);
                if (modalInstance) modalInstance.hide();
                location.reload();
              }, 800);
            } else {
              btnSubmit.innerHTML = originalText;
              btnSubmit.disabled = false;
              Swal.fire("Lỗi xử lý!", "Phản hồi máy chủ lỗi cấu trúc.", "error");
            }
          }
        })
        .catch((err) => {
          btnSubmit.innerHTML = originalText;
          btnSubmit.disabled = false;
          Swal.fire("Lỗi!", "Không thể kết nối đến máy chủ API!", "error");
        });
    };

    whModal.addEventListener("hidden.bs.modal", function () {
      whForm.classList.remove("was-validated");
      btnSubmit.innerHTML = originalText;
      btnSubmit.classList.replace("btn-success", "btn-primary");
      btnSubmit.disabled = false;
      whForm.reset();
    });
  }
})();