/**
 * MovieVerse - Advanced HTML5 Streaming Video Player
 */

class MovieVersePlayer {
  constructor(options) {
    this.container = document.querySelector(options.container);
    if (!this.container) return;

    this.video = this.container.querySelector('video');
    this.playBtn = this.container.querySelector('.btn-play-pause');
    this.scrubBar = this.container.querySelector('.video-scrub-bar');
    this.scrubFill = this.container.querySelector('.video-scrub-filled');
    this.timeCurrent = this.container.querySelector('.time-current');
    this.timeDuration = this.container.querySelector('.time-duration');
    this.volumeBtn = this.container.querySelector('.btn-volume');
    this.volumeSlider = this.container.querySelector('.volume-range');
    this.speedSelect = this.container.querySelector('.speed-selector');
    this.fullscreenBtn = this.container.querySelector('.btn-fullscreen');

    this.movieId = options.movieId;
    this.episodeId = options.episodeId || null;
    this.savedProgress = options.savedProgress || 0;
    this.nextEpisodeUrl = options.nextEpisodeUrl || null;

    this.isDraggingScrub = false;
    this.saveInterval = null;

    this.init();
  }

  init() {
    this.bindEvents();
    this.restoreProgress();
    this.startProgressAutoSave();
  }

  bindEvents() {
    // Play / Pause
    this.playBtn?.addEventListener('click', () => this.togglePlay());
    this.video.addEventListener('click', () => this.togglePlay());
    this.video.addEventListener('play', () => this.updatePlayIcon(true));
    this.video.addEventListener('pause', () => this.updatePlayIcon(false));

    // Time updates
    this.video.addEventListener('timeupdate', () => this.onTimeUpdate());
    this.video.addEventListener('loadedmetadata', () => {
      if (this.timeDuration) this.timeDuration.textContent = this.formatTime(this.video.duration);
    });

    // Scrub bar dragging / clicking
    if (this.scrubBar) {
      this.scrubBar.addEventListener('mousedown', (e) => {
        this.isDraggingScrub = true;
        this.scrub(e);
      });
      document.addEventListener('mousemove', (e) => {
        if (this.isDraggingScrub) this.scrub(e);
      });
      document.addEventListener('mouseup', () => {
        this.isDraggingScrub = false;
      });
    }

    // Volume
    if (this.volumeSlider) {
      this.volumeSlider.addEventListener('input', (e) => {
        this.video.volume = e.target.value;
        this.video.muted = (this.video.volume === 0);
        this.updateVolumeIcon();
      });
    }
    this.volumeBtn?.addEventListener('click', () => this.toggleMute());

    // Speed
    this.speedSelect?.addEventListener('change', (e) => {
      this.video.playbackRate = parseFloat(e.target.value);
    });

    // Fullscreen
    this.fullscreenBtn?.addEventListener('click', () => this.toggleFullscreen());

    // Video Ended -> Prompt next episode
    this.video.addEventListener('ended', () => {
      if (this.nextEpisodeUrl) {
        showToast('Tập tiếp theo sẽ phát sau 3 giây...');
        setTimeout(() => {
          window.location.href = this.nextEpisodeUrl;
        }, 3000);
      }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
      if (['input', 'textarea'].includes(document.activeElement.tagName.toLowerCase())) return;
      if (e.code === 'Space') {
        e.preventDefault();
        this.togglePlay();
      } else if (e.code === 'ArrowRight') {
        this.video.currentTime = Math.min(this.video.duration, this.video.currentTime + 10);
      } else if (e.code === 'ArrowLeft') {
        this.video.currentTime = Math.max(0, this.video.currentTime - 10);
      } else if (e.code === 'KeyF') {
        this.toggleFullscreen();
      }
    });

    // Save on beforeunload
    window.addEventListener('beforeunload', () => {
      this.saveProgressNow();
    });
  }

  togglePlay() {
    if (this.video.paused || this.video.ended) {
      this.video.play();
      this.container.classList.remove('paused');
    } else {
      this.video.pause();
      this.container.classList.add('paused');
    }
  }

  updatePlayIcon(isPlaying) {
    if (!this.playBtn) return;
    this.playBtn.innerHTML = isPlaying
      ? `<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>`
      : `<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>`;
  }

  onTimeUpdate() {
    if (this.isDraggingScrub) return;
    const progress = (this.video.currentTime / this.video.duration) * 100;
    if (this.scrubFill) this.scrubFill.style.width = `${progress}%`;
    if (this.timeCurrent) this.timeCurrent.textContent = this.formatTime(this.video.currentTime);
  }

  scrub(e) {
    const rect = this.scrubBar.getBoundingClientRect();
    const pos = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
    this.video.currentTime = pos * this.video.duration;
    if (this.scrubFill) this.scrubFill.style.width = `${pos * 100}%`;
  }

  toggleMute() {
    this.video.muted = !this.video.muted;
    this.updateVolumeIcon();
  }

  updateVolumeIcon() {
    if (!this.volumeBtn) return;
    const isMuted = this.video.muted || this.video.volume === 0;
    this.volumeBtn.innerHTML = isMuted
      ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5L6 9H2v6h4l5 4V5z"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>`
      : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>`;
  }

  toggleFullscreen() {
    if (!document.fullscreenElement) {
      this.container.requestFullscreen?.() || this.container.webkitRequestFullscreen?.();
    } else {
      document.exitFullscreen?.() || document.webkitExitFullscreen?.();
    }
  }

  restoreProgress() {
    if (this.savedProgress > 10) {
      this.video.addEventListener('loadedmetadata', () => {
        if (this.savedProgress < this.video.duration - 15) {
          this.video.currentTime = this.savedProgress;
          showToast(`Đã tiếp tục xem từ ${this.formatTime(this.savedProgress)}`);
        }
      }, { once: true });
    }
  }

  startProgressAutoSave() {
    this.saveInterval = setInterval(() => {
      if (!this.video.paused && this.video.currentTime > 5) {
        this.saveProgressNow();
      }
    }, 6000);
  }

  saveProgressNow() {
    if (!this.movieId || this.video.currentTime < 5) return;
    const current = Math.floor(this.video.currentTime);
    const total = Math.floor(this.video.duration) || 0;

    const payload = new URLSearchParams({
      movie_id: this.movieId,
      episode_id: this.episodeId || '',
      progress_seconds: current,
      duration_seconds: total
    });

    navigator.sendBeacon ? navigator.sendBeacon('api/progress.php', payload) : fetch('api/progress.php', {
      method: 'POST',
      body: payload
    });
  }

  formatTime(seconds) {
    if (isNaN(seconds)) return '00:00';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);
    if (h > 0) {
      return `${h}:${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
    }
    return `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
  }
}
