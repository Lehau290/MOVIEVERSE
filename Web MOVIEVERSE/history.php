<?php
$pageTitle = 'Lịch Sử Xem Phim';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

Auth::requireLogin();
$userId = $_SESSION['user_id'];
$db = Database::getConnection();

// Clear history action
if (isset($_POST['clear_history'])) {
    $del = $db->prepare("DELETE FROM watch_history WHERE user_id = :u");
    $del->execute(['u' => $userId]);
    header("Location: history.php");
    exit;
}

// Fetch Watch History
$stmt = $db->prepare("
    SELECT h.*, m.title, m.original_title, m.poster_url, m.backdrop_url, m.type, m.country, m.release_year, m.imdb_rating, m.duration_minutes
    FROM watch_history h
    JOIN movies m ON h.movie_id = m.id
    WHERE h.user_id = :u
    ORDER BY h.last_watched DESC
");
$stmt->execute(['u' => $userId]);
$history = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="section-container" style="margin:0 auto;display:flex;justify-content:space-between;align-items:flex-end;">
    <div>
      <h1 class="section-title">Lịch Sử Xem Phim</h1>
      <p style="color:var(--text-muted);margin-top:0.35rem;">Tiếp tục thưởng thức các bộ phim bạn đang theo dõi</p>
    </div>
    <?php if (!empty($history)): ?>
      <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa toàn bộ lịch sử xem không?');">
        <button type="submit" name="clear_history" class="btn btn-glass" style="color:#f87171;border-color:rgba(239,68,68,0.3);">
          <span>Xóa tất cả lịch sử</span>
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>

<main class="section-container">
  <?php if (empty($history)): ?>
    <div style="text-align:center;padding:5rem 0;color:var(--text-muted);">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem;color:var(--accent-purple);">
        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
      </svg>
      <h3>Chưa có lịch sử xem phim</h3>
      <p style="margin-top:0.5rem;">Khi bạn bắt đầu xem bất kỳ phim nào, tiến độ sẽ được tự động lưu tại đây</p>
      <a href="index.php" class="btn btn-primary" style="margin-top:1.5rem;">Xem phim ngay</a>
    </div>
  <?php else: ?>
    <div class="movies-grid">
      <?php foreach ($history as $h): 
        $movie = $h;
        $isContinueWatching = true;
        $progressPercent = ($h['duration_seconds'] > 0) ? round(($h['progress_seconds'] / $h['duration_seconds']) * 100) : 10;
        include __DIR__ . '/includes/movie-card.php';
      endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
