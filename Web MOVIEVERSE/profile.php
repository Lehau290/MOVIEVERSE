<?php
$pageTitle = 'Hồ Sơ Cá Nhân';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

Auth::requireLogin();
$userId = $_SESSION['user_id'];
$db = Database::getConnection();

$message = '';
$error = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $fullName = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $avatar = sanitize($_POST['avatar'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email không hợp lệ.';
        } else {
            $upd = $db->prepare("UPDATE users SET full_name = :fn, email = :e, avatar = :a WHERE id = :id");
            $upd->execute(['fn' => $fullName, 'e' => $email, 'a' => $avatar, 'id' => $userId]);
            $_SESSION['full_name'] = $fullName;
            $_SESSION['avatar'] = $avatar;
            $_SESSION['email'] = $email;
            $message = 'Cập nhật thông tin thành công!';
        }
    } elseif (isset($_POST['change_password'])) {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        $uStmt = $db->prepare("SELECT password FROM users WHERE id = :id");
        $uStmt->execute(['id' => $userId]);
        $hash = $uStmt->fetchColumn();

        if (!password_verify($currentPass, $hash)) {
            $error = 'Mật khẩu hiện tại không chính xác.';
        } elseif (strlen($newPass) < 6) {
            $error = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
        } elseif ($newPass !== $confirmPass) {
            $error = 'Xác nhận mật khẩu mới không khớp.';
        } else {
            $newHash = password_hash($newPass, PASSWORD_BCRYPT);
            $pUpd = $db->prepare("UPDATE users SET password = :p WHERE id = :id");
            $pUpd->execute(['p' => $newHash, 'id' => $userId]);
            $message = 'Đổi mật khẩu thành công!';
        }
    }
}

// Fetch current user
$user = Auth::user();

// Stats
$favCount = $db->query("SELECT COUNT(*) FROM favorites WHERE user_id = {$userId}")->fetchColumn();
$watchCount = $db->query("SELECT COUNT(*) FROM watch_history WHERE user_id = {$userId}")->fetchColumn();
$ratingCount = $db->query("SELECT COUNT(*) FROM ratings WHERE user_id = {$userId}")->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="section-container" style="margin:0 auto;">
    <h1 class="section-title">Hồ Sơ Của Tôi</h1>
    <p style="color:var(--text-muted);margin-top:0.35rem;">Quản lý thông tin cá nhân và bảo mật tài khoản</p>
  </div>
</div>

<main class="section-container">
  <?php if (!empty($message)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 2fr;gap:2.5rem;">
    <!-- Profile Card & Stats -->
    <div>
      <div style="background:var(--bg-card);border:1px solid var(--border-subtle);border-radius:var(--radius-md);padding:2rem;text-align:center;">
        <img src="<?= htmlspecialchars($user['avatar']) ?>" style="width:110px;height:110px;border-radius:50%;object-fit:cover;margin:0 auto 1.25rem;box-shadow:var(--shadow-glow);border:2px solid var(--accent-purple);" alt="Avatar">
        <h3 style="font-size:1.35rem;font-weight:700;"><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></h3>
        <p style="color:var(--text-muted);font-size:0.875rem;margin-top:0.25rem;">@<?= htmlspecialchars($user['username']) ?></p>
        <span class="badge-tag badge-purple" style="display:inline-block;margin-top:0.75rem;"><?= strtoupper($user['role']) ?></span>

        <!-- Activity Counters -->
        <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:1rem;margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--border-subtle);">
          <div>
            <div style="font-size:1.4rem;font-weight:800;color:var(--accent-purple);"><?= $favCount ?></div>
            <div style="font-size:0.75rem;color:var(--text-muted);">Yêu thích</div>
          </div>
          <div>
            <div style="font-size:1.4rem;font-weight:800;color:var(--accent-cyan);"><?= $watchCount ?></div>
            <div style="font-size:0.75rem;color:var(--text-muted);">Đã xem</div>
          </div>
          <div>
            <div style="font-size:1.4rem;font-weight:800;color:var(--accent-pink);"><?= $ratingCount ?></div>
            <div style="font-size:0.75rem;color:var(--text-muted);">Đánh giá</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Forms -->
    <div>
      <!-- Update Info Form -->
      <div style="background:var(--bg-card);border:1px solid var(--border-subtle);border-radius:var(--radius-md);padding:2rem;margin-bottom:2rem;">
        <h3 style="font-size:1.15rem;font-weight:700;margin-bottom:1.5rem;">Cập nhật thông tin</h3>
        <form method="POST">
          <input type="hidden" name="update_profile" value="1">
          <div class="form-group">
            <label class="form-label">Tên hiển thị</label>
            <input type="text" name="full_name" class="form-input" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Đường dẫn ảnh đại diện (Avatar URL)</label>
            <input type="url" name="avatar" class="form-input" value="<?= htmlspecialchars($user['avatar']) ?>">
          </div>
          <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;">Lưu thông tin</button>
        </form>
      </div>

      <!-- Change Password Form -->
      <div style="background:var(--bg-card);border:1px solid var(--border-subtle);border-radius:var(--radius-md);padding:2rem;">
        <h3 style="font-size:1.15rem;font-weight:700;margin-bottom:1.5rem;">Đổi mật khẩu</h3>
        <form method="POST">
          <input type="hidden" name="change_password" value="1">
          <div class="form-group">
            <label class="form-label">Mật khẩu hiện tại</label>
            <input type="password" name="current_password" class="form-input" required>
          </div>
          <div class="form-group">
            <label class="form-label">Mật khẩu mới</label>
            <input type="password" name="new_password" class="form-input" minlength="6" required>
          </div>
          <div class="form-group">
            <label class="form-label">Xác nhận mật khẩu mới</label>
            <input type="password" name="confirm_password" class="form-input" minlength="6" required>
          </div>
          <button type="submit" class="btn btn-glass" style="margin-top:0.5rem;">Cập nhật mật khẩu</button>
        </form>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
