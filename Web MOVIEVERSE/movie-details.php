<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

$movieId = intval($_GET['id'] ?? 0);
if (!$movieId) {
    header("Location: index.php");
    exit;
}

$db = Database::getConnection();

// Fetch Movie Details
$stmt = $db->prepare("SELECT * FROM movies WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $movieId]);
$movie = $stmt->fetch();

if (!$movie) {
    header("Location: index.php");
    exit;
}

// Increment view count
$db->prepare("UPDATE movies SET view_count = view_count + 1 WHERE id = :id")->execute(['id' => $movieId]);

$pageTitle = $movie['title'];

// Fetch Genres for this movie
$stmtG = $db->prepare("
    SELECT g.* FROM genres g 
    JOIN movie_genres mg ON g.id = mg.genre_id 
    WHERE mg.movie_id = :id
");
$stmtG->execute(['id' => $movieId]);
$movieGenres = $stmtG->fetchAll();

// Check if user has favorited
$isFavorited = false;
$userRating = 0;
if (Auth::isLoggedIn()) {
    $uid = $_SESSION['user_id'];
    $fStmt = $db->prepare("SELECT id FROM favorites WHERE user_id = :u AND movie_id = :m");
    $fStmt->execute(['u' => $uid, 'm' => $movieId]);
    $isFavorited = (bool)$fStmt->fetch();

    $rStmt = $db->prepare("SELECT rating FROM ratings WHERE user_id = :u AND movie_id = :m");
    $rStmt->execute(['u' => $uid, 'm' => $movieId]);
    $userRating = floatval($rStmt->fetchColumn() ?: 0);
}

// Fetch Similar Movies (same genres)
$genreIds = array_column($movieGenres, 'id');
$similarMovies = [];
if (!empty($genreIds)) {
    $inClause = implode(',', array_map('intval', $genreIds));
    $similarMovies = $db->query("
        SELECT DISTINCT m.* FROM movies m
        JOIN movie_genres mg ON m.id = mg.movie_id
        WHERE mg.genre_id IN ({$inClause}) AND m.id != {$movieId} AND m.status = 'published'
        ORDER BY m.imdb_rating DESC
        LIMIT 6
    ")->fetchAll();
}

// Fetch Comments
$stmtCom = $db->prepare("
    SELECT c.*, u.username, u.full_name, u.avatar 
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.movie_id = :m
    ORDER BY c.created_at DESC
    LIMIT 20
");
$stmtCom->execute(['m' => $movieId]);
$comments = $stmtCom->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Cinematic Panoramic Backdrop Header -->
<div class="details-hero">
  <img src="<?= htmlspecialchars(!empty($movie['backdrop_url']) ? $movie['backdrop_url'] : $movie['poster_url']) ?>" class="details-backdrop" alt="Backdrop">
  <div class="details-backdrop-overlay"></div>

  <div class="details-wrapper">
    <!-- Poster with Glow -->
    <div class="details-poster-box">
      <img src="<?= htmlspecialchars($movie['poster_url']) ?>" class="details-poster-img" alt="<?= htmlspecialchars($movie['title']) ?>">
    </div>

    <!-- Movie Details Content -->
    <div class="details-info">
      <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;">
        <span class="badge-tag badge-purple"><?= $movie['type'] === 'series' ? 'PHIM BỘ' : 'PHIM LẺ' ?></span>
        <span class="badge-tag badge-rating">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <?= number_format($movie['imdb_rating'], 1) ?> IMDb
        </span>
        <span class="badge-tag" style="background:rgba(255,255,255,0.08);color:var(--text-secondary);">
          👁 <?= number_format($movie['view_count']) ?> lượt xem
        </span>
      </div>

      <h1 class="details-title-vn"><?= htmlspecialchars($movie['title']) ?></h1>
      <?php if (!empty($movie['original_title'])): ?>
        <h2 class="details-title-en"><?= htmlspecialchars($movie['original_title']) ?></h2>
      <?php endif; ?>

      <!-- Chips row -->
      <div class="meta-chips-row">
        <span class="meta-chip">📅 <?= $movie['release_year'] ?></span>
        <span class="meta-chip">⏱ <?= formatDuration($movie['duration_minutes']) ?></span>
        <span class="meta-chip">🌍 <?= htmlspecialchars($movie['country']) ?></span>
        <?php foreach ($movieGenres as $g): ?>
          <a href="movies.php?genre=<?= $g['slug'] ?>" class="meta-chip" style="background:rgba(139, 92, 246, 0.15);border-color:rgba(139, 92, 246, 0.3);">
            <?= htmlspecialchars($g['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Synopsis -->
      <p class="synopsis-text"><?= nl2br(htmlspecialchars($movie['description'] ?? '')) ?></p>

      <!-- Action Buttons -->
      <div style="display:flex;align-items:center;gap:1rem;margin-bottom:2rem;">
        <a href="watch.php?id=<?= $movie['id'] ?>" class="btn btn-primary" style="padding:0.9rem 2.2rem;font-size:1.05rem;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          <span>Xem Phim Ngay</span>
        </a>

        <?php if (!empty($movie['trailer_url'])): ?>
          <button type="button" class="btn btn-glass btn-open-trailer" data-trailer="<?= htmlspecialchars($movie['trailer_url']) ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8" fill="currentColor"/></svg>
            <span>Trailer</span>
          </button>
        <?php endif; ?>

        <button type="button" class="btn-icon-round btn-toggle-fav <?= $isFavorited ? 'active' : '' ?>" data-movie-id="<?= $movie['id'] ?>" title="Lưu phim yêu thích">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
        </button>
      </div>

      <!-- Cast & Crew -->
      <div class="crew-grid">
        <div class="crew-item">
          <h5>Đạo diễn</h5>
          <p><?= htmlspecialchars($movie['director'] ?? 'Đang cập nhật') ?></p>
        </div>
        <div class="crew-item">
          <h5>Diễn viên chính</h5>
          <p><?= htmlspecialchars($movie['cast'] ?? 'Đang cập nhật') ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Feedback Section: Rating & Comments -->
<section class="section-container">
  <div class="feedback-section">
    <!-- Comments Area -->
    <div class="comments-area">
      <h3 class="section-title" style="margin-bottom:1.5rem;">Bình luận & Thảo luận (<?= count($comments) ?>)</h3>

      <div class="comment-form">
        <?php if (Auth::isLoggedIn()): ?>
          <form id="comment-form-ajax">
            <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
            <textarea id="comment-text" name="content" class="comment-input" placeholder="Viết cảm nghĩ của bạn về bộ phim này..." required></textarea>
            <button type="submit" class="btn btn-primary" style="padding:0.6rem 1.5rem;font-size:0.875rem;">
              <span>Gửi bình luận</span>
            </button>
          </form>
        <?php else: ?>
          <div style="text-align:center;padding:1.5rem;">
            <p style="color:var(--text-muted);margin-bottom:1rem;">Vui lòng đăng nhập để tham gia bình luận và đánh giá phim</p>
            <a href="login.php" class="btn btn-primary">Đăng nhập ngay</a>
          </div>
        <?php endif; ?>
      </div>

      <div id="comments-container" class="comments-list">
        <?php foreach ($comments as $c): ?>
          <div class="comment-item">
            <img src="<?= htmlspecialchars($c['avatar'] ?? 'assets/images/default-avatar.png') ?>" class="comment-avatar" alt="Avatar">
            <div style="flex:1;">
              <div class="comment-author-row">
                <span class="comment-author"><?= htmlspecialchars($c['full_name'] ?? $c['username']) ?></span>
                <span class="comment-date"><?= timeAgo($c['created_at']) ?></span>
              </div>
              <p style="font-size:0.925rem;color:var(--text-secondary);"><?= nl2br(htmlspecialchars($c['content'])) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Rating Sidebar -->
    <div class="rating-sidebar">
      <div class="rating-widget">
        <h4 style="font-size:1.15rem;font-weight:700;">Đánh giá của bạn</h4>
        <p style="font-size:0.85rem;color:var(--text-muted);margin-top:0.35rem;">Chạm vào ngôi sao để chấm điểm</p>

        <div id="star-rating-box" class="star-rating-box" data-movie-id="<?= $movie['id'] ?>">
          <?php for ($i = 1; $i <= 10; $i++): ?>
            <span class="star <?= $i <= $userRating ? 'active' : '' ?>" data-val="<?= $i ?>" title="<?= $i ?>/10 sao">★</span>
          <?php endfor; ?>
        </div>

        <input type="hidden" id="selected-rating-val" value="<?= $userRating ?>">
        <div style="font-size:0.9rem;font-weight:700;color:var(--accent-purple);" id="rating-status-text">
          <?= $userRating > 0 ? "Bạn đã chấm: {$userRating}/10 sao" : "Chưa đánh giá" ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Similar Movies -->
<?php if (!empty($similarMovies)): ?>
  <section class="section-container" style="margin-top:4rem;">
    <div class="section-header">
      <h3 class="section-title">Phim tương tự có thể bạn thích</h3>
    </div>
    <div class="carousel-wrapper">
      <button class="carousel-btn prev" aria-label="Lùi">‹</button>
      <div class="carousel-track">
        <?php foreach ($similarMovies as $sim): 
          $movie = $sim;
          $isContinueWatching = false;
          include __DIR__ . '/includes/movie-card.php';
        endforeach; ?>
      </div>
      <button class="carousel-btn next" aria-label="Tiến">›</button>
    </div>
  </section>
<?php endif; ?>

<script>
// AJAX Comment Submission
document.getElementById('comment-form-ajax')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const textInput = document.getElementById('comment-text');
  const content = textInput.value.trim();
  if (!content) return;

  fetch('api/comments.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `movie_id=<?= $movieId ?>&content=${encodeURIComponent(content)}`
  })
  .then(res => res.json())
  .then(res => {
    if (res.success) {
      textInput.value = '';
      const c = res.comment;
      const html = `
        <div class="comment-item">
          <img src="${c.avatar}" class="comment-avatar" alt="Avatar">
          <div style="flex:1;">
            <div class="comment-author-row">
              <span class="comment-author">${c.user_name}</span>
              <span class="comment-date">${c.time_ago}</span>
            </div>
            <p style="font-size:0.925rem;color:var(--text-secondary);">${c.content}</p>
          </div>
        </div>
      `;
      document.getElementById('comments-container').insertAdjacentHTML('afterbegin', html);
      showToast('Đã gửi bình luận!');
    }
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
