<?php
$pageTitle = 'Quản Lý Phim';
$activeNav = 'movies';
require_once __DIR__ . '/admin-header.php';

$db = Database::getConnection();

// Delete movie handler
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $delId = intval($_GET['id'] ?? 0);
    if ($delId > 0) {
        $delStmt = $db->prepare("DELETE FROM movies WHERE id = :id");
        $delStmt->execute(['id' => $delId]);
        header("Location: movies.php?msg=deleted");
        exit;
    }
}

// Search
$search = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM movies";
$params = [];
if (!empty($search)) {
    $sql .= " WHERE title LIKE :q OR original_title LIKE :q";
    $params['q'] = '%' . $search . '%';
}
$sql .= " ORDER BY id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$movies = $stmt->fetchAll();
?>

<?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-success">Thao tác thành công!</div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
  <form method="GET" style="display:flex;gap:0.75rem;">
    <input type="text" name="q" class="form-input" style="width:280px;" placeholder="Tìm tên phim..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-glass">Tìm</button>
  </form>

  <a href="movie-form.php" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    <span>Thêm Phim Mới</span>
  </a>
</div>

<div class="admin-table-card">
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Phim</th>
        <th>Loại</th>
        <th>Năm</th>
        <th>Thời lượng</th>
        <th>Lượt xem</th>
        <th>Điểm</th>
        <th>Thao tác</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($movies)): ?>
        <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:3rem;">Chưa có phim nào</td></tr>
      <?php else: ?>
        <?php foreach ($movies as $m): ?>
          <tr>
            <td>#<?= $m['id'] ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:0.85rem;">
                <img src="<?= htmlspecialchars($m['poster_url']) ?>" class="table-thumb" alt="Poster">
                <div>
                  <a href="../movie-details.php?id=<?= $m['id'] ?>" target="_blank" style="font-weight:600;color:var(--text-primary);">
                    <?= htmlspecialchars($m['title']) ?>
                  </a>
                  <div style="font-size:0.75rem;color:var(--text-muted);"><?= htmlspecialchars($m['original_title'] ?? '') ?></div>
                </div>
              </div>
            </td>
            <td>
              <span class="badge-tag <?= $m['type'] === 'series' ? 'badge-cyan' : 'badge-purple' ?>" style="font-size:0.7rem;padding:0.2rem 0.5rem;">
                <?= $m['type'] === 'series' ? 'Phim Bộ' : 'Phim Lẻ' ?>
              </span>
            </td>
            <td><?= $m['release_year'] ?></td>
            <td><?= $m['duration_minutes'] ?>m</td>
            <td><?= number_format($m['view_count']) ?></td>
            <td>★ <?= number_format($m['imdb_rating'], 1) ?></td>
            <td>
              <div class="table-actions">
                <?php if ($m['type'] === 'series'): ?>
                  <a href="episodes.php?movie_id=<?= $m['id'] ?>" class="btn-action" style="background:rgba(6,182,212,0.15);color:#67e8f9;border-color:rgba(6,182,212,0.3);">
                    Tập phim
                  </a>
                <?php endif; ?>
                <a href="movie-form.php?id=<?= $m['id'] ?>" class="btn-action btn-action-edit">Sửa</a>
                <a href="movies.php?action=delete&id=<?= $m['id'] ?>" class="btn-action btn-action-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa phim này không?');">Xóa</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
