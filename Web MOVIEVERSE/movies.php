<?php
$pageTitle = 'Danh Sách Phim';
$activePage = 'all';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

$db = Database::getConnection();

// Query Parameters
$type = sanitize($_GET['type'] ?? '');
if ($type === 'movie') $activePage = 'movies';
if ($type === 'series') $activePage = 'series';

$selectedGenre = sanitize($_GET['genre'] ?? '');
$selectedCountry = sanitize($_GET['country'] ?? '');
$selectedYear = intval($_GET['year'] ?? 0);
$selectedMinRating = floatval($_GET['rating'] ?? 0);
$sort = sanitize($_GET['sort'] ?? 'latest');
$search = sanitize($_GET['q'] ?? '');

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Fetch all genres for filter dropdown
$genres = $db->query("SELECT * FROM genres ORDER BY name ASC")->fetchAll();

// Fetch distinct countries
$countries = $db->query("SELECT DISTINCT country FROM movies WHERE country IS NOT NULL ORDER BY country ASC")->fetchAll(PDO::FETCH_COLUMN);

// Fetch distinct years
$years = $db->query("SELECT DISTINCT release_year FROM movies ORDER BY release_year DESC")->fetchAll(PDO::FETCH_COLUMN);

// Build SQL query
$conditions = ["m.status = 'published'"];
$params = [];

if (!empty($type)) {
    $conditions[] = "m.type = :type";
    $params['type'] = $type;
}

if (!empty($selectedGenre)) {
    $conditions[] = "g.slug = :genre";
    $params['genre'] = $selectedGenre;
}

if (!empty($selectedCountry)) {
    $conditions[] = "m.country = :country";
    $params['country'] = $selectedCountry;
}

if ($selectedYear > 0) {
    $conditions[] = "m.release_year = :year";
    $params['year'] = $selectedYear;
}

if ($selectedMinRating > 0) {
    $conditions[] = "m.imdb_rating >= :min_rating";
    $params['min_rating'] = $selectedMinRating;
}

if (!empty($search)) {
    $conditions[] = "(m.title LIKE :search OR m.original_title LIKE :search OR m.cast LIKE :search)";
    $params['search'] = '%' . $search . '%';
}

$whereClause = implode(' AND ', $conditions);

// Sorting
$orderBy = "m.created_at DESC";
if ($sort === 'popular' || $sort === 'views') {
    $orderBy = "m.view_count DESC";
} elseif ($sort === 'rating') {
    $orderBy = "m.imdb_rating DESC";
} elseif ($sort === 'year') {
    $orderBy = "m.release_year DESC";
}

// Total count
$countSql = "
    SELECT COUNT(DISTINCT m.id) 
    FROM movies m
    LEFT JOIN movie_genres mg ON m.id = mg.movie_id
    LEFT JOIN genres g ON mg.genre_id = g.id
    WHERE {$whereClause}
";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$totalMovies = $countStmt->fetchColumn();
$totalPages = ceil($totalMovies / $limit);

// Fetch movies
$dataSql = "
    SELECT DISTINCT m.* 
    FROM movies m
    LEFT JOIN movie_genres mg ON m.id = mg.movie_id
    LEFT JOIN genres g ON mg.genre_id = g.id
    WHERE {$whereClause}
    ORDER BY {$orderBy}
    LIMIT {$limit} OFFSET {$offset}
";
$stmt = $db->prepare($dataSql);
$stmt->execute($params);
$movies = $stmt->fetchAll();

