# 🎬 MOVIEVERSE - NỀN TẢNG XEM PHIM TRỰC TUYẾN CAO CẤP

**MovieVerse** là nền tảng xem phim trực tuyến hiện đại, phong cách **Premium Dark UI & Glassmorphism** lấy cảm hứng từ giao diện tinh tế của các nền tảng cao cấp (HBO Max, Disney+, Netflix) kết hợp cùng hiệu ứng thị giác khúc xạ ánh sáng và chuyển động mượt mà.

Website được xây dựng đầy đủ toàn bộ mã nguồn Frontend, Backend PHP hướng đối tượng/PDO và cơ sở dữ liệu MySQL, sẵn sàng chạy ngay trên **XAMPP** hoặc máy chủ web.

---

## ✨ CÁC TÍNH NĂNG NỔI BẬT

### 1. Trải nghiệm Giao diện & Người dùng (UI/UX)
- **Phong cách Premium Dark UI**: Tông màu chủ đạo đen sâu thẳm (`#06080e`), xanh bóng đêm và tím vũ trụ (`#8b5cf6`), điểm xuyết gradient neon cyan (`#06b6d4`).
- **Hiệu ứng Glassmorphism tinh xảo**: Kính mờ phản quang `backdrop-filter: blur(20px)` cho thanh điều hướng, card phim, thanh điều khiển player và modal.
- **Ambient Glowing Canvas**: Nền canvas hiệu ứng quang học mềm mại chuyển động tự nhiên theo thời gian thực.
- **Chế độ Sáng / Tối (Dark / Light Mode)**: Chuyển đổi giao diện tức thì chỉ với một cú chạm và lưu lại tùy chọn vào trình duyệt.
- **Responsive hoàn hảo**: Tối ưu hiển thị sắc nét trên Máy tính (Desktop/Laptop), Máy tính bảng (Tablet) và Điện thoại di động (Mobile).

### 2. Trang chủ & Khám phá phim
- **Hero Banner Slider**: Banner phim bom tấn khổ lớn kèm trailer xem nhanh, tiêu đề, tóm tắt và đánh giá IMDb.
- **Tiếp tục xem (Continue Watching)**: Tự động ghi nhớ các bộ phim đang xem dở kèm thanh tiến độ xem %.
- **Carousels mượt mà**: Danh sách phim Thịnh hành (Trending), Phim Mới cập nhật, Phim Đánh giá cao (IMDb 8.5+), Vũ trụ Khoa học viễn tưởng & Hoạt hình Anime.
- **Bộ lọc đa tiêu chí**: Lọc theo Phim lẻ / Phim bộ, Thể loại, Quốc gia, Năm phát hành, Điểm đánh giá và Sắp xếp (Mới nhất, Xem nhiều nhất, Điểm cao nhất).

### 3. Trình phát Video Trực Tuyến Hiện Đại (Modern Video Player)
- Trình phát video tùy biến cao cấp: Tua nhanh/tua lại 10s, thanh scrub bar kéo thả mượt mà.
- Chọn tốc độ phát: `0.5x`, `0.75x`, `1.0x (chuẩn)`, `1.25x`, `1.5x`, `2.0x`.
- Điều chỉnh âm lượng & phím tắt: Toàn màn hình (`F`), Tạm dừng/Phát (`Space`), Tua nhanh/lùi (`Mũi tên`).
- **Tự động lưu tiến độ**: Lưu vị trí giây đang xem vào cơ sở dữ liệu và tự động hỏi/tiếp tục phát từ điểm dừng khi quay lại.
- **Hỗ trợ phim bộ (TV Series)**: Hiển thị danh sách tập theo mùa (Season) kèm ảnh đại diện tập và tự động đếm ngược chuyển tập tiếp theo khi kết thúc.

### 4. Tương tác Người dùng & Cá nhân hóa
- **Tìm kiếm thông minh**: Gợi ý trực tiếp (Live Autocomplete Search) ngay khi người dùng gõ từ khóa.
- **Phim yêu thích**: Thêm/xóa phim khỏi danh sách yêu thích bằng AJAX mà không tải lại trang.
- **Đánh giá sao & Bình luận**: Chấm điểm từ 1 - 10 sao và thảo luận thời gian thực.
- **Hồ sơ cá nhân**: Đổi ảnh đại diện (avatar), cập nhật họ tên, đổi mật khẩu bảo mật và xem thống kê hoạt động.

