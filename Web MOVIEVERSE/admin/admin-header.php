<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

Auth::requireAdmin();
$currentUser = Auth::user();
$activeNav = $activeNav ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' - MovieVerse Admin' : 'Quản Trị - MovieVerse' ?></title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body style="background:var(--bg-base);">
  <div class="admin-layout">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
      <div class="admin-brand">
        <div class="brand-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        </div>
        <div>
          <div class="brand-text" style="font-size:1.15rem;font-weight:700;">MovieVerse</div>
          <div style="font-size:0.7rem;color:var(--accent-purple);font-weight:700;letter-spacing:1px;">ADMIN PANEL</div>
        </div>
      </div>

      <ul class="admin-nav-list">
        <li class="admin-nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
          <a href="index.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            <span>Tổng quan</span>
          </a>
        </li>
        <li class="admin-nav-item <?= $activeNav === 'movies' ? 'active' : '' ?>">
          <a href="movies.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="17" x2="22" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/></svg>
            <span>Quản lý Phim</span>
          </a>
        </li>
        <li class="admin-nav-item <?= $activeNav === 'genres' ? 'active' : '' ?>">
          <a href="genres.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 9h16"/><path d="M4 15h16"/><path d="M10 3L8 21"/><path d="M16 3l-2 18"/></svg>
            <span>Thể loại</span>
          </a>
        </li>
        <li class="admin-nav-item <?= $activeNav === 'banners' ? 'active' : '' ?>">
          <a href="banners.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <span>Banners Hero</span>
          </a>
        </li>
        <li class="admin-nav-item <?= $activeNav === 'users' ? 'active' : '' ?>">
          <a href="users.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Người dùng</span>
          </a>
        </li>
      </ul>

      <div style="padding-top:1rem;border-top:1px solid var(--border-subtle);">
        <a href="../index.php" style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;font-size:0.875rem;color:var(--text-muted);border-radius:var(--radius-sm);">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
          <span>Về trang chủ Web</span>
        </a>
      </div>
    </aside>

    <!-- Main Admin Container -->
    <main class="admin-main">
      <div class="admin-topbar">
        <div>
          <h2 style="font-family:'Space Grotesk',sans-serif;font-size:1.6rem;font-weight:700;"><?= $pageTitle ?? 'Bảng điều khiển' ?></h2>
          <p style="font-size:0.85rem;color:var(--text-muted);margin-top:0.2rem;">Hệ thống quản trị nội dung MovieVerse</p>
        </div>

        <div style="display:flex;align-items:center;gap:1.25rem;">
          <div style="display:flex;align-items:center;gap:0.75rem;">
            <img src="<?= htmlspecialchars($currentUser['avatar']) ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;" alt="Admin Avatar">
            <div>
              <div style="font-size:0.875rem;font-weight:600;"><?= htmlspecialchars($currentUser['full_name']) ?></div>
              <div style="font-size:0.75rem;color:var(--accent-purple);">Administrator</div>
            </div>
          </div>
          <a href="../logout.php" class="btn btn-glass" style="padding:0.45rem 0.85rem;font-size:0.8rem;color:#f87171;">Đăng xuất</a>
        </div>
      </div>
