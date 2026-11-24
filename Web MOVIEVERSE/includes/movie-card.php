<?php
/**
 * Movie Card Partial
 * Expected input: $movie array
 * Optional: $isContinueWatching (bool), $progressPercent (int)
 */
$isContinue = $isContinueWatching ?? false;
$progPercent = $progressPercent ?? 0;
$userFavorites = $userFavorites ?? [];
$isFav = in_array($movie['id'], $userFavorites);
?>
<div class="movie-card <?= $isContinue ? 'continue-card' : '' ?>" data-id="<?= $movie['id'] ?>">
  <div class="card-poster-wrapper">
    <img 
      src="<?= htmlspecialchars($isContinue && !empty($movie['backdrop_url']) ? $movie['backdrop_url'] : $movie['poster_url']) ?>" 
      alt="<?= htmlspecialchars($movie['title']) ?>" 
      class="card-poster"
      loading="lazy"
    >
    
    <span class="card-badge">
      <?= $movie['type'] === 'series' ? 'Phim Bộ' : 'Phim Lẻ' ?>
    </span>

    <span class="card-rating">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      <?= number_format($movie['imdb_rating'], 1) ?>
    </span>

    <!-- Quick Hover Overlay -->
    <div class="card-hover-overlay">
      <div class="card-quick-actions">
        <a href="watch.php?id=<?= $movie['id'] ?>" class="btn-quick-play">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          <span>Xem</span>
        </a>
        <button type="button" class="btn-quick-fav btn-toggle-fav <?= $isFav ? 'active' : '' ?>" data-movie-id="<?= $movie['id'] ?>" title="Yêu thích">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
        </button>
      </div>
      <a href="movie-details.php?id=<?= $movie['id'] ?>" style="font-size:0.75rem;color:var(--text-secondary);text-align:center;">Chi tiết →</a>
    </div>

    <?php if ($isContinue): ?>
      <div class="progress-track">
        <div class="progress-fill" style="width: <?= min(100, max(5, $progPercent)) ?>%;"></div>
      </div>
    <?php endif; ?>
  </div>

  <div class="card-info">
    <h4 class="card-title" title="<?= htmlspecialchars($movie['title']) ?>">
      <a href="movie-details.php?id=<?= $movie['id'] ?>"><?= htmlspecialchars($movie['title']) ?></a>
    </h4>
    <div class="card-meta">
      <span><?= htmlspecialchars($movie['release_year']) ?></span>
      <span>•</span>
      <span><?= htmlspecialchars($movie['country']) ?></span>
      <span>•</span>
      <span><?= formatDuration($movie['duration_minutes']) ?></span>
    </div>
  </div>
</div>
