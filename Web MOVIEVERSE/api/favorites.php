<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';

if (!Auth::isLoggedIn()) {
    echo json_encode(['require_login' => true, 'message' => 'Vui lòng đăng nhập.']);
    exit;
}

$userId = $_SESSION['user_id'];
$movieId = intval($_POST['movie_id'] ?? 0);

if (!$movieId) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID phim.']);
    exit;
}

$db = Database::getConnection();

// Check if already in favorites
$stmt = $db->prepare("SELECT id FROM favorites WHERE user_id = :u AND movie_id = :m");
$stmt->execute(['u' => $userId, 'm' => $movieId]);
$existing = $stmt->fetch();

if ($existing) {
    // Remove
    $del = $db->prepare("DELETE FROM favorites WHERE id = :id");
    $del->execute(['id' => $existing['id']]);
    echo json_encode(['success' => true, 'status' => 'removed']);
} else {
    // Add
    $ins = $db->prepare("INSERT INTO favorites (user_id, movie_id) VALUES (:u, :m)");
    $ins->execute(['u' => $userId, 'm' => $movieId]);
    echo json_encode(['success' => true, 'status' => 'added']);
}
