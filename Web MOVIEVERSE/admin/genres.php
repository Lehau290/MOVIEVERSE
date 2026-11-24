<?php
$pageTitle = 'Quản Lý Thể Loại Phim';
$activeNav = 'genres';
require_once __DIR__ . '/admin-header.php';

$db = Database::getConnection();

// Add Genre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_genre'])) {
    $name = sanitize($_POST['name'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    if (empty($slug)) $slug = slugify($name);
    $desc = sanitize($_POST['description'] ?? '');

    if (!empty($name)) {
        $stmt = $db->prepare("INSERT INTO genres (name, slug, description) VALUES (:n, :s, :d)");
        $stmt->execute(['n' => $name, 's' => $slug, 'd' => $desc]);
        header("Location: genres.php?msg=added");
        exit;
    }
}

// Delete Genre
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    $db->prepare("DELETE FROM genres WHERE id = :id")->execute(['id' => $id]);
    header("Location: genres.php?msg=deleted");
    exit;
}

// Fetch all genres with movie counts
$genres = $db->query("
    SELECT g.*, COUNT(mg.movie_id) as movie_count 
    FROM genres g
    LEFT JOIN movie_genres mg ON g.id = mg.genre_id
    GROUP BY g.id
    ORDER BY g.name ASC
")->fetchAll();
?>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:2rem;">
  <!-- Add Genre Form -->
  <div style="background:var(--bg-card);border:1px solid var(--border-subtle);border-radius:var(--radius-md);padding:1.5rem;height:fit-content;">
    <h4 style="font-size:1.1rem;font-weight:700;margin-bottom:1.25rem;">Thêm Thể Loại Mới</h4>
    <form method="POST">
      <input type="hidden" name="add_genre" value="1">
      <div class="form-group">
        <label class="form-label">Tên thể loại *</label>
        <input type="text" name="name" class="form-input" placeholder="Hành Động, Kinh Dị..." required>
      </div>

      <div class="form-group">
        <label class="form-label">Đường dẫn tĩnh (Slug - tùy chọn)</label>
        <input type="text" name="slug" class="form-input" placeholder="hanh-dong, kinh-di">
      </div>

      <div class="form-group">
        <label class="form-label">Mô tả ngắn</label>
        <textarea name="description" class="comment-input" rows="3" placeholder="Giới thiệu thể loại này..."></textarea>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:0.5rem;">
        Thêm Thể Loại
      </button>
    </form>
  </div>

  <!-- Genres Table -->
  <div class="admin-table-card">
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tên thể loại</th>
          <th>Slug</th>
          <th>Số lượng phim</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($genres as $g): ?>
          <tr>
            <td>#<?= $g['id'] ?></td>
            <td><b><?= htmlspecialchars($g['name']) ?></b></td>
            <td><code><?= htmlspecialchars($g['slug']) ?></code></td>
            <td><?= $g['movie_count'] ?> phim</td>
            <td>
              <a href="genres.php?action=delete&id=<?= $g['id'] ?>" class="btn-action btn-action-delete" onclick="return confirm('Bạn có chắc muốn xóa thể loại này?');">Xóa</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
