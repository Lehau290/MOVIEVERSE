<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

$currentUser = Auth::user();
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' - MovieVerse' : 'MovieVerse - Nền Tảng Xem Phim Trực Tuyến Đẳng Cấp' ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%238b5cf6'><path d='M18 4l2 4h-3l-2-4h-2l2 4h-3l-2-4H8l2 4H7L5 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4h-4z'/></svg>">
</head>
<body>
  <!-- Ambient Optics Canvas for ethereal glowing atmosphere -->
  <canvas id="ambient-glow-canvas" aria-hidden="true"></canvas>

  <!-- Glassmorphic Top Navigation Bar -->
  <header class="topbar">
    <div class="nav-left">
      <a href="index.php" class="brand-logo" aria-label="MovieVerse Home">
        <div class="brand-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="5 3 19 12 5 21 5 3"/>
          </svg>
        </div>
        <span class="brand-text">MovieVerse</span>
      </a>

      <ul class="nav-links">
        <li><a href="index.php" class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>">Trang chủ</a></li>
        <li><a href="movies.php?type=movie" class="nav-link <?= $activePage === 'movies' ? 'active' : '' ?>">Phim lẻ</a></li>
        <li><a href="movies.php?type=series" class="nav-link <?= $activePage === 'series' ? 'active' : '' ?>">Phim bộ</a></li>
        <li><a href="movies.php" class="nav-link <?= $activePage === 'all' ? 'active' : '' ?>">Khám phá</a></li>
        <?php if (Auth::isAdmin()): ?>
          <li><a href="admin/index.php" class="nav-link" style="color:var(--accent-pink);font-weight:700;">★ Quản Trị</a></li>
        <?php endif; ?>
      </ul>
    </div>

    <div class="nav-right">
      <!-- Live Search Box -->
      <div class="search-box-wrapper">
        <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" id="search-input" class="search-input" placeholder="Tìm tên phim, diễn viên..." autocomplete="off">
        <div id="search-suggestions" class="search-suggestions"></div>
      </div>

      <!-- Dark / Light Mode Switcher -->
      <button type="button" id="theme-toggle-btn" class="theme-toggle-btn" aria-label="Đổi giao diện">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
        </svg>
      </button>

      <!-- User Account Menu -->
      <?php if ($currentUser): ?>
        <div class="user-menu-wrapper">
          <button type="button" id="user-avatar-btn" class="user-avatar-btn">
            <img src="<?= htmlspecialchars($currentUser['avatar']) ?>" class="user-avatar-img" alt="Avatar">
            <span class="user-name-label"><?= htmlspecialchars($currentUser['full_name'] ?? $currentUser['username']) ?></span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </button>
          <div id="user-dropdown" class="user-dropdown">
            <a href="profile.php" class="dropdown-link">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <span>Hồ sơ cá nhân</span>
            </a>
            <a href="favorites.php" class="dropdown-link">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
              <span>Phim yêu thích</span>
            </a>
            <a href="history.php" class="dropdown-link">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span>Lịch sử xem</span>
            </a>
            <?php if (Auth::isAdmin()): ?>
              <div class="dropdown-divider"></div>
              <a href="admin/index.php" class="dropdown-link" style="color:var(--accent-purple);font-weight:600;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span>Bảng điều khiển Admin</span>
              </a>
            <?php endif; ?>
            <div class="dropdown-divider"></div>
            <a href="logout.php" class="dropdown-link" style="color:#f87171;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
              <span>Đăng xuất</span>
            </a>
          </div>
        </div>
      <?php else: ?>
        <a href="login.php" class="btn btn-glass" style="padding:0.5rem 1.1rem;font-size:0.875rem;">Đăng nhập</a>
        <a href="register.php" class="btn btn-primary" style="padding:0.5rem 1.1rem;font-size:0.875rem;">Đăng ký</a>
      <?php endif; ?>
    </div>
  </header>
