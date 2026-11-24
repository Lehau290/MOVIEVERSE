<?php
require_once __DIR__ . '/admin-header.php';

$db = Database::getConnection();
$movieId = intval($_GET['movie_id'] ?? 0);
if (!$movieId) {
    header("Location: movies.php");
    exit;
}

$stmtM = $db->prepare("SELECT * FROM movies WHERE id = :id LIMIT 1");
$stmtM->execute(['id' => $movieId]);
$movie = $stmtM->fetch();

$pageTitle = 'Quản Lý Tập Phim: ' . $movie['title'];
$activeNav = 'movies';

// Handle Add Episode
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_episode'])) {
    $season = intval($_POST['season_number'] ?? 1);
    $epNum = intval($_POST['episode_number'] ?? 1);
    $title = sanitize($_POST['title'] ?? '');
    $videoUrl = sanitize($_POST['video_url'] ?? '');
    $duration = intval($_POST['duration_minutes'] ?? 45);
    $thumbnail = sanitize($_POST['thumbnail_url'] ?? '');

    $ins = $db->prepare("INSERT INTO episodes (movie_id, season_number, episode_number, title, video_url, duration_minutes, thumbnail_url) VALUES (:m, :s, :e, :t, :v, :d, :thumb)");
    $ins->execute([
        'm' => $movieId,
        's' => $season,
        'e' => $epNum,
        't' => $title,
        'v' => $videoUrl,
        'd' => $duration,
        'thumb' => $thumbnail
    ]);
    header("Location: episodes.php?movie_id={$movieId}&msg=added");
    exit;
}

// Handle Delete Episode
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $epId = intval($_GET['id'] ?? 0);
    $del = $db->prepare("DELETE FROM episodes WHERE id = :id AND movie_id = :m");
    $del->execute(['id' => $epId, 'm' => $movieId]);
    header("Location: episodes.php?movie_id={$movieId}&msg=deleted");
    exit;
}

// Fetch episodes
$epStmt = $db->prepare("SELECT * FROM episodes WHERE movie_id = :m ORDER BY season_number ASC, episode_number ASC");
$epStmt->execute(['m' => $movieId]);
$episodes = $epStmt->fetchAll();
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
  <div>
    <a href="movies.php" style="color:var(--text-muted);font-size:0.85rem;">← Quay lại danh sách phim</a>
    <h3 style="font-size:1.4rem;font-weight:700;margin-top:0.35rem;"><?= htmlspecialchars($movie['title']) ?></h3>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:2rem;">
  <!-- Add Episode Form -->
  <div style="background:var(--bg-card);border:1px solid var(--border-subtle);border-radius:var(--radius-md);padding:1.5rem;height:fit-content;">
    <h4 style="font-size:1.1rem;font-weight:700;margin-bottom:1.25rem;">Thêm Tập Mới</h4>
    <form method="POST">
      <input type="hidden" name="add_episode" value="1">
      
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="form-group">
          <label class="form-label">Mùa (Season)</label>
          <input type="number" name="season_number" class="form-input" value="1" required>
        </div>
        <div class="form-group">
          <label class="form-label">Tập số</label>
          <input type="number" name="episode_number" class="form-input" value="<?= count($episodes) + 1 ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Tiêu đề tập</label>
        <input type="text" name="title" class="form-input" placeholder="Tập 1: Sự khởi đầu" required>
      </div>

      <div class="form-group">
        <label class="form-label">Thời lượng (phút)</label>
        <input type="number" name="duration_minutes" class="form-input" value="45">
      </div>

      <div class="form-group">
        <label class="form-label">Đường dẫn Video stream (mp4 URL) *</label>
        <input type="url" name="video_url" class="form-input" required placeholder="https://...">
      </div>

      <div class="form-group">
        <label class="form-label">Ảnh thumbnail tập (Tùy chọn)</label>
        <input type="url" name="thumbnail_url" class="form-input" placeholder="https://...">
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:0.5rem;">
        Thêm Tập Này
      </button>
    </form>
  </div>

  <!-- Episode Table -->
  <div class="admin-table-card">
    <table class="data-table">
      <thead>
        <tr>
          <th>Mùa/Tập</th>
          <th>Tên tập</th>
          <th>Thời lượng</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($episodes)): ?>
          <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:3rem;">Chưa có tập phim nào</td></tr>
        <?php else: ?>
          <?php foreach ($episodes as $ep): ?>
            <tr>
              <td><b>Mùa <?= $ep['season_number'] ?> - Tập <?= $ep['episode_number'] ?></b></td>
              <td><?= htmlspecialchars($ep['title']) ?></td>
              <td><?= $ep['duration_minutes'] ?> phút</td>
              <td>
                <a href="episodes.php?movie_id=<?= $movieId ?>&action=delete&id=<?= $ep['id'] ?>" class="btn-action btn-action-delete" onclick="return confirm('Bạn có chắc muốn xóa tập này?');">Xóa</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