// User favorites
$userFavorites = [];
if (Auth::isLoggedIn()) {
    $favStmt = $db->prepare("SELECT movie_id FROM favorites WHERE user_id = :u");
    $favStmt->execute(['u' => $_SESSION['user_id']]);
    $userFavorites = $favStmt->fetchAll(PDO::FETCH_COLUMN);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="section-container" style="margin:0 auto;">
    <h1 class="section-title" style="font-size:2.2rem;">
      <?= !empty($search) ? 'Kết quả tìm kiếm cho: "' . htmlspecialchars($search) . '"' : ($type === 'series' ? 'Phim Bộ Chọn Lọc' : ($type === 'movie' ? 'Phim Lẻ Chiếu Rạp' : 'Kho Phim MovieVerse')) ?>
    </h1>
    <p style="color:var(--text-muted);margin-top:0.35rem;">Khám phá <?= $totalMovies ?> tác phẩm điện ảnh chất lượng cao</p>

    <!-- Filters Bar -->
    <form method="GET" action="movies.php" class="filter-bar">
      <?php if (!empty($search)): ?>
        <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
      <?php endif; ?>

      <!-- Filter Type -->
      <div class="filter-group">
        <label class="filter-label">Loại:</label>
        <select name="type" class="filter-select" onchange="this.form.submit()">
          <option value="">Tất cả</option>
          <option value="movie" <?= $type === 'movie' ? 'selected' : '' ?>>Phim lẻ</option>
          <option value="series" <?= $type === 'series' ? 'selected' : '' ?>>Phim bộ</option>
        </select>
      </div>

      <!-- Filter Genre -->
      <div class="filter-group">
        <label class="filter-label">Thể loại:</label>
        <select name="genre" class="filter-select" onchange="this.form.submit()">
          <option value="">Tất cả thể loại</option>
          <?php foreach ($genres as $g): ?>
            <option value="<?= htmlspecialchars($g['slug']) ?>" <?= $selectedGenre === $g['slug'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($g['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Filter Country -->
      <div class="filter-group">
        <label class="filter-label">Quốc gia:</label>
        <select name="country" class="filter-select" onchange="this.form.submit()">
          <option value="">Tất cả quốc gia</option>
          <?php foreach ($countries as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= $selectedCountry === $c ? 'selected' : '' ?>>
              <?= htmlspecialchars($c) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Filter Year -->
      <div class="filter-group">
        <label class="filter-label">Năm:</label>
        <select name="year" class="filter-select" onchange="this.form.submit()">
          <option value="">Tất cả năm</option>
          <?php foreach ($years as $y): ?>
            <option value="<?= $y ?>" <?= $selectedYear === $y ? 'selected' : '' ?>><?= $y ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Filter Rating -->
      <div class="filter-group">
        <label class="filter-label">Đánh giá:</label>
        <select name="rating" class="filter-select" onchange="this.form.submit()">
          <option value="">Mọi điểm</option>
          <option value="8.0" <?= $selectedMinRating >= 8.0 ? 'selected' : '' ?>>★ 8.0 trở lên</option>
          <option value="7.0" <?= $selectedMinRating == 7.0 ? 'selected' : '' ?>>★ 7.0 trở lên</option>
        </select>
      </div>

      <!-- Sort -->
      <div class="filter-group" style="margin-left:auto;">
        <label class="filter-label">Sắp xếp:</label>
        <select name="sort" class="filter-select" onchange="this.form.submit()">
          <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Mới nhất</option>
          <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Xem nhiều nhất</option>
          <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Điểm cao nhất</option>
        </select>
      </div>
    </form>
  </div>
</div>

<main class="section-container">
  <?php if (empty($movies)): ?>
    <div style="text-align:center;padding:5rem 0;color:var(--text-muted);">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem;color:var(--accent-purple);">
        <circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/>
      </svg>
      <h3>Không tìm thấy phim phù hợp</h3>
      <p style="margin-top:0.5rem;">Hãy thử thay đổi điều kiện lọc hoặc từ khóa tìm kiếm</p>
      <a href="movies.php" class="btn btn-primary" style="margin-top:1.5rem;">Đặt lại bộ lọc</a>
    </div>
  <?php else: ?>
    <div class="movies-grid">
      <?php foreach ($movies as $movie): 
        $isContinueWatching = false;
        include __DIR__ . '/includes/movie-card.php';
      endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): 
          $queryParams = $_GET;
          $queryParams['page'] = $i;
          $pageUrl = 'movies.php?' . http_build_query($queryParams);
        ?>
          <a href="<?= $pageUrl ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
