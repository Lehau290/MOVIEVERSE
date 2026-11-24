<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$action = $_GET['action'] ?? '';
$msg = '';

if ($action === 'reseed') {
    $db = Database::getConnection();
    // Clear and reseed
    $driver = Database::getDriver();
    if ($driver === 'sqlite') {
        $sqliteFile = __DIR__ . '/database/movieverse.sqlite';
        if (file_exists($sqliteFile)) {
            unlink($sqliteFile);
        }
    } else {
        $db->exec("DROP TABLE IF EXISTS comments, ratings, watch_history, favorites, banners, episodes, movie_genres, genres, movies, users");
    }
    Database::ensureSchemaAndSeed();
    $msg = 'Đã khởi tạo và nạp lại toàn bộ dữ liệu mẫu thành công!';
}

try {
    $db = Database::getConnection();
    $driver = Database::getDriver();
    $movieCount = $db->query("SELECT COUNT(*) FROM movies")->fetchColumn();
    $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $genreCount = $db->query("SELECT COUNT(*) FROM genres")->fetchColumn();
    $bannerCount = $db->query("SELECT COUNT(*) FROM banners")->fetchColumn();
    $dbStatus = true;
} catch (Exception $e) {
    $dbStatus = false;
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <title>Khởi Tạo & Kiểm Tra Hệ Thống - MovieVerse</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .setup-box {
      max-width: 680px;
      margin: 4rem auto;
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-lg);
      padding: 2.5rem;
      box-shadow: var(--shadow-md);
    }
    .check-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.85rem 0;
      border-bottom: 1px solid var(--border-subtle);
    }
  </style>
</head>
<body>
  <div class="setup-box">
    <div style="text-align:center;margin-bottom:2rem;">
      <a href="index.php" class="brand-logo" style="justify-content:center;margin-bottom:1rem;">
        <div class="brand-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        </div>
        <span class="brand-text">MovieVerse</span>
      </a>
      <h2>Trạng Thái Hệ Thống & Cài Đặt</h2>
      <p style="color:var(--text-muted);margin-top:0.35rem;">Kiểm tra kết nối Cơ sở dữ liệu và dữ liệu mẫu</p>
    </div>

    <?php if (!empty($msg)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div style="margin-bottom:2rem;">
      <div class="check-item">
        <span>Kết nối Database</span>
        <span class="badge-tag <?= $dbStatus ? 'badge-cyan' : 'badge-pink' ?>">
          <?= $dbStatus ? 'ĐANG KẾT NỐI (' . strtoupper($driver) . ')' : 'LỖI KẾT NỐI' ?>
        </span>
      </div>
      <div class="check-item">
        <span>Số lượng phim trong kho</span>
        <b><?= $movieCount ?? 0 ?> phim</b>
      </div>
      <div class="check-item">
        <span>Thể loại phim</span>
        <b><?= $genreCount ?? 0 ?> thể loại</b>
      </div>
      <div class="check-item">
        <span>Tài khoản người dùng</span>
        <b><?= $userCount ?? 0 ?> tài khoản</b>
      </div>
      <div class="check-item">
        <span>Hero Banners</span>
        <b><?= $bannerCount ?? 0 ?> banners</b>
      </div>
    </div>

    <!-- Credentials Info -->
    <div style="background:rgba(139, 92, 246, 0.12);border:1px solid rgba(139, 92, 246, 0.3);border-radius:var(--radius-sm);padding:1rem;margin-bottom:2rem;">
      <div style="font-weight:700;color:var(--accent-purple);margin-bottom:0.5rem;">Tài khoản mặc định sẵn có:</div>
      <div>👑 <b>Admin:</b> <code>admin</code> / Mật khẩu: <code>admin123</code> (Toàn quyền quản trị)</div>
      <div>👤 <b>Thành viên:</b> <code>user</code> / Mật khẩu: <code>user123</code> (Xem phim, bình luận, yêu thích)</div>
    </div>

    <div style="display:flex;gap:1rem;justify-content:center;">
      <a href="index.php" class="btn btn-primary">Vào Trang Chủ</a>
      <a href="login.php" class="btn btn-glass">Đăng Nhập</a>
      <a href="setup.php?action=reseed" class="btn btn-glass" style="color:var(--accent-pink);" onclick="return confirm('Khởi tạo lại toàn bộ dữ liệu mẫu?');">Nạp Lại Dữ Liệu Mẫu</a>
    </div>
  </div>
</body>
</html>
