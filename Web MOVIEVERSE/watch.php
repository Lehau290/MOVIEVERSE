<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

$movieId = intval($_GET['id'] ?? 0);
$episodeId = intval($_GET['ep'] ?? 0);

if (!$movieId) {
    header("Location: index.php");
    exit;
}

$db = Database::getConnection();

// Fetch Movie Details
$stmt = $db->prepare("SELECT * FROM movies WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $movieId]);
$movie = $stmt->fetch();

if (!$movie) {
    header("Location: index.php");
    exit;
}

$pageTitle = 'Đang xem: ' . $movie['title'];

// Fetch Episodes if Series
$episodes = [];
$currentEpisode = null;
$nextEpisode = null;

if ($movie['type'] === 'series') {
    $epStmt = $db->prepare("SELECT * FROM episodes WHERE movie_id = :m ORDER BY season_number ASC, episode_number ASC");
    $epStmt->execute(['m' => $movieId]);
    $episodes = $epStmt->fetchAll();

    if ($episodeId > 0) {
        foreach ($episodes as $idx => $ep) {
            if ($ep['id'] === $episodeId) {
                $currentEpisode = $ep;
                $nextEpisode = $episodes[$idx + 1] ?? null;
                break;
            }
        }
    }
    if (!$currentEpisode && !empty($episodes)) {
        $currentEpisode = $episodes[0];
        $nextEpisode = $episodes[1] ?? null;
    }
}

// Active video source
$videoSource = $currentEpisode ? $currentEpisode['video_url'] : $movie['video_url'];

// If video source is null or empty, provide standard fallback sample
if (empty($videoSource)) {
    $videoSource = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
}

// Fetch saved watch progress for current user
$savedProgress = 0;
if (Auth::isLoggedIn()) {
    $uid = $_SESSION['user_id'];
    $histStmt = $db->prepare("SELECT progress_seconds FROM watch_history WHERE user_id = :u AND movie_id = :m");
    $histStmt->execute(['u' => $uid, 'm' => $movieId]);
    $savedProgress = intval($histStmt->fetchColumn() ?: 0);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="watch-container">
  <!-- Custom Modern Video Player Container -->
  <div id="movie-player-box" class="player-box">
    <video id="main-video" class="video-element" preload="metadata" playsinline>
      <source src="<?= htmlspecialchars($videoSource) ?>" type="video/mp4">
      Trình duyệt của bạn không hỗ trợ thẻ video HTML5.
    </video>

    <!-- Custom On-Screen Player Controls -->
    <div class="player-controls">
      <!-- Scrub Progress Bar with buffered indicator -->
      <div class="video-scrub-bar">
        <div class="video-scrub-filled">
          <div class="video-scrub-handle"></div>
        </div>
      </div>

      <div class="controls-row">
        <div class="controls-left">
          <!-- Play / Pause -->
          <button type="button" class="control-btn btn-play-pause" title="Phát / Tạm dừng (Phím cách)">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </button>

          <!-- 10s Rewind -->
          <button type="button" class="control-btn" onclick="document.getElementById('main-video').currentTime -= 10" title="Lùi 10 giây">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
          </button>

          <!-- 10s Fast-Forward -->
          <button type="button" class="control-btn" onclick="document.getElementById('main-video').currentTime += 10" title="Tua 10 giây">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
          </button>

          <!-- Volume Controls -->
          <button type="button" class="control-btn btn-volume" title="Âm thanh / Tắt tiếng">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
          </button>
          <input type="range" class="volume-range" min="0" max="1" step="0.05" value="1" style="width:75px;accent-color:var(--accent-purple);">

          <!-- Time counters -->
          <div class="time-display">
            <span class="time-current">00:00</span> / <span class="time-duration">--:--</span>
          </div>
        </div>

        <div class="controls-right">
          <!-- Playback Speed -->
          <select class="speed-selector" style="background:rgba(0,0,0,0.6);border:1px solid rgba(255,255,255,0.2);color:#fff;border-radius:4px;padding:2px 6px;font-size:0.8rem;outline:none;">
            <option value="0.5">0.5x</option>
            <option value="0.75">0.75x</option>
            <option value="1.0" selected>1.0x Chuẩn</option>
            <option value="1.25">1.25x</option>
            <option value="1.5">1.5x</option>
            <option value="2.0">2.0x</option>
          </select>

          <!-- Quality Badge -->
          <span style="font-size:0.75rem;font-weight:700;background:var(--accent-purple);padding:2px 8px;border-radius:4px;color:#fff;">1080p FHD</span>

          <!-- Fullscreen Button -->
          <button type="button" class="control-btn btn-fullscreen" title="Toàn màn hình (F)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Information Below Player -->
  <div style="margin-top:2rem;display:flex;justify-content:space-between;align-items:flex-start;gap:2rem;">
    <div style="flex:1;">
      <h1 style="font-family:'Space Grotesk',sans-serif;font-size:1.85rem;font-weight:700;">
        <?= htmlspecialchars($movie['title']) ?>
        <?php if ($currentEpisode): ?>
          <span style="font-size:1.15rem;color:var(--accent-purple);margin-left:0.5rem;">- Mùa <?= $currentEpisode['season_number'] ?> • Tập <?= $currentEpisode['episode_number'] ?>: <?= htmlspecialchars($currentEpisode['title']) ?></span>
        <?php endif; ?>
      </h1>
      <p style="color:var(--text-muted);margin-top:0.5rem;font-size:0.95rem;">
        <?= htmlspecialchars($movie['release_year']) ?> • <?= htmlspecialchars($movie['country']) ?> • ★ <?= number_format($movie['imdb_rating'], 1) ?> IMDb
      </p>
      <p style="color:var(--text-secondary);margin-top:1rem;line-height:1.6;font-size:0.95rem;">
        <?= nl2br(htmlspecialchars($movie['description'] ?? '')) ?>
      </p>
    </div>

    <div style="display:flex;gap:0.75rem;">
      <button type="button" class="btn btn-glass btn-toggle-fav" data-movie-id="<?= $movie['id'] ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
        <span>Yêu thích</span>
      </button>
      <a href="movie-details.php?id=<?= $movie['id'] ?>" class="btn btn-glass">
        <span>Chi tiết phim</span>
      </a>
    </div>
  </div>

  <!-- If TV Series: Seasons & Episodes Selection -->
  <?php if (!empty($episodes)): ?>
    <div class="episodes-panel">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
        <h3 style="font-size:1.25rem;font-weight:700;">Danh sách tập phim</h3>
        <span style="font-size:0.85rem;color:var(--text-muted);"><?= count($episodes) ?> tập có sẵn</span>
      </div>

      <div class="episodes-grid">
        <?php foreach ($episodes as $ep): 
          $isActiveEp = ($currentEpisode && $currentEpisode['id'] === $ep['id']);
        ?>
          <a href="watch.php?id=<?= $movie['id'] ?>&ep=<?= $ep['id'] ?>" class="episode-card <?= $isActiveEp ? 'active' : '' ?>">
            <img src="<?= htmlspecialchars(!empty($ep['thumbnail_url']) ? $ep['thumbnail_url'] : $movie['backdrop_url']) ?>" class="episode-thumb" alt="Tập <?= $ep['episode_number'] ?>">
            <div style="flex:1;min-width:0;">
              <div style="font-size:0.875rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                Tập <?= $ep['episode_number'] ?>: <?= htmlspecialchars($ep['title']) ?>
              </div>
              <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.25rem;">
                Thời lượng: <?= $ep['duration_minutes'] ?> phút
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<script src="assets/js/player.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  new MovieVersePlayer({
    container: '#movie-player-box',
    movieId: <?= $movie['id'] ?>,
    episodeId: <?= $currentEpisode ? $currentEpisode['id'] : 'null' ?>,
    savedProgress: <?= $savedProgress ?>,
    nextEpisodeUrl: <?= $nextEpisode ? "'watch.php?id={$movie['id']}&ep={$nextEpisode['id']}'" : 'null' ?>
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
