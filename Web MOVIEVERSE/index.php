<?php
$pageTitle = 'Trang Chủ - Xem Phim Trực Tuyến Đẳng Cấp';
$activePage = 'home';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

$db = Database::getConnection();

// Fetch Hero Banners
$banners = $db->query("
    SELECT b.*, m.title as movie_title, m.original_title, m.imdb_rating, m.release_year, m.description as movie_desc, m.trailer_url, m.type as movie_type
    FROM banners b
    LEFT JOIN movies m ON b.movie_id = m.id
    WHERE b.is_active = 1
    ORDER BY b.order_num ASC
")->fetchAll();

// Fetch Continue Watching if logged in
$continueWatching = [];
$userFavorites = [];
if (Auth::isLoggedIn()) {
    $uid = $_SESSION['user_id'];
    $stmtHist = $db->prepare("
        SELECT h.*, m.title, m.original_title, m.backdrop_url, m.poster_url, m.type, m.duration_minutes, m.imdb_rating, m.release_year, m.country
        FROM watch_history h
        JOIN movies m ON h.movie_id = m.id
        WHERE h.user_id = :u AND h.progress_seconds > 0
        ORDER BY h.last_watched DESC
        LIMIT 6
    ");
    $stmtHist->execute(['u' => $uid]);
    $continueWatching = $stmtHist->fetchAll();

    // Fetch user favorites ids for active state
    $stmtFav = $db->prepare("SELECT movie_id FROM favorites WHERE user_id = :u");
    $stmtFav->execute(['u' => $uid]);
    $userFavorites = $stmtFav->fetchAll(PDO::FETCH_COLUMN);
}

// Fetch Trending Movies (by views)
$trending = $db->query("
    SELECT * FROM movies 
    WHERE status = 'published' 
    ORDER BY view_count DESC 
    LIMIT 10
")->fetchAll();

// Fetch New Releases (2024 / recent)
$newReleases = $db->query("
    SELECT * FROM movies 
    WHERE status = 'published' 
    ORDER BY release_year DESC, id DESC 
    LIMIT 10
")->fetchAll();

// Fetch Top Rated (rating desc)
$topRated = $db->query("
    SELECT * FROM movies 
    WHERE status = 'published' 
    ORDER BY imdb_rating DESC 
    LIMIT 10
")->fetchAll();

// Fetch Sci-Fi & Action movies
$sciFiAction = $db->query("
    SELECT DISTINCT m.* FROM movies m
    JOIN movie_genres mg ON m.id = mg.movie_id
    JOIN genres g ON mg.genre_id = g.id
    WHERE g.slug IN ('khoa-hoc-vien-tuong', 'hanh-dong') AND m.status = 'published'
    ORDER BY m.imdb_rating DESC
    LIMIT 10
")->fetchAll();

// Fetch Anime & Animation
$animation = $db->query("
    SELECT DISTINCT m.* FROM movies m
    JOIN movie_genres mg ON m.id = mg.movie_id
    JOIN genres g ON mg.genre_id = g.id
    WHERE g.slug = 'hoat-hinh' AND m.status = 'published'
    ORDER BY m.id DESC
    LIMIT 10
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Banner Slider -->
<section class="hero-slider">
  <?php foreach ($banners as $idx => $b): ?>
    <div class="slide-item <?= $idx === 0 ? 'active' : '' ?>" data-index="<?= $idx ?>">
      <img src="<?= htmlspecialchars($b['image_url']) ?>" class="slide-bg-img" alt="<?= htmlspecialchars($b['title']) ?>">
      <div class="slide-overlay"></div>
      
      <div class="slide-content-container">
        <div class="slide-badges">
          <span class="badge-tag badge-purple">NỔI BẬT</span>
          <span class="badge-tag badge-cyan"><?= ($b['movie_type'] ?? 'movie') === 'series' ? 'PHIM BỘ' : 'PHIM LẺ' ?></span>
          <span class="badge-tag badge-rating">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <?= number_format($b['imdb_rating'] ?? 8.5, 1) ?> IMDb
          </span>
          <span class="badge-tag" style="background:rgba(255,255,255,0.1);color:#fff;"><?= $b['release_year'] ?? 2024 ?></span>
        </div>

        <h1 class="slide-title"><?= htmlspecialchars($b['title']) ?></h1>
        <div class="slide-subtitle"><?= htmlspecialchars($b['subtitle']) ?></div>
        <p class="slide-description"><?= htmlspecialchars($b['movie_desc'] ?? '') ?></p>

        <div class="slide-actions">
          <a href="watch.php?id=<?= $b['movie_id'] ?>" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            <span><?= htmlspecialchars($b['button_text']) ?></span>
          </a>

          <?php if (!empty($b['trailer_url'])): ?>
            <button type="button" class="btn btn-glass btn-open-trailer" data-trailer="<?= htmlspecialchars($b['trailer_url']) ?>">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8" fill="currentColor"/></svg>
              <span>Xem Trailer</span>
            </button>
          <?php endif; ?>

          <a href="movie-details.php?id=<?= $b['movie_id'] ?>" class="btn btn-glass">
            <span>Chi tiết</span>
          </a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <!-- Dots pagination -->
  <div class="slider-pagination">
    <?php foreach ($banners as $idx => $b): ?>
      <span class="pagination-dot <?= $idx === 0 ? 'active' : '' ?>" data-index="<?= $idx ?>"></span>
    <?php endforeach; ?>
  </div>
</section>

<!-- 2. Continue Watching (if available) -->
<?php if (!empty($continueWatching)): ?>
  <section class="section-container">
    <div class="section-header">
      <div class="section-title-wrap">
        <h2 class="section-title">Tiếp tục xem</h2>
        <span class="section-badge"><?= count($continueWatching) ?> phim</span>
      </div>
    </div>

    <div class="carousel-wrapper">
      <button class="carousel-btn prev" aria-label="Lùi">‹</button>
      <div class="carousel-track">
        <?php foreach ($continueWatching as $hist): 
          $movie = $hist;
          $isContinueWatching = true;
          $progressPercent = ($hist['duration_seconds'] > 0) ? round(($hist['progress_seconds'] / $hist['duration_seconds']) * 100) : 15;
          include __DIR__ . '/includes/movie-card.php';
        endforeach; ?>
      </div>
      <button class="carousel-btn next" aria-label="Tiến">›</button>
    </div>
  </section>
<?php endif; ?>

<!-- 3. Trending Now -->
<section class="section-container">
  <div class="section-header">
    <div class="section-title-wrap">
      <h2 class="section-title">Phim đang thịnh hành</h2>
      <span class="section-badge" style="background:rgba(236, 72, 153, 0.15);color:var(--accent-pink);">HOT 🔥</span>
    </div>
    <a href="movies.php?sort=views" class="view-all-link">Xem tất cả <span>→</span></a>
  </div>

  <div class="carousel-wrapper">
    <button class="carousel-btn prev" aria-label="Lùi">‹</button>
    <div class="carousel-track">
      <?php foreach ($trending as $movie): 
        $isContinueWatching = false;
        include __DIR__ . '/includes/movie-card.php';
      endforeach; ?>
    </div>
    <button class="carousel-btn next" aria-label="Tiến">›</button>
  </div>
</section>

<!-- 4. New Releases -->
<section class="section-container">
  <div class="section-header">
    <div class="section-title-wrap">
      <h2 class="section-title">Phim mới cập nhật</h2>
      <span class="section-badge">MỚI</span>
    </div>
    <a href="movies.php?sort=latest" class="view-all-link">Xem tất cả <span>→</span></a>
  </div>

  <div class="carousel-wrapper">
    <button class="carousel-btn prev" aria-label="Lùi">‹</button>
    <div class="carousel-track">
      <?php foreach ($newReleases as $movie): 
        $isContinueWatching = false;
        include __DIR__ . '/includes/movie-card.php';
      endforeach; ?>
    </div>
    <button class="carousel-btn next" aria-label="Tiến">›</button>
  </div>
</section>

<!-- 5. Top Rated Movies -->
<section class="section-container">
  <div class="section-header">
    <div class="section-title-wrap">
      <h2 class="section-title">Phim được đánh giá cao</h2>
      <span class="section-badge" style="background:rgba(245, 158, 11, 0.15);color:var(--accent-amber);">★ 8.5+</span>
    </div>
    <a href="movies.php?sort=rating" class="view-all-link">Xem tất cả <span>→</span></a>
  </div>

  <div class="carousel-wrapper">
    <button class="carousel-btn prev" aria-label="Lùi">‹</button>
    <div class="carousel-track">
      <?php foreach ($topRated as $movie): 
        $isContinueWatching = false;
        include __DIR__ . '/includes/movie-card.php';
      endforeach; ?>
    </div>
    <button class="carousel-btn next" aria-label="Tiến">›</button>
  </div>
</section>

<!-- 6. Sci-Fi & Action -->
<section class="section-container">
  <div class="section-header">
    <div class="section-title-wrap">
      <h2 class="section-title">Vũ trụ Khoa Học Viễn Tưởng & Hành Động</h2>
    </div>
    <a href="movies.php?genre=khoa-hoc-vien-tuong" class="view-all-link">Xem thêm <span>→</span></a>
  </div>

  <div class="carousel-wrapper">
    <button class="carousel-btn prev" aria-label="Lùi">‹</button>
    <div class="carousel-track">
      <?php foreach ($sciFiAction as $movie): 
        $isContinueWatching = false;
        include __DIR__ . '/includes/movie-card.php';
      endforeach; ?>
    </div>
    <button class="carousel-btn next" aria-label="Tiến">›</button>
  </div>
</section>

<!-- 7. Anime & Animation -->
<section class="section-container">
  <div class="section-header">
    <div class="section-title-wrap">
      <h2 class="section-title">Hoạt Hình & Anime Đỉnh Cao</h2>
    </div>
    <a href="movies.php?genre=hoat-hinh" class="view-all-link">Xem thêm <span>→</span></a>
  </div>

  <div class="carousel-wrapper">
    <button class="carousel-btn prev" aria-label="Lùi">‹</button>
    <div class="carousel-track">
      <?php foreach ($animation as $movie): 
        $isContinueWatching = false;
        include __DIR__ . '/includes/movie-card.php';
      endforeach; ?>
    </div>
    <button class="carousel-btn next" aria-label="Tiến">›</button>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
