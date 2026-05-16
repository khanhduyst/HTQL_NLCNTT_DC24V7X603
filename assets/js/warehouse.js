(function () {
  let products = [];
  let isEditMode = false;
  let currentEditSku = "";

  function loadProductsFromDB() {
    const area = document.getElementById("wh-content-render");
    if (!area) return;

    fetch("api/api_products.php")
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

          if (filterSelect)
            filterSelect.innerHTML =
              '<option value="">Tất cả danh mục</option>';
          if (addSelect)
            addSelect.innerHTML =
              '<option value="">-- Chọn danh mục --</option>';

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

    const getProductImageHTML = (p, size = 35) => {
      if (
        p.image_url &&
        (p.image_url.startsWith("http://") ||
          p.image_url.startsWith("https://"))
      ) {
        return `<img src="${p.image_url}" alt="${p.name}" class="object-fit-cover rounded shadow-sm" style="width: ${size}px; height: ${size}px; min-width: ${size}px; border: 1px solid #e3e6f0;">`;
      }
      return `<div class="wh-img-box text-white d-flex align-items-center justify-content-center" 
                   style="background: ${p.color || "#4361ee"}; width: ${size}px; height: ${size}px; min-width: ${size}px; font-size: ${size / 2.5}px; border-radius: 6px;">
                  ${p.icon || '<i class="fas fa-box"></i>'}
              </div>`;
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

  window.openEditWarehouse = function (index) {
    const p = products[index];
    isEditMode = true;
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
    if (specInput) {
      specInput.value = p.specification || "";
    }

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
      text: `Sản phẩm [${p.sku}] - ${p.name} sẽ bị rút khỏi danh mục hiển thị, dữ liệu kho cũ vẫn được lưu trữ bảo mật.`,
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
                text: "Hệ thống đã ẩn sản phẩm và cập nhật lịch sử hệ thống.",
                confirmButtonColor: "#4361ee",
              }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire("Lỗi!", response.message, "error");
            }
          })
          .catch((err) => {
            Swal.fire("Lỗi!", "Không thể kết nối đến máy chủ API!", "error");
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
    });

    whModalEl.addEventListener("show.bs.modal", function (e) {
      if (isEditMode) return;

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
    });
  }

  window.showWhDetail = function (index) {
    const p = products[index];
    const modalBody = document.getElementById("wh-modal-body");
    if (!modalBody) return;

    const imgHTML =
      p.image_url &&
      (p.image_url.startsWith("http://") || p.image_url.startsWith("https://"))
        ? `<div class="position-relative overflow-hidden rounded-3 shadow-sm" style="height: 100%; min-height: 260px;">
            <img src="${p.image_url}" alt="${p.name}" class="w-100 h-100 object-fit-cover position-absolute top-0 start-0">
            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,0.75) 100%);"></div>
            <div class="position-absolute bottom-0 start-0 p-3 text-start w-100">
                <span class="badge bg-blur text-white mb-2 fw-bold tracking-wider" style="backdrop-filter: blur(8px); background: rgba(255,255,255,0.2); font-size: 11px; letter-spacing: 0.5px;">${p.sku}</span>
                <h4 class="fw-bold text-white mb-0" style="letter-spacing: -0.5px;">${p.name}</h4>
            </div>
         </div>`
        : `<div class="w-100 rounded-3 d-flex flex-column align-items-center justify-content-center text-white p-4 shadow-sm" 
              style="background: linear-gradient(135deg, ${p.color || "#4361ee"} 0%, #2b3a8a 100%); min-height: 260px;">
              <span class="mb-3" style="font-size: 64px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.15));">${p.icon || "📦"}</span>
              <span class="badge bg-white text-dark mb-1 fw-bold">${p.sku}</span>
              <h4 class="fw-bold text-white mb-0 mt-1">${p.name}</h4>
         </div>`;

    let mfgText = "-- / -- / ----";
    if (p.mfg_date && p.mfg_date !== "0000-00-00") {
      const d = new Date(p.mfg_date);
      if (!isNaN(d.getTime())) {
        mfgText =
          d.getDate().toString().padStart(2, "0") +
          "-" +
          (d.getMonth() + 1).toString().padStart(2, "0") +
          "-" +
          d.getFullYear();
      }
    }

    let expText = "-- / -- / ----";
    if (p.exp_date && p.exp_date !== "0000-00-00") {
      const d = new Date(p.exp_date);
      if (!isNaN(d.getTime())) {
        expText =
          d.getDate().toString().padStart(2, "0") +
          "-" +
          (d.getMonth() + 1).toString().padStart(2, "0") +
          "-" +
          d.getFullYear();
      }
    }

    const brandText = p.brand ? p.brand : "Chưa gắn thương hiệu";
    const locationText = p.location ? p.location : "Chưa xếp kệ hàng";
    const descText = p.description
      ? p.description
      : "Không có ghi chú lưu kho cho đợt nhập hàng này.";

    modalBody.innerHTML = `
      <div class="row g-4 text-start">
          <div class="col-md-5 d-flex flex-column justify-content-between">
              ${imgHTML}
          </div>

          <div class="col-md-7">
              <div class="row g-2">
                  <div class="col-6">
                      <div class="p-3 bg-light rounded-3 border-0 h-100" style="background-color: #f8f9fa !important;">
                          <small class="text-muted d-block mb-1 font-monospace text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Phân loại danh mục</small>
                          <span class="fw-bold text-dark d-block"><i class="fas fa-tag me-1.5 text-secondary small"></i> ${p.cat}</span>
                      </div>
                  </div>
                  <div class="col-6">
                      <div class="p-3 bg-light rounded-3 border-0 h-100" style="background-color: #f8f9fa !important;">
                          <small class="text-muted d-block mb-1 font-monospace text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Hãng / Thương hiệu</small>
                          <span class="fw-bold text-primary d-block"><i class="fas fa-copyright me-1.5 text-primary small"></i> ${brandText}</span>
                      </div>
                  </div>
                  <div class="col-6">
                      <div class="p-3 bg-light rounded-3 border-0 h-100" style="background-color: #f8f9fa !important;">
                          <small class="text-muted d-block mb-1 font-monospace text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Số lượng hiện tồn</small>
                          <span class="fw-bold text-dark d-block" style="font-size: 16px;"><i class="fas fa-layer-group me-1.5 text-secondary small"></i> ${p.stock} <span class="fw-normal text-muted small">${p.unit || "Cái"}</span></span>
                      </div>
                  </div>
                  <div class="col-6">
                      <div class="p-3 bg-light rounded-3 border-0 h-100" style="background-color: #f8f9fa !important;">
                          <small class="text-muted d-block mb-1 font-monospace text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Vị trí định vị kho</small>
                          <span class="fw-bold text-dark d-block"><i class="fas fa-map-marker-alt me-1.5 text-danger small"></i> ${locationText}</span>
                      </div>
                  </div>
                  <div class="col-6">
                      <div class="p-3 rounded-3 h-100" style="background-color: #f0fdf4;">
                          <small class="text-success d-block mb-1 font-monospace text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Giá vốn nhập vào</small>
                          <span class="fw-extrabold text-success d-block" style="font-size: 18px; letter-spacing: -0.5px;">${p.price} <small style="font-size: 12px;">đ</small></span>
                      </div>
                  </div>
                  <div class="col-6">
                      <div class="p-3 rounded-3 h-100" style="background-color: #fef2f2;">
                          <small class="text-danger d-block mb-1 font-monospace text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Giá niêm yết bán</small>
                          <span class="fw-extrabold text-danger d-block" style="font-size: 18px; letter-spacing: -0.5px;">${p.price_out ? Number(p.price_out).toLocaleString("vi-VN") : p.price} <small style="font-size: 12px;">đ</small></span>
                      </div>
                  </div>
                  <div class="col-12">
                      <div class="p-2.5 bg-light rounded-3 border-0 text-center" style="background-color: #eef2ff !important;">
                          <small class="text-muted d-block mb-0.5" style="font-size: 11px;">Quy cách / Dung tích hàng hóa</small>
                          <span class="fw-bold text-indigo small" style="color: #4f46e5;"><i class="fas fa-info-circle me-1"></i> ${p.specification ? p.specification : "Chưa cập nhật thông số"}</span>
                      </div>
                  </div>
                  <div class="col-6">
                      <div class="p-2.5 bg-light rounded-3 border-0 text-center" style="background-color: #fdfaf2 !important;">
                          <small class="text-muted d-block mb-0.5" style="font-size: 11px;">Ngày sản xuất (NSX)</small>
                          <span class="fw-bold text-dark small"><i class="far fa-calendar-alt me-1 text-warning"></i> ${mfgText}</span>
                      </div>
                  </div>
                  <div class="col-6">
                      <div class="p-2.5 bg-light rounded-3 border-0 text-center" style="background-color: #fff5f5 !important;">
                          <small class="text-muted d-block mb-0.5" style="font-size: 11px;">Hạn sử dụng (HSD)</small>
                          <span class="fw-bold text-danger small"><i class="far fa-calendar-times me-1 text-danger"></i> ${expText}</span>
                      </div>
                  </div>
              </div>
          </div>
          
          <div class="col-12 mt-3">
              <div class="p-3 rounded-3 border-0" style="background: rgba(241, 245, 249, 0.6); backdrop-filter: blur(4px);">
                  <span class="text-dark fw-bold small d-flex align-items-center mb-1.5"><i class="fas fa-comment-alt-lines me-2 text-primary"></i> Nhật ký & Ghi chú đợt nhập hàng:</span>
                  <p class="text-muted mb-0 small" style="line-height: 1.5; font-style: italic;">"${descText}"</p>
              </div>
          </div>
      </div>
      
      <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
          <div>
             <span class="badge text-uppercase tracking-wider px-2.5 py-1.5 stock-${p.status}" style="font-size: 10px;">${p.label}</span>
          </div>
          <button type="button" class="btn btn-dark rounded-pill px-4 btn-sm shadow-none" data-bs-dismiss="modal" style="font-size: 12px; font-weight: 600; background-color: #111827;">Đóng cửa sổ</button>
      </div>
    `;

    const modalEl = document.getElementById("wh-detail-modal");
    const modalInstance =
      bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modalInstance.show();
  };

  window.triggerReloadCategories = function () {
    loadCategoriesFromDB();
  };

  loadProductsFromDB();
  loadCategoriesFromDB();
  window.onresize = renderWarehouse;

  const whForm = document.getElementById("form-add-warehouse");
  if (whForm && whModalEl) {
    const btnSubmit = whForm.querySelector('button[type="submit"]');
    const inputSku = document.getElementById("wh-sku");
    const btnGenSku = document.getElementById("btn-wh-gen-sku");
    const originalText = btnSubmit.innerHTML;

    if (btnGenSku) {
      btnGenSku.onclick = function () {
        const random = Math.floor(10000 + Math.random() * 90000);
        inputSku.value = "WH-" + random;
        inputSku.classList.add("is-valid");
        setTimeout(() => inputSku.classList.remove("is-valid"), 500);
      };
    }

    whForm.onsubmit = async function (e) {
      e.preventDefault();

      if (!whForm.checkValidity()) {
        e.stopPropagation();
        whForm.classList.add("was-validated");
        return;
      }

      btnSubmit.disabled = true;
      btnSubmit.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span> Đang xử lý...';

      let finalImageUrl = "default.png";
      const imageInput = document.getElementById("wh-add-image");

      if (isEditMode) {
        const currentProduct = products.find((p) => p.sku === currentEditSku);
        if (currentProduct && currentProduct.image_url) {
          finalImageUrl = currentProduct.image_url;
        }
      }

      if (imageInput && imageInput.files.length > 0) {
        try {
          const cloudName = "dnjbvgejr";
          const uploadPreset = "htql_upload";

          const clData = new FormData();
          clData.append("file", imageInput.files[0]);
          clData.append("upload_preset", uploadPreset);

          const clRes = await fetch(
            `https://api.cloudinary.com/v1_1/${cloudName}/image/upload`,
            {
              method: "POST",
              body: clData,
            },
          );

          const clJson = await clRes.json();
          if (clJson.secure_url) {
            finalImageUrl = clJson.secure_url;
          }
        } catch (clErr) {
          console.error("Lỗi upload Cloudinary:", clErr);
        }
      }

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
        image_url: finalImageUrl,
      };

      fetch("api/api_products.php", {
        method: isEditMode ? "PUT" : "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(productData),
      })
        .then((res) => res.json())
        .then((response) => {
          if (response.status === "success") {
            btnSubmit.innerHTML = '<i class="fas fa-check me-2"></i> Đã xong!';
            btnSubmit.classList.replace("btn-primary", "btn-success");

            const modalInstance = bootstrap.Modal.getInstance(whModalEl);
            if (modalInstance) modalInstance.hide();

            Swal.fire({
              icon: "success",
              title: isEditMode
                ? "Cập nhật thành công!"
                : "Nhập kho thành công!",
              text: "Sản phẩm đã được cập nhật hệ thống.",
              confirmButtonText: "Tuyệt vời",
              confirmButtonColor: "#4361ee",
            }).then(() => {
              location.reload();
            });
          } else {
            btnSubmit.innerHTML = originalText;
            btnSubmit.disabled = false;
            Swal.fire("Lỗi!", response.message, "error");
          }
        })
        .catch((err) => {
          btnSubmit.innerHTML = originalText;
          btnSubmit.disabled = false;
          Swal.fire("Lỗi!", "Không thể kết nối đến máy chủ API!", "error");
        });
    };
  }
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
          Swal.fire(
            "Lỗi xử lý JSON!",
            "Backend trả về dữ liệu không chuẩn mã hóa.",
            "error",
          );
        }
      }
    })
    .catch((err) => {
      console.log("Lỗi kết nối gốc:", err);
      Swal.fire("Lỗi!", "Không thể kết nối đến máy chủ API!", "error");
    });
};
