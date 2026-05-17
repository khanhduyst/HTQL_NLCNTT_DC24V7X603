(function () {
  let products = [];
  let isEditMode = false;
  let currentEditSku = "";
  let currentPage = 1;

  function loadProductsFromDB() {
    const area = document.getElementById("wh-content-render");
    if (!area) return;

    const searchInput = document.getElementById("wh-search-input");
    const catSelect = document.getElementById("wh-category-filter");
    const statusSelect = document.getElementById("wh-status-filter");

    const keyword = searchInput
      ? encodeURIComponent(searchInput.value.trim())
      : "";
    const selectedCat = catSelect ? encodeURIComponent(catSelect.value) : "";
    const selectedStatus = statusSelect
      ? encodeURIComponent(statusSelect.value)
      : "";

    // Bắn toàn bộ tham số phân trang và bộ lọc qua URL cho file PHP giải quyết
    const apiUrl = `api/api_products.php?page=${currentPage}&search=${keyword}&category=${selectedCat}&status=${selectedStatus}`;

    fetch(apiUrl)
      .then((res) => res.json())
      .then((response) => {
        if (response.status === "success") {
          products = response.data;

          const countEl = document.getElementById("wh-total-count");
          if (countEl) countEl.innerText = response.total_items;

          renderWarehouse();
          renderWhPagination(response.total_pages);
        } else {
          area.innerHTML = `<div class="alert alert-danger m-3">Không thể tải dữ liệu: ${response.message}</div>`;
        }
      })
      .catch((err) => {
        area.innerHTML = `<div class="alert alert-danger m-3">Lỗi kết nối hệ thống!</div>`;
      });
  }

  function loadCategoriesFromDB() {
    fetch("api/api_categories.php")
      .then((res) => res.json())
      .then((response) => {
        if (response.status === "success") {
          const filterSelect = document.getElementById("wh-category-filter");
          const addSelect = document.getElementById("wh-add-category");

          if (filterSelect)
            filterSelect.innerHTML =
              '<option value="">Tất cả danh mục</option>';
          if (addSelect)
            addSelect.innerHTML =
              '<option value="">-- Chọn danh mục --</option>';

          response.data.forEach((cat) => {
            if (filterSelect)
              filterSelect.innerHTML += `<option value="${cat.name}">${cat.name}</option>`;
            if (addSelect)
              addSelect.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
          });
        }
      })
      .catch((err) => console.error("Lỗi nạp danh mục:", err));
  }

  function triggerWarehouseSearch() {
    currentPage = 1;
    loadProductsFromDB();
  }

  function renderWarehouse() {
    const area = document.getElementById("wh-content-render");
    if (!area) return;

    if (!products || products.length === 0) {
      area.innerHTML = `<div class="text-center py-5 text-muted">Không tìm thấy sản phẩm nào phù hợp bộ lọc!</div>`;
      return;
    }

    const getProductImageHTML = (p, size = 35) => {
      if (
        p &&
        p.image_url &&
        typeof p.image_url === "string" &&
        (p.image_url.startsWith("http://") ||
          p.image_url.startsWith("https://"))
      ) {
        return `<img src="${p.image_url}" alt="${p.name || "Product"}" class="object-fit-cover rounded shadow-sm" style="width: ${size}px; height: ${size}px; min-width: ${size}px; border: 1px solid #e3e6f0;">`;
      }
      const safeColor = p && p.color ? p.color : "#4361ee";
      const safeIcon = p && p.icon ? p.icon : '<i class="fas fa-box"></i>';
      return `<div class="wh-img-box text-white d-flex align-items-center justify-content-center" style="background: ${safeColor}; width: ${size}px; height: ${size}px; min-width: ${size}px; font-size: ${size / 2.5}px; border-radius: 6px;">${safeIcon}</div>`;
    };

    if (window.innerWidth <= 991.98) {
      area.innerHTML = `
                <div class="wh-card-list">
                    ${products
                      .map(
                        (p, index) => `
                        <div class="wh-list-item shadow-sm">
                            <div class="wh-item-header">
                                ${getProductImageHTML(p, 45)} 
                                <div class="flex-grow-1 ms-2">
                                    <div class="fw-bold text-dark">${p.name}</div>
                                    <small class="text-muted">SKU: ${p.sku}</small>
                                </div>
                                <span class="wh-badge stock-${p.status}">${p.stock}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="fw-bold text-primary">${p.price} đ</span>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-light btn-sm rounded-pill px-2.5 border" onclick="showWhDetail(${index})"><i class="fas fa-eye text-dark"></i></button>
                                    <button class="btn btn-light btn-sm rounded-pill px-2.5 border text-primary" onclick="openEditWarehouse(${index})"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-light btn-sm rounded-pill px-2.5 border text-danger" onclick="deleteWarehouseItem(${index})"><i class="fas fa-trash"></i></button>
                                </div>
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
                                        ${getProductImageHTML(p, 38)} 
                                        <div class="fw-bold text-dark ms-3">${p.name}</div>
                                    </div>
                                </td>
                                <td><code class="text-pink fw-bold" style="color: #d63384;">${p.sku}</code></td>
                                <td>${p.cat}</td>
                                <td>${p.price} đ</td>
                                <td class="fw-bold">${p.stock}</td>
                                <td><span class="wh-badge stock-${p.status}">${p.label}</span></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-light btn-sm rounded-circle me-1" onclick="showWhDetail(${index})" title="Xem chi tiết"><i class="fas fa-eye text-dark"></i></button>
                                    <button class="btn btn-light btn-sm rounded-circle me-1 text-primary" onclick="openEditWarehouse(${index})" title="Chỉnh sửa"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-light btn-sm rounded-circle text-danger" onclick="deleteWarehouseItem(${index})" title="Xóa"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `,
                          )
                          .join("")}
                    </tbody>
                </table>
            `;
    }
  }

  function renderWhPagination(totalPages) {
    const pagin = document.getElementById("wh-pagination");
    if (!pagin) return;

    if (totalPages <= 1) {
      pagin.innerHTML = "";
      return;
    }

    let html = `<ul class="pagination pagination-sm mb-0">`;

    if (currentPage === 1) {
      html += `<li class="page-item disabled"><span class="page-link border-0 bg-light rounded-2 me-1"><i class="fas fa-chevron-left small"></i></span></li>`;
    } else {
      html += `<li class="page-item"><a class="page-link border-0 bg-light text-dark rounded-2 me-1" href="#" onclick="changeWarehousePage(${currentPage - 1})"><i class="fas fa-chevron-left small"></i></a></li>`;
    }

    for (let i = 1; i <= totalPages; i++) {
      if (i === currentPage) {
        html += `<li class="page-item active"><span class="page-link border-0 rounded-2 me-1 shadow-sm" style="background: #4361ee; width: 30px; text-align: center;">${i}</span></li>`;
      } else {
        html += `<li class="page-item"><a class="page-link border-0 bg-light text-dark rounded-2 me-1" href="#" style="width: 30px; text-align: center;" onclick="changeWarehousePage(${i})">${i}</a></li>`;
      }
    }

    if (currentPage === totalPages) {
      html += `<li class="page-item disabled"><span class="page-link border-0 bg-light rounded-2"><i class="fas fa-chevron-right small"></i></span></li>`;
    } else {
      html += `<li class="page-item"><a class="page-link border-0 bg-light text-dark rounded-2" href="#" onclick="changeWarehousePage(${currentPage + 1})"><i class="fas fa-chevron-right small"></i></a></li>`;
    }

    html += `</ul>`;
    pagin.innerHTML = html;
  }

  window.changeWarehousePage = function (pageNumber) {
    currentPage = pageNumber;
    loadProductsFromDB();
  };

  window.clearWarehouseFormForNew = function () {
    isEditMode = false;
    currentEditSku = "";

    const form = document.getElementById("form-add-warehouse");
    if (form) {
      form.reset();
      form.classList.remove("was-validated");
    }

    const skuInput = document.getElementById("wh-sku");
    if (skuInput) skuInput.disabled = false;

    const specInput = document.getElementById("wh-add-specification");
    if (specInput) specInput.value = "";

    const noteInput = document.getElementById("wh-add-note");
    if (noteInput) noteInput.value = "";

    const locInput = document.getElementById("wh-add-location");
    if (locInput) locInput.value = "";

    const modalTitle = document.querySelector(
      "#modalAddWarehouse .modal-title",
    );
    const btnSubmit = document.querySelector(
      "#form-add-warehouse button[type='submit']",
    );
    if (modalTitle)
      modalTitle.innerHTML =
        '<i class="fas fa-boxes me-2"></i> Khởi Tạo & Nhập Kho Hàng Hóa';
    if (btnSubmit)
      btnSubmit.innerHTML =
        '<i class="fas fa-save me-1"></i> Xác nhận nhập kho';

    const whModalEl = document.getElementById("modalAddWarehouse");
    const modalInstance =
      bootstrap.Modal.getInstance(whModalEl) || new bootstrap.Modal(whModalEl);
    modalInstance.show();
  };

  window.openEditWarehouse = function (index) {
    isEditMode = true;
    const p = products[index];
    currentEditSku = p.sku;

    const modalTitle = document.querySelector(
      "#modalAddWarehouse .modal-title",
    );
    const btnSubmit = document.querySelector(
      "#form-add-warehouse button[type='submit']",
    );

    if (modalTitle)
      modalTitle.innerHTML =
        '<i class="fas fa-edit me-2"></i> Cập Nhật Thông Tin Hàng Hóa';
    if (btnSubmit)
      btnSubmit.innerHTML = '<i class="fas fa-save me-1"></i> Lưu thay đổi';

    document.getElementById("wh-add-name").value = p.name;
    document.getElementById("wh-sku").value = p.sku;
    document.getElementById("wh-sku").disabled = true;
    document.getElementById("wh-add-brand").value = p.brand || "";
    document.getElementById("wh-add-quantity").value = p.stock;
    document.getElementById("wh-add-unit").value = p.unit || "Cái";
    document.getElementById("wh-add-min-alert").value = p.min_alert || 5;
    document.getElementById("wh-add-location").value = p.location || "";
    document.getElementById("wh-add-mfg-date").value = p.mfg_date || "";
    document.getElementById("wh-add-exp-date").value = p.exp_date || "";

    const specInput = document.getElementById("wh-add-specification");
    if (specInput) specInput.value = p.specification || "";

    const rawPriceIn = p.price ? p.price.replace(/\./g, "") : 0;
    document.getElementById("wh-add-price-in").value = rawPriceIn;
    document.getElementById("wh-add-price-out").value = p.price_out || 0;
    document.getElementById("wh-add-note").value = p.description || "";

    const selectCat = document.getElementById("wh-add-category");
    if (selectCat) {
      for (let option of selectCat.options) {
        if (option.text === p.cat) {
          selectCat.value = option.value;
          break;
        }
      }
    }

    const whModalEl = document.getElementById("modalAddWarehouse");
    const modalInstance =
      bootstrap.Modal.getInstance(whModalEl) || new bootstrap.Modal(whModalEl);
    modalInstance.show();
  };

  window.deleteWarehouseItem = function (index) {
    const p = products[index];
    Swal.fire({
      title: "Xác nhận xóa hàng hóa?",
      text: `Sản phẩm [${p.sku}] - ${p.name} sẽ bị rút khỏi danh mục hiển thị.`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ef4444",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Đúng vậy, xóa nó!",
      cancelButtonText: "Hủy thao tác",
    }).then((result) => {
      if (result.isConfirmed) {
        fetch("api/api_products.php", {
          method: "DELETE",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ sku: p.sku }),
        })
          .then((res) => res.json())
          .then((response) => {
            if (response.status === "success") {
              Swal.fire({
                icon: "success",
                title: "Đã xóa mềm thành công!",
                confirmButtonColor: "#4361ee",
              }).then(() => {
                loadProductsFromDB();
              });
            } else {
              Swal.fire("Lỗi!", response.message, "error");
            }
          });
      }
    });
  };

  const whModalEl = document.getElementById("modalAddWarehouse");
  if (whModalEl) {
    whModalEl.addEventListener("hidden.bs.modal", function () {
      isEditMode = false;
      currentEditSku = "";
      document.getElementById("wh-sku").disabled = false;
    });
  }

  window.showWhDetail = function (index) {
    const p = products[index];
    const modalBody = document.getElementById("wh-modal-body");
    if (!modalBody) return;

    const imgHTML =
      p.image_url &&
      (p.image_url.startsWith("http://") || p.image_url.startsWith("https://"))
        ? `<div class="position-relative overflow-hidden rounded-3 shadow-sm" style="height: 100%; min-height: 260px;"><img src="${p.image_url}" alt="${p.name}" class="w-100 h-100 object-fit-cover position-absolute top-0 start-0"></div>`
        : `<div class="w-100 rounded-3 d-flex flex-column align-items-center justify-content-center text-white p-4" style="background: #4361ee; min-height: 260px;"><span style="font-size: 64px;">📦</span><h4 class="fw-bold text-white mt-1">${p.name}</h4></div>`;

    modalBody.innerHTML = `
      <div class="row g-4 text-start">
          <div class="col-md-5 d-flex flex-column justify-content-between">${imgHTML}</div>
          <div class="col-md-7">
              <div class="row g-2">
                  <div class="col-6"><div class="p-3 bg-light rounded-3 h-100"><b>Danh mục:</b><br>${p.cat}</div></div>
                  <div class="col-6"><div class="p-3 bg-light rounded-3 h-100"><b>Thương hiệu:</b><br>${p.brand || "-"}</div></div>
                  <div class="col-6"><div class="p-3 bg-light rounded-3 h-100"><b>Tồn kho:</b><br>${p.stock} ${p.unit}</div></div>
                  <div class="col-6"><div class="p-3 bg-light rounded-3 h-100"><b>Vị trí:</b><br>${p.location || "-"}</div></div>
                  <div class="col-6"><div class="p-3 rounded-3 h-100 bg-success bg-opacity-10 text-success"><b>Giá nhập:</b><br>${p.price} đ</div></div>
                  <div class="col-6"><div class="p-3 rounded-3 h-100 bg-danger bg-opacity-10 text-danger"><b>Giá bán:</b><br>${Number(p.price_out).toLocaleString("vi-VN")} đ</div></div>
                  <div class="col-12"><div class="p-2.5 bg-light rounded-3 text-center"><b>Quy cách:</b> ${p.specification || "Chưa cập nhật"}</div></div>
              </div>
          </div>
      </div>
    `;

    const modalEl = document.getElementById("wh-detail-modal");
    const modalInstance =
      bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modalInstance.show();
  };

  loadProductsFromDB();
  loadCategoriesFromDB();

  const searchInp = document.getElementById("wh-search-input");
  if (searchInp) searchInp.addEventListener("input", triggerWarehouseSearch);

  const catFilt = document.getElementById("wh-category-filter");
  if (catFilt) catFilt.addEventListener("change", triggerWarehouseSearch);

  const statusFilt = document.getElementById("wh-status-filter");
  if (statusFilt) statusFilt.addEventListener("change", triggerWarehouseSearch);

  // Kích hoạt nút bấm tải file bằng liên kết chuyển hướng thẳng tới file PHP
  const btnExport = document.getElementById("btn-wh-export-excel");
  if (btnExport) {
    btnExport.onclick = function () {
      const searchInput = document.getElementById("wh-search-input");
      const catSelect = document.getElementById("wh-category-filter");
      const statusSelect = document.getElementById("wh-status-filter");

      const keyword = searchInput
        ? encodeURIComponent(searchInput.value.trim())
        : "";
      const selectedCat = catSelect ? encodeURIComponent(catSelect.value) : "";
      const selectedStatus = statusSelect
        ? encodeURIComponent(statusSelect.value)
        : "";

      // Chuyển hướng trình duyệt gọi file PHP xuất file Excel
      window.location.href = `api/export_excel.php?search=${keyword}&category=${selectedCat}&status=${selectedStatus}`;
    };
  }

  const whForm = document.getElementById("form-add-warehouse");
  if (whForm) {
    whForm.onsubmit = async function (e) {
      e.preventDefault();
      const btnSubmit = whForm.querySelector('button[type="submit"]');
      btnSubmit.disabled = true;

      const productData = {
        name: document.getElementById("wh-add-name").value,
        sku: document.getElementById("wh-sku").value,
        category_id: document.getElementById("wh-add-category").value,
        brand: document.getElementById("wh-add-brand").value,
        stock: document.getElementById("wh-add-quantity").value,
        unit: document.getElementById("wh-add-unit").value,
        min_alert: document.getElementById("wh-add-min-alert").value,
        location: document.getElementById("wh-add-location").value,
        mfg_date: document.getElementById("wh-add-mfg-date").value,
        exp_date: document.getElementById("wh-add-exp-date").value,
        price_in: document.getElementById("wh-add-price-in").value || 0,
        price_out: document.getElementById("wh-add-price-out").value || 0,
        description: document.getElementById("wh-add-note").value,
        specification: document.getElementById("wh-add-specification")
          ? document.getElementById("wh-add-specification").value
          : "",
        image_url: "default.png",
      };

      fetch("api/api_products.php", {
        method: isEditMode ? "PUT" : "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(productData),
      })
        .then((res) => res.json())
        .then((response) => {
          if (response.status === "success") {
            const modalInstance = bootstrap.Modal.getInstance(
              document.getElementById("modalAddWarehouse"),
            );
            if (modalInstance) modalInstance.hide();
            Swal.fire(
              "Thành công!",
              "Dữ liệu kho đã được đồng bộ chuẩn PHP.",
              "success",
            ).then(() => {
              loadProductsFromDB();
              btnSubmit.disabled = false;
            });
          }
        });
    };
  }
})();