### 5. Hệ thống Quản trị Toàn diện (Admin Panel)
- Phân quyền bảo mật: Chỉ tài khoản có `role = 'admin'` mới có quyền truy cập khu vực quản trị.
- **Quản lý Phim**: Thêm mới, chỉnh sửa thông tin phim, chọn thể loại, cập nhật poster/backdrop/trailer/video, xóa phim.
- **Quản lý Tập phim**: Thêm và chỉnh sửa các tập phim cho Series.
- **Quản lý Thể loại**: Thêm, sửa, xóa các thể loại phim.
- **Quản lý Banners Hero**: Thêm và sắp xếp các banner trượt trên trang chủ.
- **Quản lý Người dùng**: Xem danh sách thành viên, khóa/mở khóa tài khoản, phân quyền Quản trị viên (Admin) hoặc Thành viên (User).
- **Thống kê tổng quan**: Số lượng phim, tổng lượt xem toàn sàn, tổng số người dùng và bình luận.

---

## 🔐 BẢO MẬT HỆ THỐNG
- Mật khẩu người dùng được mã hóa bằng thuật toán `password_hash()` (BCRYPT) an toàn.
- Kiểm tra mật khẩu đăng nhập bằng `password_verify()`.
- Toàn bộ truy vấn cơ sở dữ liệu sử dụng **PDO Prepared Statements** ngăn chặn triệt để SQL Injection.
- Lọc và khử độc dữ liệu đầu vào chống tấn công XSS (Cross-Site Scripting).
- Kiểm tra quyền hạn nghiêm ngặt tại tầng Backend.

---

## 📂 CẤU TRÚC THƯ MỤC DỰ ÁN

```text
d:\Web MOVIEVERSE\
├── admin\                      # Bảng điều khiển Quản trị viên
│   ├── admin-header.php        # Header & Sidebar admin
│   ├── admin-footer.php        # Footer admin
│   ├── index.php               # Bảng thống kê tổng quan
│   ├── movies.php              # Quản lý danh sách phim
│   ├── movie-form.php          # Form thêm & sửa phim
│   ├── episodes.php            # Quản lý tập phim bộ
│   ├── genres.php              # Quản lý thể loại phim
│   ├── banners.php             # Quản lý hero banners
│   └── users.php               # Quản lý người dùng & phân quyền
├── api\                        # API endpoints xử lý AJAX
│   ├── search.php              # Tìm kiếm gợi ý tự động
│   ├── favorites.php           # Thêm / xóa phim yêu thích
│   ├── progress.php            # Tự động lưu tiến độ xem phim
│   ├── rating.php              # Chấm điểm sao phim
│   └── comments.php            # Đăng và tải bình luận
├── assets\                     # Tài nguyên giao diện
│   ├── css\
│   │   ├── style.css           # CSS giao diện chính (Glassmorphism & Dark UI)
│   │   └── admin.css           # CSS khu vực quản trị
│   └── js\
│       ├── main.js             # Canvas, slider, live search, theme toggle
│       └── player.js           # Video player hiện đại, tiến độ xem, phím tắt
├── config\                     # Cấu hình lõi
│   ├── database.php            # Kết nối PDO MySQL (kèm SQLite auto-fallback)
│   ├── auth.php                # Quản lý session, đăng nhập, đăng ký, phân quyền
│   └── helpers.php             # Hàm tiện ích xử lý chuỗi, thời gian, slug
├── database\
│   ├── database.sql            # File DDL MySQL tạo bảng & ràng buộc khóa ngoại
│   └── seed_data.php           # Trình nạp dữ liệu phim, poster, tập phim mẫu
├── includes\                   # Các khối giao diện tái sử dụng
│   ├── header.php              # Navbar kính mờ, tìm kiếm, avatar menu
│   ├── footer.php              # Footer thông tin, liên kết, modal trailer
│   └── movie-card.php          # Component thẻ phim tương tác
├── index.php                   # Trang chủ MovieVerse
├── movies.php                  # Trang danh sách phim & bộ lọc
├── movie-details.php           # Trang chi tiết phim, diễn viên, bình luận
├── watch.php                   # Trang xem phim & chọn tập
├── favorites.php               # Trang danh sách phim đã lưu
├── history.php                 # Trang lịch sử xem phim
├── profile.php                 # Trang hồ sơ cá nhân & đổi mật khẩu
├── login.php                   # Trang đăng nhập
├── register.php                # Trang đăng ký
├── logout.php                  # Đăng xuất
├── setup.php                   # Trang kiểm tra & nạp dữ liệu mẫu trực quan
└── README.md                   # Tài liệu hướng dẫn sử dụng
```

