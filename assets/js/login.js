document.getElementById("login-form").onsubmit = async function (e) {
  e.preventDefault();
  const userId = document.getElementById("login-id").value.trim();
  const userPass = document.getElementById("login-pass").value.trim();
  const btn = document.getElementById("btn-submit-login");
  const originalText = btn.innerHTML;
  console.log("Attempting login with:", { userId, userPass });
  btn.disabled = true;
  btn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span> Đang xác thực...';

  try {
    const response = await fetch("api/login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ username: userId, password: userPass }),
    });

    const result = await response.json();

    if (result.status === "success") {
      localStorage.setItem("erp_user", JSON.stringify(result.user_data));
      Swal.fire({
        icon: "success",
        title: "Thành công",
        showConfirmButton: false,
        timer: 1500,
      }).then(() => (window.location.href = "./"));
    } else {
      btn.disabled = false;
      btn.innerHTML = originalText;
      Swal.fire({ icon: "error", title: "Lỗi", text: result.message });
    }
  } catch (error) {
    btn.disabled = false;
    btn.innerHTML = originalText;
    Swal.fire({
      icon: "error",
      title: "Lỗi",
      text: "Không kết nối được server!",
    });
  }
};
