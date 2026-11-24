<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

if (!Auth::isLoggedIn()) {
    echo json_encode(['require_login' => true, 'message' => 'Vui lòng đăng nhập để bình luận.']);
    exit;
}

$userId = $_SESSION['user_id'];
$movieId = intval($_POST['movie_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if (!$movieId || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Nội dung bình luận không được để trống.']);
    exit;
}

$db = Database::getConnection();
$stmt = $db->prepare("INSERT INTO comments (user_id, movie_id, content) VALUES (:u, :m, :c)");
$stmt->execute(['u' => $userId, 'm' => $movieId, 'c' => sanitize($content)]);

$commentId = $db->lastInsertId();
$user = Auth::user();

echo json_encode([
    'success' => true,
    'comment' => [
        'id' => $commentId,
        'user_name' => $user['full_name'] ?? $user['username'],
        'avatar' => $user['avatar'],
        'content' => sanitize($content),
        'time_ago' => 'vừa xong'
    ]
]);
