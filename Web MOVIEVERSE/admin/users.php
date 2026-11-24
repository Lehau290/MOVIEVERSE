<?php
$pageTitle = 'Quản Lý Người Dùng';
$activeNav = 'users';
require_once __DIR__ . '/admin-header.php';

$db = Database::getConnection();

// Toggle Role (user <-> admin)
if (isset($_GET['action']) && $_GET['action'] === 'toggle_role') {
    $uid = intval($_GET['id'] ?? 0);
    if ($uid !== $_SESSION['user_id']) { // Do not change self
        $u = $db->query("SELECT role FROM users WHERE id = {$uid}")->fetch();
        if ($u) {
            $newRole = ($u['role'] === 'admin') ? 'user' : 'admin';
            $db->prepare("UPDATE users SET role = :r WHERE id = :id")->execute(['r' => $newRole, 'id' => $uid]);
        }
    }
    header("Location: users.php?msg=updated");
    exit;
}

// Toggle Status (active <-> banned)
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status') {
    $uid = intval($_GET['id'] ?? 0);
    if ($uid !== $_SESSION['user_id']) {
        $u = $db->query("SELECT status FROM users WHERE id = {$uid}")->fetch();
        if ($u) {
            $newStatus = ($u['status'] === 'active') ? 'banned' : 'active';
            $db->prepare("UPDATE users SET status = :s WHERE id = :id")->execute(['s' => $newStatus, 'id' => $uid]);
        }
    }
    header("Location: users.php?msg=updated");
    exit;
}

// Fetch users
$users = $db->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<div class="admin-table-card">
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Người dùng</th>
        <th>Email</th>
        <th>Vai trò</th>
        <th>Trạng thái</th>
        <th>Ngày tham gia</th>
        <th>Thao tác</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td>#<?= $u['id'] ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:0.75rem;">
              <img src="<?= htmlspecialchars($u['avatar']) ?>" style="width:34px;height:34px;border-radius:50%;object-fit:cover;" alt="Avatar">
              <div>
                <div style="font-weight:600;"><?= htmlspecialchars($u['full_name'] ?? $u['username']) ?></div>
                <div style="font-size:0.75rem;color:var(--text-muted);">@<?= htmlspecialchars($u['username']) ?></div>
              </div>
            </div>
          </td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td>
            <span class="badge-tag <?= $u['role'] === 'admin' ? 'badge-purple' : '' ?>" style="font-size:0.75rem;">
              <?= strtoupper($u['role']) ?>
            </span>
          </td>
          <td>
            <span class="badge-tag <?= $u['status'] === 'active' ? 'badge-cyan' : 'badge-pink' ?>" style="font-size:0.75rem;">
              <?= $u['status'] === 'active' ? 'Hoạt động' : 'Bị khóa' ?>
            </span>
          </td>
          <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          <td>
            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
              <div class="table-actions">
                <a href="users.php?action=toggle_role&id=<?= $u['id'] ?>" class="btn-action btn-action-edit">
                  Đổi thành <?= $u['role'] === 'admin' ? 'User' : 'Admin' ?>
                </a>
                <a href="users.php?action=toggle_status&id=<?= $u['id'] ?>" class="btn-action <?= $u['status'] === 'active' ? 'btn-action-delete' : 'btn-action-edit' ?>">
                  <?= $u['status'] === 'active' ? 'Khóa TK' : 'Mở Khóa' ?>
                </a>
              </div>
            <?php else: ?>
              <span style="font-size:0.75rem;color:var(--text-muted);">(Tài khoản hiện tại)</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
