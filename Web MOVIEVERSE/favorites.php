<?php
$pageTitle = 'Phim Yêu Thích';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

Auth::requireLogin();
$userId = $_SESSION['user_id'];
$db = Database::getConnection();

// Fetch favorites
$stmt = $db->prepare("
    SELECT m.*, f.created_at as favorited_at 
    FROM favorites f
    JOIN movies m ON f.movie_id = m.id
    WHERE f.user_id = :u
    ORDER BY f.created_at DESC
");
$stmt->execute(['u' => $userId]);
$favorites = $stmt->fetchAll();
$userFavorites = array_column($favorites, 'id');

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="section-container" style="margin:0 auto;">
    <h1 class="section-title">Danh Sách Phim Yêu Thích</h1>
    <p style="color:var(--text-muted);margin-top:0.35rem;">Bạn đang lưu <?= count($favorites) ?> bộ phim yêu thích</p>
  </div>
</div>

<main class="section-container">
  <?php if (empty($favorites)): ?>
    <div style="text-align:center;padding:5rem 0;color:var(--text-muted);">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem;color:var(--accent-pink);">
        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
      </svg>
      <h3>Danh sách yêu thích trống</h3>
      <p style="margin-top:0.5rem;">Hãy nhấn vào biểu tượng trái tim ở bất kỳ bộ phim nào để lưu vào đây nhé!</p>
      <a href="movies.php" class="btn btn-primary" style="margin-top:1.5rem;">Khám phá phim ngay</a>
    </div>
  <?php else: ?>
    <div class="movies-grid">
      <?php foreach ($favorites as $movie): 
        $isContinueWatching = false;
        include __DIR__ . '/includes/movie-card.php';
      endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
