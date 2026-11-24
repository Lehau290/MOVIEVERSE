<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';

if (!Auth::isLoggedIn()) {
    echo json_encode(['require_login' => true]);
    exit;
}

$userId = $_SESSION['user_id'];
$movieId = intval($_POST['movie_id'] ?? 0);
$episodeId = !empty($_POST['episode_id']) ? intval($_POST['episode_id']) : null;
$progress = intval($_POST['progress_seconds'] ?? 0);
$duration = intval($_POST['duration_seconds'] ?? 0);

if (!$movieId || $progress <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

$db = Database::getConnection();

// Upsert into watch_history
$stmt = $db->prepare("SELECT id FROM watch_history WHERE user_id = :u AND movie_id = :m");
$stmt->execute(['u' => $userId, 'm' => $movieId]);
$existing = $stmt->fetch();

if ($existing) {
    $upd = $db->prepare("UPDATE watch_history SET episode_id = :e, progress_seconds = :p, duration_seconds = :d, last_watched = CURRENT_TIMESTAMP WHERE id = :id");
    $upd->execute(['e' => $episodeId, 'p' => $progress, 'd' => $duration, 'id' => $existing['id']]);
} else {
    $ins = $db->prepare("INSERT INTO watch_history (user_id, movie_id, episode_id, progress_seconds, duration_seconds) VALUES (:u, :m, :e, :p, :d)");
    $ins->execute(['u' => $userId, 'm' => $movieId, 'e' => $episodeId, 'p' => $progress, 'd' => $duration]);
}

echo json_encode(['success' => true]);
