<?php
$pageTitle = 'Quản Lý Hero Banners';
$activeNav = 'banners';
require_once __DIR__ . '/admin-header.php';

$db = Database::getConnection();

// Add Banner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_banner'])) {
    $movieId = intval($_POST['movie_id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $subtitle = sanitize($_POST['subtitle'] ?? '');
    $imageUrl = sanitize($_POST['image_url'] ?? '');
    $buttonText = sanitize($_POST['button_text'] ?? 'Xem ngay');
    $buttonLink = sanitize($_POST['button_link'] ?? '#');
    $orderNum = intval($_POST['order_num'] ?? 0);

    $ins = $db->prepare("INSERT INTO banners (movie_id, title, subtitle, image_url, button_text, button_link, order_num) VALUES (:m, :t, :s, :i, :bt, :bl, :o)");
    $ins->execute([
        'm' => $movieId > 0 ? $movieId : null,
        't' => $title,
        's' => $subtitle,
        'i' => $imageUrl,
        'bt' => $buttonText,
        'bl' => $buttonLink,
        'o' => $orderNum
    ]);
    header("Location: banners.php?msg=added");
    exit;
}

// Delete Banner
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    $db->prepare("DELETE FROM banners WHERE id = :id")->execute(['id' => $id]);
    header("Location: banners.php?msg=deleted");
    exit;
}

// Fetch movies for dropdown
$allMovies = $db->query("SELECT id, title FROM movies ORDER BY title ASC")->fetchAll();

// Fetch banners
$banners = $db->query("
    SELECT b.*, m.title as movie_title 
    FROM banners b
    LEFT JOIN movies m ON b.movie_id = m.id
    ORDER BY b.order_num ASC
")->fetchAll();
?>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:2rem;">
  <!-- Add Banner Form -->
  <div style="background:var(--bg-card);border:1px solid var(--border-subtle);border-radius:var(--radius-md);padding:1.5rem;height:fit-content;">
    <h4 style="font-size:1.1rem;font-weight:700;margin-bottom:1.25rem;">Thêm Banner Mới</h4>
    <form method="POST">
      <input type="hidden" name="add_banner" value="1">
      
      <div class="form-group">
        <label class="form-label">Chọn phim liên kết</label>
        <select name="movie_id" class="filter-select" style="width:100%;">
          <option value="0">-- Không liên kết phim cụ thể --</option>
          <?php foreach ($allMovies as $m): ?>
            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Tiêu đề Banner *</label>
        <input type="text" name="title" class="form-input" placeholder="Tên phim hoặc chiến dịch" required>
      </div>

      <div class="form-group">
        <label class="form-label">Phụ đề / Thông điệp</label>
        <input type="text" name="subtitle" class="form-input" placeholder="Siêu phẩm ra mắt...">
      </div>

      <div class="form-group">
        <label class="form-label">Đường dẫn ảnh Banner (1920x800) *</label>
        <input type="url" name="image_url" class="form-input" required placeholder="https://...">
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="form-group">
          <label class="form-label">Chữ trên nút</label>
          <input type="text" name="button_text" class="form-input" value="Xem Ngay">
        </div>
        <div class="form-group">
          <label class="form-label">Thứ tự hiển thị</label>
          <input type="number" name="order_num" class="form-input" value="1">
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:0.5rem;">
        Thêm Banner
      </button>
    </form>
  </div>

  <!-- Banners Table -->
  <div class="admin-table-card">
    <table class="data-table">
      <thead>
        <tr>
          <th>Hình ảnh</th>
          <th>Tiêu đề / Phim</th>
          <th>Thứ tự</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($banners as $b): ?>
          <tr>
            <td>
              <img src="<?= htmlspecialchars($b['image_url']) ?>" style="width:110px;height:55px;object-fit:cover;border-radius:6px;" alt="Banner">
            </td>
            <td>
              <div style="font-weight:600;"><?= htmlspecialchars($b['title']) ?></div>
              <div style="font-size:0.75rem;color:var(--text-muted);"><?= htmlspecialchars($b['subtitle'] ?? '') ?></div>
            </td>
            <td><?= $b['order_num'] ?></td>
            <td>
              <a href="banners.php?action=delete&id=<?= $b['id'] ?>" class="btn-action btn-action-delete" onclick="return confirm('Bạn có chắc muốn xóa banner này?');">Xóa</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
