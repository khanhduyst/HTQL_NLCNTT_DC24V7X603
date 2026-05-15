// assets/js/main.js

window.showFire = function (text, icon = "success", title = "Thông báo") {
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
  });

  Toast.fire({
    icon: icon,
    title: title,
    text: text,
    customClass: {
      popup: "shadow-lg border-0 rounded-3",
      title: "fw-bold fs-6",
    },
  });
};

const navigate = (moduleName) => {
  const container = document.getElementById("app-container");
  container.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>`;

  fetch(`views/${moduleName}.php`)
    .then((res) => {
      if (!res.ok) throw new Error();
      return res.text();
    })
    .then((html) => {
      container.innerHTML = html;

      // Nạp CSS tương ứng với module
      if (!document.getElementById(`css-${moduleName}`)) {
        const link = document.createElement("link");
        link.id = `css-${moduleName}`;
        link.rel = "stylesheet";
        link.href = `assets/css/${moduleName}.css`;
        document.head.appendChild(link);
      }

      // Nạp và thực thi JS tương ứng với module
      const oldScript = document.getElementById(`js-${moduleName}`);
      if (oldScript) oldScript.remove();

      const script = document.createElement("script");
      script.id = `js-${moduleName}`;
      script.src = `assets/js/${moduleName}.js?v=${new Date().getTime()}`;
      document.body.appendChild(script);

      // Cập nhật trạng thái Active trên Sidebar
      document.querySelectorAll("#main-sidebar .nav-link").forEach((link) => {
        link.classList.remove("active");
        const onclickAttr = link.getAttribute("onclick");
        if (onclickAttr && onclickAttr.includes(moduleName)) {
          link.classList.add("active");
        }
      });

      // Cập nhật Hash trên thanh địa chỉ
      if (location.hash !== `#${moduleName}`) {
        location.hash = moduleName;
      }
    })
    .catch((err) => {
      container.innerHTML = `<div class="alert alert-danger mt-3">Lỗi tải module: ${moduleName}</div>`;
    });
};

$(document).ready(() => {
  // Khởi tạo trang dựa trên Hash hoặc mặc định là Dashboard
  const initHash = location.hash.replace("#", "");
  navigate(initHash || "dashboard");

  // Xử lý đóng mở Sidebar
  $("#toggle-sidebar").on("click", function (e) {
    e.stopPropagation();
    if (window.innerWidth <= 992) {
      $("#main-sidebar").toggleClass("show-mobile");
    } else {
      $("#main-sidebar").toggleClass("collapsed");
    }
  });

  // Đóng sidebar khi click ra ngoài (trên mobile)
  $(document).on("click", function (e) {
    if (
      window.innerWidth <= 992 &&
      !$(e.target).closest("#main-sidebar").length &&
      !$(e.target).closest("#toggle-sidebar").length
    ) {
      $("#main-sidebar").removeClass("show-mobile");
    }
  });
});

// Xử lý khi bấm nút Back/Forward trên trình duyệt
window.onhashchange = function () {
  const hash = location.hash.replace("#", "");
  if (hash) {
    navigate(hash);
  }
};
