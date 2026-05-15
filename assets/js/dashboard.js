(function () {
  const topProducts = [
    {
      name: "OnePlus 10 Pro",
      cat: "Điện thoại",
      price: "21.500.000 đ",
      status: "done",
      label: "Hoàn tất",
      icon: "📱",
      color: "#4361ee",
    },
    {
      name: "Magic Mouse",
      cat: "Phụ kiện",
      price: "2.400.000 đ",
      status: "done",
      label: "Hoàn tất",
      icon: "🖱️",
      color: "#10b981",
    },
    {
      name: "MacBook M3",
      cat: "Laptop",
      price: "45.000.000 đ",
      status: "pending",
      label: "Đang xử lý",
      icon: "💻",
      color: "#f59e0b",
    },
  ];

  const recentTrans = [
    {
      title: "Chuyển khoản Wallet",
      amount: "+ 2,500,000 đ",
      type: "in",
      date: "Vừa xong",
    },
    {
      title: "Thanh toán đơn #ORD-092",
      amount: "+ 15,200,000 đ",
      type: "in",
      date: "2 phút trước",
    },
    {
      title: "Chi phí vận hành",
      amount: "- 1,200,000 đ",
      type: "out",
      date: "15 phút trước",
    },
  ];

  function initDashboardCharts() {
    // 1. Biểu đồ Biến động Doanh thu & Lợi nhuận (Area Chart)
    const revenueEl = document.getElementById("dash-revenue-chart");
    if (revenueEl) {
      const revenueOptions = {
        series: [
          {
            name: "Doanh thu",
            data: [
              450, 520, 480, 610, 590, 720, 680, 810, 950, 880, 1020, 1150,
            ],
          },
          {
            name: "Lợi nhuận",
            data: [120, 150, 130, 210, 180, 250, 230, 310, 400, 350, 420, 510],
          },
        ],
        chart: {
          height: 350,
          type: "area",
          toolbar: { show: false },
          fontFamily: "inherit",
        },
        colors: ["#4361ee", "#10b981"],
        stroke: { curve: "smooth", width: 3 },
        dataLabels: {
          enabled: true,
          offsetY: -10,
          style: { fontSize: "10px", colors: ["#4361ee", "#10b981"] },
        },
        fill: {
          type: "gradient",
          gradient: { opacityFrom: 0.4, opacityTo: 0.1 },
        },
        xaxis: {
          categories: [
            "T1",
            "T2",
            "T3",
            "T4",
            "T5",
            "T6",
            "T7",
            "T8",
            "T9",
            "T10",
            "T11",
            "T12",
          ],
        },
        grid: { borderColor: "#f1f1f1", strokeDashArray: 4 },
        legend: { position: "bottom" },
      };
      new ApexCharts(revenueEl, revenueOptions).render();
    }

    // 2. Biểu đồ Doanh thu theo Danh mục (Donut Chart)
    const categoryEl = document.getElementById("dash-category-chart");
    if (categoryEl) {
      const categoryOptions = {
        series: [44, 32, 14, 10],
        chart: { type: "donut", height: 350, fontFamily: "inherit" },
        labels: ["Điện thoại", "Laptop", "Phụ kiện", "Khác"],
        colors: ["#4361ee", "#4cc9f0", "#f59e0b", "#7c3aed"],
        legend: { position: "bottom" },
        plotOptions: {
          pie: {
            donut: {
              size: "75%",
              labels: {
                show: true,
                total: { show: true, label: "Tổng", formatter: () => "100%" },
              },
            },
          },
        },
        dataLabels: { enabled: false },
      };
      new ApexCharts(categoryEl, categoryOptions).render();
    }
  }

  function renderDashboardData() {
    // Render Bảng sản phẩm
    const prodList = document.getElementById("dash-top-products");
    if (prodList) {
      prodList.innerHTML = topProducts
        .map(
          (p) => `
        <tr>
          <td class="ps-4">
            <div class="d-flex align-items-center">
              <div class="me-3 d-flex align-items-center justify-content-center rounded" style="background: ${p.color}20; width: 35px; height: 35px;">${p.icon}</div>
              <span class="fw-bold text-dark small">${p.name}</span>
            </div>
          </td>
          <td class="small">${p.cat}</td>
          <td class="fw-bold small">${p.price}</td>
          <td><span class="status-badge status-${p.status}">${p.label}</span></td>
          <td class="text-end pe-4">
            <button class="btn btn-light btn-sm rounded-circle border"><i class="fas fa-ellipsis-v"></i></button>
          </td>
        </tr>
      `,
        )
        .join("");
    }

    // Render Giao dịch
    const transList = document.getElementById("dash-recent-trans");
    if (transList) {
      transList.innerHTML = recentTrans
        .map(
          (t) => `
        <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-4 py-3">
          <div class="d-flex align-items-center">
            <div class="dash-icon-box ${t.type === "in" ? "bg-success-subtle text-success" : "bg-danger-subtle text-danger"} me-3">
              <i class="fas ${t.type === "in" ? "fa-arrow-down" : "fa-arrow-up"}"></i>
            </div>
            <div>
              <div class="fw-bold text-dark small">${t.title}</div>
              <small class="text-muted" style="font-size: 11px;">${t.date}</small>
            </div>
          </div>
          <span class="fw-bold ${t.type === "in" ? "text-success" : "text-danger"} small">${t.amount}</span>
        </li>
      `,
        )
        .join("");
    }

    // Khởi tạo biểu đồ
    if (typeof ApexCharts === "undefined") {
      const script = document.createElement("script");
      script.src = "https://cdn.jsdelivr.net/npm/apexcharts";
      script.onload = initDashboardCharts;
      document.head.appendChild(script);
    } else {
      initDashboardCharts();
    }
  }

  renderDashboardData();
})();
