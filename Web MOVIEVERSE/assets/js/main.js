/**
 * MovieVerse - Core UI & Interaction Scripts
 * Inspired by Sunrain Gallery & Modern Streaming UX
 */

document.addEventListener('DOMContentLoaded', () => {
  initAmbientCanvas();
  initThemeSwitcher();
  initTopBarScroll();
  initHeroSlider();
  initCarousels();
  initLiveSearch();
  initUserDropdown();
  initTrailerModal();
  initFavorites();
  initRatingWidget();
});

/* -------------------------------------------------------------
   1. Ambient Sky/Optics Canvas (inspired by Sunrain Gallery)
   ------------------------------------------------------------- */
function initAmbientCanvas() {
  const canvas = document.getElementById('ambient-glow-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let width, height;
  let particles = [];

  function resize() {
    width = canvas.width = window.innerWidth;
    height = canvas.height = window.innerHeight;
  }
  window.addEventListener('resize', resize);
  resize();

  class GlowOrb {
    constructor() {
      this.x = Math.random() * width;
      this.y = Math.random() * height;
      this.radius = Math.random() * 120 + 80;
      this.vx = (Math.random() - 0.5) * 0.4;
      this.vy = (Math.random() - 0.5) * 0.4;
      this.color = Math.random() > 0.5 ? '139, 92, 246' : '6, 182, 212'; // Purple or Cyan
    }

    update() {
      this.x += this.vx;
      this.y += this.vy;
      if (this.x < -100) this.x = width + 100;
      if (this.x > width + 100) this.x = -100;
      if (this.y < -100) this.y = height + 100;
      if (this.y > height + 100) this.y = -100;
    }

    draw() {
      const grad = ctx.createRadialGradient(this.x, this.y, 0, this.x, this.y, this.radius);
      grad.addColorStop(0, `rgba(${this.color}, 0.08)`);
      grad.addColorStop(1, `rgba(${this.color}, 0)`);
      ctx.fillStyle = grad;
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
      ctx.fill();
    }
  }

  for (let i = 0; i < 6; i++) {
    particles.push(new GlowOrb());
  }

  function animate() {
    ctx.clearRect(0, 0, width, height);
    particles.forEach(p => {
      p.update();
      p.draw();
    });
    requestAnimationFrame(animate);
  }
  animate();
}

/* -------------------------------------------------------------
   2. Theme Switcher (Dark / Light)
   ------------------------------------------------------------- */
function initThemeSwitcher() {
  const toggleBtn = document.getElementById('theme-toggle-btn');
  if (!toggleBtn) return;

  const currentTheme = localStorage.getItem('mv_theme') || 'dark';
  document.documentElement.setAttribute('data-theme', currentTheme);
  updateThemeIcon(currentTheme);

  toggleBtn.addEventListener('click', () => {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const newTheme = isDark ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('mv_theme', newTheme);
    updateThemeIcon(newTheme);
  });

  function updateThemeIcon(theme) {
    const icon = toggleBtn.querySelector('i, svg');
    if (toggleBtn) {
      toggleBtn.innerHTML = theme === 'dark' 
        ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>`
        : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>`;
      toggleBtn.setAttribute('title', theme === 'dark' ? 'Chuyển sang chế độ Sáng' : 'Chuyển sang chế độ Tối');
    }
  }
}

/* -------------------------------------------------------------
   3. Navbar Scroll Elevation
   ------------------------------------------------------------- */
function initTopBarScroll() {
  const topbar = document.querySelector('.topbar');
  if (!topbar) return;
  window.addEventListener('scroll', () => {
    if (window.scrollY > 40) {
      topbar.classList.add('scrolled');
    } else {
      topbar.classList.remove('scrolled');
    }
  });
}

/* -------------------------------------------------------------
   4. Hero Slider
   ------------------------------------------------------------- */
function initHeroSlider() {
  const slides = document.querySelectorAll('.slide-item');
  const dots = document.querySelectorAll('.pagination-dot');
  if (slides.length <= 1) return;

  let current = 0;
  let timer = null;

  function showSlide(index) {
    slides.forEach(s => s.classList.remove('active'));
    dots.forEach(d => d.classList.remove('active'));

    current = (index + slides.length) % slides.length;
    slides[current].classList.add('active');
    if (dots[current]) dots[current].classList.add('active');
  }

  function startAutoPlay() {
    timer = setInterval(() => {
      showSlide(current + 1);
    }, 6000);
  }

  dots.forEach(dot => {
    dot.addEventListener('click', (e) => {
      clearInterval(timer);
      const idx = parseInt(e.target.dataset.index, 10);
      showSlide(idx);
      startAutoPlay();
    });
  });

  startAutoPlay();
}

/* -------------------------------------------------------------
   5. Horizontal Carousels
   ------------------------------------------------------------- */
function initCarousels() {
  const wrappers = document.querySelectorAll('.carousel-wrapper');
  wrappers.forEach(wrap => {
    const track = wrap.querySelector('.carousel-track');
    const prevBtn = wrap.querySelector('.carousel-btn.prev');
    const nextBtn = wrap.querySelector('.carousel-btn.next');

    if (!track) return;

    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        track.scrollBy({ left: -track.clientWidth * 0.75, behavior: 'smooth' });
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        track.scrollBy({ left: track.clientWidth * 0.75, behavior: 'smooth' });
      });
    }
  });
}

/* -------------------------------------------------------------
   6. Live Autocomplete Search
   ------------------------------------------------------------- */
