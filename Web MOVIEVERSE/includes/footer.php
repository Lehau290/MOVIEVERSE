  <!-- Trailer Modal -->
  <div id="trailer-modal" class="modal-backdrop">
    <div class="modal-content">
      <button type="button" id="modal-close-btn" class="modal-close-btn" aria-label="Đóng">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
      <div style="position:relative;padding-top:56.25%;">
        <iframe id="trailer-iframe" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
      </div>
    </div>
  </div>

  <!-- Site Footer -->
  <footer class="site-footer">
    <div class="footer-content">
      <div class="footer-brand">
        <a href="index.php" class="brand-logo">
          <div class="brand-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <polygon points="5 3 19 12 5 21 5 3"/>
            </svg>
          </div>
          <span class="brand-text">MovieVerse</span>
        </a>
        <p class="footer-desc">
          Nền tảng xem phim trực tuyến hàng đầu với giao diện cao cấp, âm thanh sống động và trải nghiệm rạp chiếu phim đỉnh cao ngay tại nhà.
        </p>
      </div>

      <div class="footer-col">
        <h4>Khám phá</h4>
        <ul class="footer-links">
          <li><a href="movies.php?type=movie">Phim Lẻ Chiếu Rạp</a></li>
          <li><a href="movies.php?type=series">Phim Bộ Độc Quyền</a></li>
          <li><a href="movies.php?sort=views">Phim Xem Nhiều Nhất</a></li>
          <li><a href="movies.php?sort=rating">Đánh Giá Cao Nhất</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Thể loại nổi bật</h4>
        <ul class="footer-links">
          <li><a href="movies.php?genre=hanh-dong">Hành Động</a></li>
          <li><a href="movies.php?genre=khoa-hoc-vien-tuong">Khoa Học Viễn Tưởng</a></li>
          <li><a href="movies.php?genre=hoat-hinh">Hoạt Hình & Anime</a></li>
          <li><a href="movies.php?genre=kinh-di">Kinh Dị & Giật Gân</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Hỗ trợ & Điều khoản</h4>
        <ul class="footer-links">
          <li><a href="#">Trung tâm trợ giúp</a></li>
          <li><a href="#">Chính sách bản quyền</a></li>
          <li><a href="#">Điều khoản sử dụng</a></li>
          <li><a href="#">Liên hệ quảng cáo</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <div>© <?= date('Y') ?> MovieVerse. Toàn bộ bản quyền thuộc về nền tảng. Phiên bản cao cấp.</div>
      <div style="display:flex;gap:1.5rem;">
        <span style="color:var(--text-muted);">Tốc độ truyền tải: 4K HDR</span>
        <span style="color:var(--accent-purple);">Máy chủ: Việt Nam</span>
      </div>
    </div>
  </footer>

  <script src="assets/js/main.js"></script>
</body>
</html>