---

## 🚀 HƯỚNG DẪN CÀI ĐẶT & CHẠY TRÊN XAMPP

### Cách 1: Chạy trực tiếp trên XAMPP Apache & MySQL

1. **Sao chép thư mục dự án**:
   - Đặt toàn bộ thư mục `Web MOVIEVERSE` vào thư mục `htdocs` của XAMPP:
     `C:\xampp\htdocs\movieverse` (hoặc giữ nguyên `C:\xampp\htdocs\Web MOVIEVERSE`).
2. **Khởi động dịch vụ trong XAMPP Control Panel**:
   - Mở **XAMPP Control Panel**.
   - Bấm **Start** ở cả 2 module: **Apache** và **MySQL**.
3. **Nhập Cơ sở dữ liệu (Import Database)**:
   - Truy cập vào **phpMyAdmin** trên trình duyệt: `http://localhost/phpmyadmin/`.
   - Tạo mới một cơ sở dữ liệu có tên: `movieverse` (chọn Collation: `utf8mb4_unicode_ci`).
   - Chọn database `movieverse` vừa tạo, bấm tab **Import (Nhập)** -> Chọn file `database/database.sql` trong thư mục dự án -> Bấm **Import**.
   *(Lưu ý: Nếu bạn chưa kịp tạo DB, hệ thống có cơ chế tự động khởi tạo khi bạn mở trang web lần đầu!)*
4. **Truy cập Website**:
   - Mở trình duyệt và truy cập:
     ```text
     http://localhost/movieverse/
     ```
     hoặc truy cập trang kiểm tra trạng thái:
     ```text
     http://localhost/movieverse/setup.php
     ```

---

### Cách 2: Chạy kiểm tra nhanh bằng PHP Built-in Server
Nếu chưa mở XAMPP MySQL, bạn có thể chạy ngay bằng lệnh PHP tích hợp:
1. Mở PowerShell hoặc Terminal tại thư mục dự án `d:\Web MOVIEVERSE`.
2. Chạy lệnh:
   ```bash
   php -S localhost:8000
   ```
3. Mở trình duyệt và truy cập:
   ```text
   http://localhost:8000
   ```
*(Hệ thống đã tích hợp sẵn trình điều khiển tự động chuyển đổi sang SQLite nếu MySQL chưa được bật, giúp bạn trải nghiệm và kiểm thử ngay lập tức mà không bị gián đoạn).*

---

## 🔑 TÀI KHOẢN MẪU ĐỂ KIỂM THỬ (DEMO ACCOUNTS)

Hệ thống đã có sẵn 2 tài khoản mẫu:

| Loại tài khoản | Tên đăng nhập | Mật khẩu | Quyền hạn |
| :--- | :--- | :--- | :--- |
| **Quản trị viên (Admin)** | `admin` | `admin123` | Toàn quyền quản trị hệ thống, quản lý phim, thể loại, banner, người dùng |
| **Thành viên (User)** | `user` | `user123` | Xem phim, lưu yêu thích, đánh giá sao, bình luận, xem lịch sử |

---

## 🍿 CHÚC BẠN CÓ NHỮNG TRẢI NGHIỆM TUYỆT VỜI CÙNG MOVIEVERSE!
Mọi thắc mắc hoặc yêu cầu nâng cấp tính năng mới, vui lòng mở rộng trong các module tương ứng.