function initLiveSearch() {
  const input = document.getElementById('search-input');
  const suggestionsBox = document.getElementById('search-suggestions');
  if (!input || !suggestionsBox) return;

  let debounceTimer;

  input.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    const query = input.value.trim();
    if (query.length < 2) {
      suggestionsBox.classList.remove('active');
      suggestionsBox.innerHTML = '';
      return;
    }

    debounceTimer = setTimeout(() => {
      fetch(`api/search.php?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
          if (data.length === 0) {
            suggestionsBox.innerHTML = '<div style="padding:1rem;color:var(--text-muted);font-size:0.875rem;">Không tìm thấy phim phù hợp</div>';
          } else {
            suggestionsBox.innerHTML = data.map(m => `
              <a href="movie-details.php?id=${m.id}" class="suggestion-item">
                <img src="${m.poster_url}" class="suggestion-poster" alt="${m.title}">
                <div class="suggestion-info">
                  <div class="suggestion-title">${m.title}</div>
                  <div class="suggestion-meta">
                    <span>${m.release_year}</span> • <span>★ ${m.imdb_rating}</span> • <span>${m.type === 'series' ? 'Phim bộ' : 'Phim lẻ'}</span>
                  </div>
                </div>
              </a>
            `).join('');
          }
          suggestionsBox.classList.add('active');
        })
        .catch(err => console.error(err));
    }, 250);
  });

  document.addEventListener('click', (e) => {
    if (!input.contains(e.target) && !suggestionsBox.contains(e.target)) {
      suggestionsBox.classList.remove('active');
    }
  });
}

/* -------------------------------------------------------------
   7. User Dropdown Menu
   ------------------------------------------------------------- */
function initUserDropdown() {
  const userBtn = document.getElementById('user-avatar-btn');
  const dropdown = document.getElementById('user-dropdown');
  if (!userBtn || !dropdown) return;

  userBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.classList.toggle('active');
  });

  document.addEventListener('click', () => {
    dropdown.classList.remove('active');
  });
}

/* -------------------------------------------------------------
   8. Trailer Modal
   ------------------------------------------------------------- */
function initTrailerModal() {
  const modal = document.getElementById('trailer-modal');
  const iframe = document.getElementById('trailer-iframe');
  const closeBtn = document.getElementById('modal-close-btn');
  const openBtns = document.querySelectorAll('.btn-open-trailer');

  if (!modal || !iframe) return;

  openBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const trailerUrl = btn.dataset.trailer;
      if (trailerUrl) {
        // Ensure autoplay parameter
        const embedUrl = trailerUrl.includes('?') ? `${trailerUrl}&autoplay=1` : `${trailerUrl}?autoplay=1`;
        iframe.src = embedUrl;
        modal.classList.add('active');
      }
    });
  });

  function closeModal() {
    modal.classList.remove('active');
    iframe.src = '';
  }

  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });
}

/* -------------------------------------------------------------
   9. AJAX Favorites
   ------------------------------------------------------------- */
function initFavorites() {
  document.addEventListener('click', (e) => {
    const favBtn = e.target.closest('.btn-toggle-fav');
    if (!favBtn) return;

    e.preventDefault();
    const movieId = favBtn.dataset.movieId;
    if (!movieId) return;

    fetch('api/favorites.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `movie_id=${encodeURIComponent(movieId)}`
    })
      .then(res => res.json())
      .then(res => {
        if (res.require_login) {
          window.location.href = 'login.php';
          return;
        }
        if (res.status === 'added') {
          favBtn.classList.add('active');
          showToast('Đã thêm vào danh sách yêu thích!');
        } else if (res.status === 'removed') {
          favBtn.classList.remove('active');
          showToast('Đã xóa khỏi danh sách yêu thích');
        }
      })
      .catch(err => console.error(err));
  });
}

/* -------------------------------------------------------------
   10. Interactive Star Rating Widget
   ------------------------------------------------------------- */
function initRatingWidget() {
  const starContainer = document.getElementById('star-rating-box');
  if (!starContainer) return;

  const stars = starContainer.querySelectorAll('.star');
  const ratingInput = document.getElementById('selected-rating-val');
  const movieId = starContainer.dataset.movieId;

  stars.forEach(star => {
    star.addEventListener('mouseover', () => {
      const val = parseInt(star.dataset.val, 10);
      stars.forEach(s => {
        s.classList.toggle('hover', parseInt(s.dataset.val, 10) <= val);
      });
    });

    star.addEventListener('mouseout', () => {
      stars.forEach(s => s.classList.remove('hover'));
    });

    star.addEventListener('click', () => {
      const val = parseInt(star.dataset.val, 10);
      if (ratingInput) ratingInput.value = val;
      stars.forEach(s => {
        s.classList.toggle('active', parseInt(s.dataset.val, 10) <= val);
      });

      // Submit via AJAX
      fetch('api/rating.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `movie_id=${encodeURIComponent(movieId)}&rating=${val}`
      })
      .then(res => res.json())
      .then(res => {
        if (res.success) {
          showToast(`Cảm ơn bạn đã đánh giá ${val} sao!`);
        } else if (res.require_login) {
          window.location.href = 'login.php';
        }
      });
    });
  });
}

/* -------------------------------------------------------------
   Toast Notification Helper
   ------------------------------------------------------------- */
function showToast(message) {
  let toast = document.getElementById('mv-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'mv-toast';
    toast.style.cssText = `
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      background: var(--bg-glass-strong);
      backdrop-filter: blur(16px);
      border: 1px solid var(--accent-purple);
      color: #fff;
      padding: 0.85rem 1.5rem;
      border-radius: var(--radius-full);
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
      z-index: 300;
      font-size: 0.9rem;
      font-weight: 600;
      transform: translateY(100px);
      opacity: 0;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    `;
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.style.transform = 'translateY(0)';
  toast.style.opacity = '1';

  setTimeout(() => {
    toast.style.transform = 'translateY(100px)';
    toast.style.opacity = '0';
  }, 3000);
}
