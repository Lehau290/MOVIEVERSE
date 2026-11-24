<?php
$pageTitle = 'Tổng Quan Hệ Thống';
$activeNav = 'dashboard';
require_once __DIR__ . '/admin-header.php';

$db = Database::getConnection();

// Statistics
$movieCount = $db->query("SELECT COUNT(*) FROM movies")->fetchColumn();
$userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalViews = $db->query("SELECT SUM(view_count) FROM movies")->fetchColumn() ?: 0;
$commentCount = $db->query("SELECT COUNT(*) FROM comments")->fetchColumn();

// Top 5 viewed movies
$topMovies = $db->query("SELECT * FROM movies ORDER BY view_count DESC LIMIT 5")->fetchAll();

// Latest registered users
$latestUsers = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!-- Stat Cards -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(139, 92, 246, 0.15);color:var(--accent-purple);">🎬</div>
    <div>
      <div class="stat-value"><?= number_format($movieCount) ?></div>
      <div class="stat-label">Tổng số phim</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(6, 182, 212, 0.15);color:var(--accent-cyan);">👥</div>
    <div>
      <div class="stat-value"><?= number_format($userCount) ?></div>
      <div class="stat-label">Người dùng</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(236, 72, 153, 0.15);color:var(--accent-pink);">👁</div>
    <div>
      <div class="stat-value"><?= number_format($totalViews) ?></div>
      <div class="stat-label">Tổng lượt xem</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(245, 158, 11, 0.15);color:var(--accent-amber);">💬</div>
    <div>
      <div class="stat-value"><?= number_format($commentCount) ?></div>
      <div class="stat-label">Bình luận</div>
    </div>
  </div>
</div>

<!-- Quick Action Bar -->
<div style="margin-bottom:2.5rem;display:flex;gap:1rem;">
  <a href="movie-form.php" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    <span>Thêm Phim Mới</span>
  </a>
  <a href="banners.php" class="btn btn-glass">
    <span>Quản Lý Banners</span>
  </a>
  <a href="genres.php" class="btn btn-glass">
    <span>Quản Lý Thể Loại</span>
  </a>
</div>

<!-- Tables Grid -->
<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:2rem;">
  <!-- Top Viewed Movies -->
  <div class="admin-table-card">
    <div class="admin-table-header">
      <h3 style="font-size:1.05rem;font-weight:700;">Phim Xem Nhiều Nhất</h3>
      <a href="movies.php" style="font-size:0.8rem;color:var(--accent-purple);">Xem tất cả →</a>
    </div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Phim</th>
          <th>Loại</th>
          <th>Lượt xem</th>
          <th>Đánh giá</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($topMovies as $m): ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:0.75rem;">
                <img src="<?= htmlspecialchars($m['poster_url']) ?>" class="table-thumb" style="width:36px;height:48px;" alt="Poster">
                <div>
                  <div style="font-weight:600;font-size:0.875rem;"><?= htmlspecialchars($m['title']) ?></div>
                  <div style="font-size:0.75rem;color:var(--text-muted);"><?= $m['release_year'] ?></div>
                </div>
              </div>
            </td>
            <td>
              <span class="badge-tag <?= $m['type'] === 'series' ? 'badge-cyan' : 'badge-purple' ?>" style="font-size:0.7rem;padding:0.15rem 0.5rem;">
                <?= $m['type'] === 'series' ? 'Bộ' : 'Lẻ' ?>
              </span>
            </td>
            <td><b><?= number_format($m['view_count']) ?></b></td>
            <td>★ <?= number_format($m['imdb_rating'], 1) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Latest Users -->
  <div class="admin-table-card">
    <div class="admin-table-header">
      <h3 style="font-size:1.05rem;font-weight:700;">Người Dùng Mới Nhất</h3>
      <a href="users.php" style="font-size:0.8rem;color:var(--accent-purple);">Xem tất cả →</a>
    </div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Tài khoản</th>
          <th>Vai trò</th>
          <th>Ngày tạo</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($latestUsers as $u): ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:0.65rem;">
                <img src="<?= htmlspecialchars($u['avatar']) ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" alt="Avatar">
                <div>
                  <div style="font-weight:600;font-size:0.85rem;"><?= htmlspecialchars($u['full_name'] ?? $u['username']) ?></div>
                  <div style="font-size:0.75rem;color:var(--text-muted);"><?= htmlspecialchars($u['email']) ?></div>
                </div>
              </div>
            </td>
            <td>
              <span class="badge-tag <?= $u['role'] === 'admin' ? 'badge-purple' : '' ?>" style="font-size:0.7rem;padding:0.15rem 0.5rem;">
                <?= strtoupper($u['role']) ?>
              </span>
            </td>
            <td style="color:var(--text-muted);font-size:0.75rem;"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
