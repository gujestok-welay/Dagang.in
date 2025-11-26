// Password match checker
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm_password");
const passwordMatch = document.getElementById("password-match");
const form = document.getElementById("registerForm");

function checkPasswordMatch() {
  if (confirmPassword.value.length === 0) {
    passwordMatch.textContent = "";
    return false;
  }
  if (password.value === confirmPassword.value) {
    passwordMatch.textContent = "✓ Password cocok";
    passwordMatch.className = "text-success";
    return true;
  } else {
    passwordMatch.textContent = "✗ Password tidak cocok";
    passwordMatch.className = "text-danger";
    return false;
  }
}
confirmPassword.addEventListener("input", checkPasswordMatch);
password.addEventListener("input", function () {
  if (confirmPassword.value.length > 0) {
    checkPasswordMatch();
  }
});
form.addEventListener("submit", function (e) {
  if (password.value !== confirmPassword.value) {
    e.preventDefault();
    alert("Password dan Konfirmasi Password tidak cocok!");
    return false;
  }
});
// Auto-redirect setelah registrasi sukses
// Pastikan kode di bawah ini hanya dieksekusi jika registrasi sukses
if (
  typeof registrationSuccess !== "undefined" &&
  registrationSuccess === true
) {
  setTimeout(function () {
    window.location.href = "login.php";
  }, 3000);
}
