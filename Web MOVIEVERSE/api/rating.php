<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';

if (!Auth::isLoggedIn()) {
    echo json_encode(['require_login' => true, 'message' => 'Vui lòng đăng nhập để đánh giá.']);
    exit;
}

$userId = $_SESSION['user_id'];
$movieId = intval($_POST['movie_id'] ?? 0);
$rating = floatval($_POST['rating'] ?? 0);
$review = trim($_POST['review'] ?? '');

if (!$movieId || $rating < 1 || $rating > 10) {
    echo json_encode(['success' => false, 'message' => 'Số điểm đánh giá không hợp lệ.']);
    exit;
}

$db = Database::getConnection();

// Upsert rating
$stmt = $db->prepare("SELECT id FROM ratings WHERE user_id = :u AND movie_id = :m");
$stmt->execute(['u' => $userId, 'm' => $movieId]);
$existing = $stmt->fetch();

if ($existing) {
    $upd = $db->prepare("UPDATE ratings SET rating = :r, review = :rev, created_at = CURRENT_TIMESTAMP WHERE id = :id");
    $upd->execute(['r' => $rating, 'rev' => $review, 'id' => $existing['id']]);
} else {
    $ins = $db->prepare("INSERT INTO ratings (user_id, movie_id, rating, review) VALUES (:u, :m, :r, :rev)");
    $ins->execute(['u' => $userId, 'm' => $movieId, 'r' => $rating, 'rev' => $review]);
}

// Recalculate movie average rating
$calc = $db->prepare("SELECT AVG(rating) as avg_rating FROM ratings WHERE movie_id = :m");
$calc->execute(['m' => $movieId]);
$avg = round($calc->fetch()['avg_rating'] ?? 7.5, 1);

$updMovie = $db->prepare("UPDATE movies SET imdb_rating = :avg WHERE id = :m");
$updMovie->execute(['avg' => $avg, 'm' => $movieId]);

echo json_encode(['success' => true, 'avg_rating' => $avg, 'message' => 'Đánh giá thành công!']);
