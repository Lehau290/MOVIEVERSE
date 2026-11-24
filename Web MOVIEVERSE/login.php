<?php
$pageTitle = 'Đăng Nhập';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

if (Auth::isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
$redirect = sanitize($_GET['redirect'] ?? 'index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
    } else {
        if (Auth::login($username, $password)) {
            header("Location: " . $redirect);
            exit;
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng Nhập - MovieVerse</title>
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
        <h2 style="font-size:1.5rem;font-weight:700;">Chào mừng trở lại</h2>
        <p style="color:var(--text-muted);font-size:0.875rem;margin-top:0.25rem;">Đăng nhập để tiếp tục trải nghiệm điện ảnh đỉnh cao</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <!-- Quick Demo Credentials Hint Box -->
      <div style="background:rgba(139, 92, 246, 0.12);border:1px solid rgba(139, 92, 246, 0.3);border-radius:var(--radius-sm);padding:0.75rem 1rem;font-size:0.8rem;margin-bottom:1.5rem;">
        <div style="font-weight:700;color:var(--accent-purple);margin-bottom:0.25rem;">Tài khoản kiểm thử nhanh (Demo):</div>
        <div>👑 <b>Admin:</b> <code>admin</code> / pass: <code>admin123</code></div>
        <div>👤 <b>User:</b> <code>user</code> / pass: <code>user123</code></div>
      </div>

      <form method="POST">
        <div class="form-group">
          <label class="form-label">Tên đăng nhập hoặc Email</label>
          <input type="text" name="username" class="form-input" placeholder="admin hoặc user" required autofocus>
        </div>

        <div class="form-group">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <label class="form-label">Mật khẩu</label>
            <a href="#" style="font-size:0.8rem;color:var(--accent-purple);">Quên mật khẩu?</a>
          </div>
          <input type="password" name="password" class="form-input" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:1rem;padding:0.85rem;">
          Đăng Nhập
        </button>
      </form>

      <div style="text-align:center;margin-top:1.75rem;font-size:0.875rem;color:var(--text-muted);">
        Chưa có tài khoản? <a href="register.php" style="color:var(--accent-purple);font-weight:600;">Đăng ký ngay</a>
      </div>
    </div>
  </div>
</body>
</html>
