<?php
require_once __DIR__ . '/admin-header.php';

$db = Database::getConnection();
$movieId = intval($_GET['id'] ?? 0);
$isEdit = ($movieId > 0);
$pageTitle = $isEdit ? 'Chỉnh Sửa Phim' : 'Thêm Phim Mới';

$movie = [
    'title' => '',
    'original_title' => '',
    'slug' => '',
    'type' => 'movie',
    'description' => '',
    'poster_url' => '',
    'backdrop_url' => '',
    'trailer_url' => '',
    'video_url' => '',
    'release_year' => 2024,
    'duration_minutes' => 120,
    'country' => 'Mỹ',
    'director' => '',
    'cast' => '',
    'imdb_rating' => 8.0,
    'is_featured' => 0,
    'status' => 'published'
];

$selectedGenres = [];

if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM movies WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $movieId]);
    $movie = $stmt->fetch();
    if (!$movie) {
        header("Location: movies.php");
        exit;
    }

    $gStmt = $db->prepare("SELECT genre_id FROM movie_genres WHERE movie_id = :id");
    $gStmt->execute(['id' => $movieId]);
    $selectedGenres = $gStmt->fetchAll(PDO::FETCH_COLUMN);
}

// Fetch all genres
$allGenres = $db->query("SELECT * FROM genres ORDER BY name ASC")->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $originalTitle = sanitize($_POST['original_title'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    if (empty($slug)) $slug = slugify($title);
    $type = sanitize($_POST['type'] ?? 'movie');
    $description = trim($_POST['description'] ?? '');
    $posterUrl = sanitize($_POST['poster_url'] ?? '');
    $backdropUrl = sanitize($_POST['backdrop_url'] ?? '');
    $trailerUrl = sanitize($_POST['trailer_url'] ?? '');
    $videoUrl = sanitize($_POST['video_url'] ?? '');
    $releaseYear = intval($_POST['release_year'] ?? 2024);
    $durationMinutes = intval($_POST['duration_minutes'] ?? 120);
    $country = sanitize($_POST['country'] ?? 'Mỹ');
    $director = sanitize($_POST['director'] ?? '');
    $cast = sanitize($_POST['cast'] ?? '');
    $imdbRating = floatval($_POST['imdb_rating'] ?? 8.0);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $status = sanitize($_POST['status'] ?? 'published');

    $postGenres = $_POST['genres'] ?? [];

    if (empty($title) || empty($posterUrl)) {
        $error = 'Vui lòng nhập tiêu đề phim và link Poster.';
    } else {
        if ($isEdit) {
            $updSql = "UPDATE movies SET title = :t, original_title = :ot, slug = :s, type = :type, description = :desc, poster_url = :p, backdrop_url = :b, trailer_url = :tr, video_url = :v, release_year = :y, duration_minutes = :dur, country = :c, director = :dir, cast = :cast, imdb_rating = :imdb, is_featured = :feat, status = :st WHERE id = :id";
            $updStmt = $db->prepare($updSql);
            $updStmt->execute([
                't' => $title, 'ot' => $originalTitle, 's' => $slug, 'type' => $type, 'desc' => $description,
                'p' => $posterUrl, 'b' => $backdropUrl, 'tr' => $trailerUrl, 'v' => $videoUrl,
                'y' => $releaseYear, 'dur' => $durationMinutes, 'c' => $country, 'dir' => $director,
                'cast' => $cast, 'imdb' => $imdbRating, 'feat' => $isFeatured, 'st' => $status,
                'id' => $movieId
            ]);

            // Sync genres
            $db->prepare("DELETE FROM movie_genres WHERE movie_id = :id")->execute(['id' => $movieId]);
            $insG = $db->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (:m, :g)");
            foreach ($postGenres as $gid) {
                $insG->execute(['m' => $movieId, 'g' => intval($gid)]);
            }

            header("Location: movies.php?msg=updated");
            exit;
        } else {
            $insSql = "INSERT INTO movies (title, original_title, slug, type, description, poster_url, backdrop_url, trailer_url, video_url, release_year, duration_minutes, country, director, cast, imdb_rating, is_featured, status) VALUES (:t, :ot, :s, :type, :desc, :p, :b, :tr, :v, :y, :dur, :c, :dir, :cast, :imdb, :feat, :st)";
            $insStmt = $db->prepare($insSql);
            $insStmt->execute([
                't' => $title, 'ot' => $originalTitle, 's' => $slug, 'type' => $type, 'desc' => $description,
                'p' => $posterUrl, 'b' => $backdropUrl, 'tr' => $trailerUrl, 'v' => $videoUrl,
                'y' => $releaseYear, 'dur' => $durationMinutes, 'c' => $country, 'dir' => $director,
                'cast' => $cast, 'imdb' => $imdbRating, 'feat' => $isFeatured, 'st' => $status
            ]);
            $newId = $db->lastInsertId();

            $insG = $db->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (:m, :g)");
            foreach ($postGenres as $gid) {
                $insG->execute(['m' => $newId, 'g' => intval($gid)]);
            }

            header("Location: movies.php?msg=created");
            exit;
        }
    }
}
?>

<div style="max-width:900px;margin-bottom:3rem;">
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div style="background:var(--bg-card);border:1px solid var(--border-subtle);border-radius:var(--radius-md);padding:2rem;">
    <form method="POST">
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
        <div class="form-group">
          <label class="form-label">Tên phim (Tiếng Việt) *</label>
          <input type="text" name="title" class="form-input" value="<?= htmlspecialchars($movie['title']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Tên gốc (Tiếng Anh / Quốc tế)</label>
          <input type="text" name="original_title" class="form-input" value="<?= htmlspecialchars($movie['original_title'] ?? '') ?>">
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.5rem;">
        <div class="form-group">
          <label class="form-label">Loại phim</label>
          <select name="type" class="filter-select" style="width:100%;">
            <option value="movie" <?= $movie['type'] === 'movie' ? 'selected' : '' ?>>Phim lẻ</option>
            <option value="series" <?= $movie['type'] === 'series' ? 'selected' : '' ?>>Phim bộ (Series)</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Năm phát hành</label>
          <input type="number" name="release_year" class="form-input" value="<?= $movie['release_year'] ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Thời lượng (phút)</label>
          <input type="number" name="duration_minutes" class="form-input" value="<?= $movie['duration_minutes'] ?>" required>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.5rem;">
        <div class="form-group">
          <label class="form-label">Quốc gia</label>
          <input type="text" name="country" class="form-input" value="<?= htmlspecialchars($movie['country']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Điểm đánh giá IMDb</label>
          <input type="number" step="0.1" name="imdb_rating" class="form-input" value="<?= $movie['imdb_rating'] ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Trạng thái</label>
          <select name="status" class="filter-select" style="width:100%;">
            <option value="published" <?= $movie['status'] === 'published' ? 'selected' : '' ?>>Xuất bản (Hiển thị)</option>
            <option value="upcoming" <?= $movie['status'] === 'upcoming' ? 'selected' : '' ?>>Sắp chiếu</option>
            <option value="draft" <?= $movie['status'] === 'draft' ? 'selected' : '' ?>>Bản nháp</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Thể loại phim</label>
        <div style="display:flex;flex-wrap:wrap;gap:1rem;background:rgba(255,255,255,0.03);padding:1rem;border-radius:var(--radius-sm);border:1px solid var(--border-subtle);">
          <?php foreach ($allGenres as $g): ?>
            <label style="display:flex;align-items:center;gap:0.4rem;font-size:0.875rem;cursor:pointer;">
              <input type="checkbox" name="genres[]" value="<?= $g['id'] ?>" <?= in_array($g['id'], $selectedGenres) ? 'checked' : '' ?> style="accent-color:var(--accent-purple);">
              <span><?= htmlspecialchars($g['name']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Tóm tắt nội dung phim</label>
        <textarea name="description" class="comment-input" rows="4"><?= htmlspecialchars($movie['description'] ?? '') ?></textarea>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
        <div class="form-group">
          <label class="form-label">Đạo diễn</label>
          <input type="text" name="director" class="form-input" value="<?= htmlspecialchars($movie['director'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Diễn viên chính</label>
          <input type="text" name="cast" class="form-input" value="<?= htmlspecialchars($movie['cast'] ?? '') ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Đường dẫn Poster (URL ảnh dọc) *</label>
        <input type="url" name="poster_url" class="form-input" value="<?= htmlspecialchars($movie['poster_url']) ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label">Đường dẫn Backdrop (URL ảnh nền ngang lớn)</label>
        <input type="url" name="backdrop_url" class="form-input" value="<?= htmlspecialchars($movie['backdrop_url'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label class="form-label">Đường dẫn Trailer YouTube Embed (vd: https://www.youtube.com/embed/Way9Dexny3w)</label>
        <input type="url" name="trailer_url" class="form-input" value="<?= htmlspecialchars($movie['trailer_url'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label class="form-label">Đường dẫn Video phát trực tuyến (mp4 / stream URL)</label>
        <input type="url" name="video_url" class="form-input" value="<?= htmlspecialchars($movie['video_url'] ?? '') ?>">
      </div>

      <div class="form-group" style="margin-top:1.5rem;">
        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600;">
          <input type="checkbox" name="is_featured" value="1" <?= $movie['is_featured'] ? 'checked' : '' ?> style="accent-color:var(--accent-purple);width:18px;height:18px;">
          <span>Đánh dấu là Phim nổi bật (Featured)</span>
        </label>
      </div>

      <div style="display:flex;gap:1rem;margin-top:2rem;">
        <button type="submit" class="btn btn-primary" style="padding:0.75rem 2rem;">
          <?= $isEdit ? 'Lưu Thay Đổi' : 'Thêm Phim Mới' ?>
        </button>
        <a href="movies.php" class="btn btn-glass">Hủy bỏ</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
