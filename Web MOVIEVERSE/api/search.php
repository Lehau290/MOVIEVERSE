<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$query = trim($_GET['q'] ?? '');
if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$db = Database::getConnection();
$searchTerm = '%' . $query . '%';

$stmt = $db->prepare("
    SELECT id, title, original_title, slug, type, poster_url, release_year, imdb_rating 
    FROM movies 
    WHERE (title LIKE :q OR original_title LIKE :q OR cast LIKE :q)
      AND status = 'published'
    ORDER BY view_count DESC 
    LIMIT 8
");
$stmt->execute(['q' => $searchTerm]);
$results = $stmt->fetchAll();

echo json_encode($results);
