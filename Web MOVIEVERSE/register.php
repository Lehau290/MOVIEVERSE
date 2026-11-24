<?php
$pageTitle = 'Đăng Ký Tài Khoản';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

if (Auth::isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($password !== $confirmPassword) {
        $error = 'Xác nhận mật khẩu không khớp.';
    } else {
        $res = Auth::register($username, $email, $password, $fullName);
        if ($res['success']) {
            $success = $res['message'];
            // Auto login
            Auth::login($username, $password);
            header("Location: index.php");
            exit;
        } else {
            $error = $res['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng Ký - MovieVerse</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="auth-wrapper">
    <div class="auth-card">
      <div style="text-align:center;margin-bottom:2rem;">
        <a href="index.php" class="brand-logo" style="justify-content:center;margin-bottom:1rem;">
          <div class="brand-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <span class="brand-text">MovieVerse</span>
        </a>
        <h2 style="font-size:1.5rem;font-weight:700;">Tạo tài khoản mới</h2>
        <p style="color:var(--text-muted);font-size:0.875rem;margin-top:0.25rem;">Gia nhập cộng đồng người yêu điện ảnh chất lượng cao</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label class="form-label">Tên hiển thị (Họ và tên)</label>
          <input type="text" name="full_name" class="form-input" placeholder="Nguyễn Văn A" required>
        </div>

        <div class="form-group">
          <label class="form-label">Tên đăng nhập (Username)</label>
          <input type="text" name="username" class="form-input" placeholder="nguyenvana" minlength="3" required>
        </div>

        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-input" placeholder="vana@example.com" required>
        </div>

        <div class="form-group">
          <label class="form-label">Mật khẩu (ít nhất 6 ký tự)</label>
          <input type="password" name="password" class="form-input" minlength="6" placeholder="••••••••" required>
        </div>

        <div class="form-group">
          <label class="form-label">Xác nhận mật khẩu</label>
          <input type="password" name="confirm_password" class="form-input" minlength="6" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:1rem;padding:0.85rem;">
          Tạo Tài Khoản
        </button>
      </form>

      <div style="text-align:center;margin-top:1.75rem;font-size:0.875rem;color:var(--text-muted);">
        Đã có tài khoản? <a href="login.php" style="color:var(--accent-purple);font-weight:600;">Đăng nhập ngay</a>
      </div>
    </div>
  </div>
</body>
</html>
