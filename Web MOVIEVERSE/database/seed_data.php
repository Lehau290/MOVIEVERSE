<?php
/**
 * MovieVerse - Database Seeder & Schema Initializer
 * Automatically runs on first launch if database is empty
 */

class MovieVerseSeeder {
    public static function run(PDO $db, string $driver = 'mysql'): void {
        self::createTables($db, $driver);
        self::seedData($db);
    }

    private static function createTables(PDO $db, string $driver): void {
        if ($driver === 'sqlite') {
            $queries = [
                "CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT UNIQUE NOT NULL,
                    email TEXT UNIQUE NOT NULL,
                    password TEXT NOT NULL,
                    full_name TEXT,
                    avatar TEXT DEFAULT 'assets/images/default-avatar.png',
                    role TEXT DEFAULT 'user',
                    status TEXT DEFAULT 'active',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS genres (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    slug TEXT UNIQUE NOT NULL,
                    description TEXT
                )",
                "CREATE TABLE IF NOT EXISTS movies (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    original_title TEXT,
                    slug TEXT UNIQUE NOT NULL,
                    type TEXT DEFAULT 'movie',
                    description TEXT,
                    poster_url TEXT NOT NULL,
                    backdrop_url TEXT,
                    trailer_url TEXT,
                    video_url TEXT,
                    release_year INTEGER DEFAULT 2024,
                    duration_minutes INTEGER DEFAULT 120,
                    country TEXT DEFAULT 'Mỹ',
                    director TEXT,
                    cast TEXT,
                    imdb_rating REAL DEFAULT 7.5,
                    view_count INTEGER DEFAULT 0,
                    is_featured INTEGER DEFAULT 0,
                    status TEXT DEFAULT 'published',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS movie_genres (
                    movie_id INTEGER NOT NULL,
                    genre_id INTEGER NOT NULL,
                    PRIMARY KEY (movie_id, genre_id)
                )",
                "CREATE TABLE IF NOT EXISTS episodes (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    movie_id INTEGER NOT NULL,
                    season_number INTEGER DEFAULT 1,
                    episode_number INTEGER DEFAULT 1,
                    title TEXT NOT NULL,
                    video_url TEXT NOT NULL,
                    duration_minutes INTEGER DEFAULT 45,
                    thumbnail_url TEXT
                )",
                "CREATE TABLE IF NOT EXISTS banners (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    movie_id INTEGER,
                    title TEXT NOT NULL,
                    subtitle TEXT,
                    image_url TEXT NOT NULL,
                    button_text TEXT DEFAULT 'Xem ngay',
                    button_link TEXT DEFAULT '#',
                    order_num INTEGER DEFAULT 0,
                    is_active INTEGER DEFAULT 1
                )",
                "CREATE TABLE IF NOT EXISTS favorites (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    movie_id INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(user_id, movie_id)
                )",
                "CREATE TABLE IF NOT EXISTS watch_history (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    movie_id INTEGER NOT NULL,
                    episode_id INTEGER,
                    progress_seconds INTEGER DEFAULT 0,
                    duration_seconds INTEGER DEFAULT 0,
                    last_watched DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(user_id, movie_id)
                )",
                "CREATE TABLE IF NOT EXISTS ratings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    movie_id INTEGER NOT NULL,
                    rating REAL NOT NULL,
                    review TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(user_id, movie_id)
                )",
                "CREATE TABLE IF NOT EXISTS comments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    movie_id INTEGER NOT NULL,
                    parent_id INTEGER,
                    content TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )"
            ];

            foreach ($queries as $sql) {
                $db->exec($sql);
            }
        } else {
            // MySQL: Load from database.sql
            $sqlFile = __DIR__ . '/database.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($statements as $stmt) {
                    if (!empty($stmt)) {
                        try {
                            $db->exec($stmt);
                        } catch (PDOException $e) {
                            // ignore already exists
                        }
                    }
                }
            }
        }
    }

    private static function seedData(PDO $db): void {
        // Check if users already seeded
        $chk = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($chk > 0) return;

        // 1. Seed Users (admin123, user123)
        $users = [
            [
                'username' => 'admin',
                'email' => 'admin@movieverse.vn',
                'password' => password_hash('admin123', PASSWORD_BCRYPT),
                'full_name' => 'Quản Trị Viên MovieVerse',
                'role' => 'admin',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80'
            ],
            [
                'username' => 'user',
                'email' => 'user@movieverse.vn',
                'password' => password_hash('user123', PASSWORD_BCRYPT),
                'full_name' => 'Nguyễn Minh Tuấn',
                'role' => 'user',
                'avatar' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=300&q=80'
            ]
        ];

        $stmtUser = $db->prepare("INSERT INTO users (username, email, password, full_name, role, avatar) VALUES (:u, :e, :p, :f, :r, :a)");
        foreach ($users as $u) {
            $stmtUser->execute([
                'u' => $u['username'],
                'e' => $u['email'],
                'p' => $u['password'],
                'f' => $u['full_name'],
                'r' => $u['role'],
                'a' => $u['avatar']
            ]);
        }

        // 2. Seed Genres
        $genres = [
            ['name' => 'Hành Động', 'slug' => 'hanh-dong', 'description' => 'Những pha rượt đuổi, chiến đấu gay cấn kịch tính.'],
            ['name' => 'Khoa Học Viễn Tưởng', 'slug' => 'khoa-hoc-vien-tuong', 'description' => 'Vũ trụ, tương lai, du hành thời gian và công nghệ kỳ ảo.'],
            ['name' => 'Phiêu Lưu', 'slug' => 'phieu-luu', 'description' => 'Những chuyến hành trình khám phá thế giới mới.'],
            ['name' => 'Kinh Dị', 'slug' => 'kinh-di', 'description' => 'Hồi hộp, ám ảnh và những bí ẩn kinh hoàng.'],
            ['name' => 'Tình Cảm', 'slug' => 'tinh-cam', 'description' => 'Những câu chuyện tình yêu sâu lắng, rung động trái tim.'],
            ['name' => 'Hoạt Hình', 'slug' => 'hoat-hinh', 'description' => 'Anime, đồ họa 3D đỉnh cao dành cho mọi lứa tuổi.'],
            ['name' => 'Tâm Lý', 'slug' => 'tam-ly', 'description' => 'Chiều sâu cảm xúc, khám phá bản chất con người.'],
            ['name' => 'Hài Hước', 'slug' => 'hai-huoc', 'description' => 'Những tràng cười sảng khoái và thư giãn nhẹ nhàng.'],
            ['name' => 'Giật Gân', 'slug' => 'giat-gan', 'description' => 'Đấu trí đỉnh cao, bất ngờ đến phút cuối cùng.'],
            ['name' => 'Kỳ Ảo', 'slug' => 'ky-ao', 'description' => 'Phép thuật, rồng thiêng và những vương quốc thần thoại.']
        ];

        $stmtGenre = $db->prepare("INSERT INTO genres (name, slug, description) VALUES (:name, :slug, :description)");
        foreach ($genres as $g) {
            $stmtGenre->execute($g);
        }

        // 3. Seed Movies & Series with open-source test video streams & trailers
        // Open legal test streams: Big Buck Bunny (HLS/MP4), Tears of Steel, Elephants Dream, Sintel
        $sampleVideos = [
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WeAreGoingOnBullrun.mp4'
        ];

        $movies = [
            [
                'title' => 'Dune: Hành Tinh Cát - Phần Hai',
                'original_title' => 'Dune: Part Two',
                'slug' => 'dune-hanh-tinh-cat-phan-hai',
                'type' => 'movie',
                'description' => 'Paul Atreides liên minh cùng Chani và người Fremen trên hành trình báo thù những kẻ đã hủy hoại gia đình anh. Đứng trước sự lựa chọn giữa tình yêu và số phận của vũ trụ, anh phải ngăn chặn một tương lai đen tối mà chỉ mình anh có thể thấy trước.',
                'poster_url' => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=600&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1920&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/Way9Dexny3w',
                'video_url' => $sampleVideos[1],
                'release_year' => 2024,
                'duration_minutes' => 166,
                'country' => 'Mỹ',
                'director' => 'Denis Villeneuve',
                'cast' => 'Timothée Chalamet, Zendaya, Rebecca Ferguson, Javier Bardem',
                'imdb_rating' => 8.6,
                'view_count' => 125400,
                'is_featured' => 1,
                'genres' => ['khoa-hoc-vien-tuong', 'phieu-luu', 'hanh-dong']
            ],
            [
                'title' => 'Oppenheimer',
                'original_title' => 'Oppenheimer',
                'slug' => 'oppenheimer',
                'type' => 'movie',
                'description' => 'Câu chuyện về cuộc đời của nhà vật lý lý thuyết J. Robert Oppenheimer, người đóng vai trò then chốt trong Dự án Manhattan phát triển vũ khí hạt nhân đầu tiên trong Thế chiến thứ hai.',
                'poster_url' => 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?auto=format&fit=crop&w=600&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1920&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/uYPbbksJxIg',
                'video_url' => $sampleVideos[0],
                'release_year' => 2023,
                'duration_minutes' => 180,
                'country' => 'Mỹ',
                'director' => 'Christopher Nolan',
                'cast' => 'Cillian Murphy, Emily Blunt, Matt Damon, Robert Downey Jr.',
                'imdb_rating' => 8.9,
                'view_count' => 189200,
                'is_featured' => 1,
                'genres' => ['tam-ly', 'giat-gan']
            ],
            [
                'title' => 'Arcane: Liên Minh Huyền Thoại',
                'original_title' => 'Arcane: League of Legends',
                'slug' => 'arcane-lien-minh-huyen-thoai',
                'type' => 'series',
                'description' => 'Giữa sự bất hòa gay gắt giữa hai thành phố Piltover giàu có và Zaun nghèo nàn, hai chị em Vi và Powder (Jinx) chiến đấu ở hai đầu chiến tuyến trong một cuộc chiến phép thuật và công nghệ.',
                'poster_url' => 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=600&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=1920&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/fXmAurh012s',
                'video_url' => $sampleVideos[2],
                'release_year' => 2024,
                'duration_minutes' => 45,
                'country' => 'Mỹ',
                'director' => 'Christian Linke, Alex Yee',
                'cast' => 'Hailee Steinfeld, Ella Purnell, Kevin Alejandro',
                'imdb_rating' => 9.0,
                'view_count' => 234100,
                'is_featured' => 1,
                'genres' => ['hoat-hinh', 'hanh-dong', 'ky-ao']
            ],
            [
                'title' => 'Interstellar: Hố Đen Tử Thần',
                'original_title' => 'Interstellar',
                'slug' => 'interstellar-ho-den-tu-than',
                'type' => 'movie',
                'description' => 'Khi Trái Đất đối mặt với ngày tận thế do khủng hoảng lương thực, một nhóm phi hành gia du hành qua một lỗ sâu mới xuất hiện trong không gian nhằm tìm kiếm hành tinh mới cho nhân loại.',
                'poster_url' => 'https://images.unsplash.com/photo-1506703719100-a0f3a48c0f86?auto=format&fit=crop&w=600&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1447433589675-4aaa569f3e05?auto=format&fit=crop&w=1920&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/zSWdZVtXT7E',
                'video_url' => $sampleVideos[3],
                'release_year' => 2014,
                'duration_minutes' => 169,
                'country' => 'Mỹ',
                'director' => 'Christopher Nolan',
                'cast' => 'Matthew McConaughey, Anne Hathaway, Jessica Chastain',
                'imdb_rating' => 8.7,
                'view_count' => 312000,
                'is_featured' => 0,
                'genres' => ['khoa-hoc-vien-tuong', 'phieu-luu', 'tam-ly']
            ],
            [
                'title' => 'Người Nhện: Du Hành Vũ Trụ Nhện',
                'original_title' => 'Spider-Man: Across the Spider-Verse',
                'slug' => 'spider-man-across-the-spider-verse',
                'type' => 'movie',
                'description' => 'Miles Morales được đưa qua Đa vũ trụ, nơi cậu gặp gỡ một đội ngũ Người Nhện được giao nhiệm vụ bảo vệ chính sự tồn tại của Đa vũ trụ. Khi các anh hùng xung đột về cách xử lý mối đe dọa, Miles phải định nghĩa lại ý nghĩa của việc trở thành anh hùng.',
                'poster_url' => 'https://images.unsplash.com/photo-1635805737707-575885ab0820?auto=format&fit=crop&w=600&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=1920&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/cqGjhVJWtEg',
                'video_url' => $sampleVideos[4],
                'release_year' => 2023,
                'duration_minutes' => 140,
                'country' => 'Mỹ',
                'director' => 'Joaquim Dos Santos, Kemp Powers',
                'cast' => 'Shameik Moore, Hailee Steinfeld, Oscar Isaac',
                'imdb_rating' => 8.8,
                'view_count' => 195000,
                'is_featured' => 1,
                'genres' => ['hoat-hinh', 'hanh-dong', 'khoa-hoc-vien-tuong']
            ],
            [
                'title' => 'Ký Sinh Trùng',
                'original_title' => 'Parasite (Gisaengchung)',
                'slug' => 'ky-sinh-trung-parasite',
                'type' => 'movie',
                'description' => 'Một gia đình nghèo từng bước thâm nhập và làm việc cho một gia đình siêu giàu bằng những mưu kế tinh vi, dẫn đến những biến cố không thể lường trước.',
                'poster_url' => 'https://images.unsplash.com/photo-1518173946687-a4c8a383392e?auto=format&fit=crop&w=600&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1920&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/5xH0RzeSojI',
                'video_url' => $sampleVideos[0],
                'release_year' => 2019,
                'duration_minutes' => 132,
                'country' => 'Hàn Quốc',
                'director' => 'Bong Joon-ho',
                'cast' => 'Song Kang-ho, Lee Sun-kyun, Cho Yeo-jeong, Choi Woo-shik',
                'imdb_rating' => 8.5,
                'view_count' => 220000,
                'is_featured' => 0,
                'genres' => ['tam-ly', 'giat-gan', 'hai-huoc']
            ],
            [
                'title' => 'Stranger Things: Cậu Bé Mất Tích',
                'original_title' => 'Stranger Things',
                'slug' => 'stranger-things-cau-be-mat-tich',
                'type' => 'series',
                'description' => 'Khi một cậu bé biến mất một cách bí ẩn, thị trấn nhỏ Hawkins bắt đầu hé lộ những thí nghiệm bí mật, thế lực siêu nhiên khủng khiếp và một cô bé có năng lực ngoại cảm kỳ lạ.',
                'poster_url' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=600&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1920&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/b9EkMc79ZSU',
                'video_url' => $sampleVideos[1],
                'release_year' => 2023,
                'duration_minutes' => 55,
                'country' => 'Mỹ',
                'director' => 'The Duffer Brothers',
                'cast' => 'Millie Bobby Brown, Finn Wolfhard, Winona Ryder, David Harbour',
                'imdb_rating' => 8.7,
                'view_count' => 450000,
                'is_featured' => 0,
                'genres' => ['khoa-hoc-vien-tuong', 'kinh-di', 'tam-ly']
            ],
            [
                'title' => 'Vùng Đất Câm Lặng: Ngày Đầu Tiên',
                'original_title' => 'A Quiet Place: Day One',
                'slug' => 'vung-dat-cam-lang-ngay-dau-tien',
                'type' => 'movie',
                'description' => 'Trải nghiệm ngày thế giới rơi vào tĩnh lặng hoàn toàn khi những sinh vật ngoài hành tinh sở hữu thính giác siêu phàm đổ bộ xuống thành phố New York náo nhiệt.',
                'poster_url' => 'https://images.unsplash.com/photo-1509281373149-e957c6296406?auto=format&fit=crop&w=600&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1478760329108-5c3ed9d495a0?auto=format&fit=crop&w=1920&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/YPY7J-flzE8',
                'video_url' => $sampleVideos[2],
                'release_year' => 2024,
                'duration_minutes' => 100,
                'country' => 'Mỹ',
                'director' => 'Michael Sarnoski',
                'cast' => 'Lupita Nyong\'o, Joseph Quinn, Alex Wolff, Djimon Hounsou',
                'imdb_rating' => 7.2,
                'view_count' => 110000,
                'is_featured' => 0,
                'genres' => ['kinh-di', 'giat-gan', 'khoa-hoc-vien-tuong']
            ],
            [
                'title' => 'The Batman: Vạch Trần Bóng Tối',
                'original_title' => 'The Batman',
                'slug' => 'the-batman-vach-tran-bong-toi',
                'type' => 'movie',
                'description' => 'Trong năm thứ hai hoạt động chống tội phạm, Batman khám phá sự thối nát tại Gotham City trong khi săn đuổi Riddler, một kẻ giết người hàng loạt nhắm vào giới thượng lưu thành phố.',
                'poster_url' => 'https://images.unsplash.com/photo-1509248961158-e54f6934749c?auto=format&fit=crop&w=600&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1514565131-fce0801e5785?auto=format&fit=crop&w=1920&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/mqqft2x_Aa4',
                'video_url' => $sampleVideos[3],
                'release_year' => 2022,
                'duration_minutes' => 176,
                'country' => 'Mỹ',
                'director' => 'Matt Reeves',
                'cast' => 'Robert Pattinson, Zoë Kravitz, Jeffrey Wright, Colin Farrell',
                'imdb_rating' => 7.9,
                'view_count' => 210000,
                'is_featured' => 0,
                'genres' => ['hanh-dong', 'giat-gan', 'tam-ly']
            ],
            [
                'title' => 'Kẻ Trộm Giấc Mơ',
                'original_title' => 'Inception',
                'slug' => 'ke-trom-giac-mo-inception',
                'type' => 'movie',
                'description' => 'Một tên trộm chuyên xâm nhập vào tiềm thức của người khác để đánh cắp bí mật kinh doanh được trao cơ hội xóa sạch quá khứ tội lỗi bằng cách thực hiện điều ngược lại: gieo ý tưởng vào tâm trí mục tiêu.',
                'poster_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=600&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1507499739999-097706ad8914?auto=format&fit=crop&w=1920&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/YoHD9XEInc0',
                'video_url' => $sampleVideos[4],
                'release_year' => 2010,
                'duration_minutes' => 148,
                'country' => 'Mỹ',
                'director' => 'Christopher Nolan',
                'cast' => 'Leonardo DiCaprio, Joseph Gordon-Levitt, Elliot Page, Tom Hardy',
                'imdb_rating' => 8.8,
                'view_count' => 480000,
                'is_featured' => 0,
                'genres' => ['khoa-hoc-vien-tuong', 'hanh-dong', 'giat-gan']
            ],
            [
                'title' => 'Tên Cậu Là Gì?',
                'original_title' => 'Your Name (Kimi no Na wa)',
                'slug' => 'ten-cau-la-gi-your-name',
                'type' => 'movie',
                'description' => 'Hai thiếu niên xa lạ bỗng nhiên bị hoán đổi cơ thể trong những giấc mơ. Khi sự gắn kết giữa họ ngày càng sâu sắc, một bí mật khủng khiếp về thời gian và định mệnh dần được mở ra.',
                'poster_url' => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=600&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=1920&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/xU47nhruN-Q',
                'video_url' => $sampleVideos[0],
                'release_year' => 2016,
                'duration_minutes' => 106,
                'country' => 'Nhật Bản',
                'director' => 'Makoto Shinkai',
                'cast' => 'Ryunosuke Kamiki, Mone Kamishiraishi, Ryo Narita',
                'imdb_rating' => 8.4,
                'view_count' => 380000,
                'is_featured' => 0,
                'genres' => ['hoat-hinh', 'tinh-cam', 'ky-ao']
            ],
            [
                'title' => 'Gia Tộc Rồng',
                'original_title' => 'House of the Dragon',
                'slug' => 'gia-toc-rong-house-of-the-dragon',
                'type' => 'series',
                'description' => 'Lấy bối cảnh 200 năm trước sự kiện Game of Thrones, câu chuyện kể về khởi đầu của sự kết thúc cho Gia tộc Targaryen và cuộc nội chiến đẫm máu được biết đến là Vũ Điệu Của Loài Rồng.',
                'poster_url' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=600&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=1920&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/DotnJ7tTA34',
                'video_url' => $sampleVideos[1],
                'release_year' => 2024,
                'duration_minutes' => 60,
                'country' => 'Mỹ',
                'director' => 'Ryan J. Condal, George R. R. Martin',
                'cast' => 'Matt Smith, Emma D\'Arcy, Olivia Cooke, Rhys Ifans',
                'imdb_rating' => 8.5,
                'view_count' => 290000,
                'is_featured' => 1,
                'genres' => ['ky-ao', 'hanh-dong', 'tam-ly']
            ]
        ];

        // Fetch genre slugs map
        $genreMap = [];
        $res = $db->query("SELECT id, slug FROM genres")->fetchAll();
        foreach ($res as $row) {
            $genreMap[$row['slug']] = $row['id'];
        }

        $stmtMovie = $db->prepare("INSERT INTO movies (title, original_title, slug, type, description, poster_url, backdrop_url, trailer_url, video_url, release_year, duration_minutes, country, director, cast, imdb_rating, view_count, is_featured) VALUES (:title, :orig, :slug, :type, :desc, :poster, :backdrop, :trailer, :video, :year, :duration, :country, :director, :cast, :imdb, :views, :featured)");
        
        $stmtMg = $db->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (:m, :g)");
        $stmtEp = $db->prepare("INSERT INTO episodes (movie_id, season_number, episode_number, title, video_url, duration_minutes, thumbnail_url) VALUES (:m, :s, :e, :t, :v, :d, :thumb)");

        foreach ($movies as $m) {
            $stmtMovie->execute([
                'title' => $m['title'],
                'orig' => $m['original_title'],
                'slug' => $m['slug'],
                'type' => $m['type'],
                'desc' => $m['description'],
                'poster' => $m['poster_url'],
                'backdrop' => $m['backdrop_url'],
                'trailer' => $m['trailer_url'],
                'video' => $m['video_url'],
                'year' => $m['release_year'],
                'duration' => $m['duration_minutes'],
                'country' => $m['country'],
                'director' => $m['director'],
                'cast' => $m['cast'],
                'imdb' => $m['imdb_rating'],
                'views' => $m['view_count'],
                'featured' => $m['is_featured']
            ]);

            $movieId = $db->lastInsertId();

            // Insert genres
            foreach ($m['genres'] as $gSlug) {
                if (isset($genreMap[$gSlug])) {
                    $stmtMg->execute(['m' => $movieId, 'g' => $genreMap[$gSlug]]);
                }
            }

            // If series, create 4 sample episodes
            if ($m['type'] === 'series') {
                for ($ep = 1; $ep <= 4; $ep++) {
                    $vid = $sampleVideos[$ep % count($sampleVideos)];
                    $stmtEp->execute([
                        'm' => $movieId,
                        's' => 1,
                        'e' => $ep,
                        't' => "Tập {$ep}: Sự Khởi Đầu Mới",
                        'v' => $vid,
                        'd' => $m['duration_minutes'],
                        'thumb' => $m['backdrop_url']
                    ]);
                }
            }
        }

        // 4. Seed Banners
        $banners = [
            [
                'movie_id' => 1,
                'title' => 'Dune: Hành Tinh Cát - Phần Hai',
                'subtitle' => 'Siêu phẩm điện ảnh khoa học viễn tưởng hoành tráng nhất năm',
                'image_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1920&q=80',
                'button_text' => 'Xem Ngay',
                'button_link' => 'watch.php?id=1',
                'order_num' => 1
            ],
            [
                'movie_id' => 3,
                'title' => 'Arcane: Mùa Mới Khởi Tranh',
                'subtitle' => 'Đỉnh cao hoạt họa và âm nhạc từ vũ trụ Liên Minh Huyền Thoại',
                'image_url' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=1920&q=80',
                'button_text' => 'Khám Phá',
                'button_link' => 'watch.php?id=3',
                'order_num' => 2
            ],
            [
                'movie_id' => 5,
                'title' => 'Người Nhện: Du Hành Vũ Trụ Nhện',
                'subtitle' => 'Hành trình vượt qua mọi giới hạn định mệnh cùng Miles Morales',
                'image_url' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=1920&q=80',
                'button_text' => 'Xem Ngay',
                'button_link' => 'watch.php?id=5',
                'order_num' => 3
            ]
        ];

        $stmtBanner = $db->prepare("INSERT INTO banners (movie_id, title, subtitle, image_url, button_text, button_link, order_num) VALUES (:m, :t, :s, :i, :bt, :bl, :o)");
        foreach ($banners as $b) {
            $stmtBanner->execute([
                'm' => $b['movie_id'],
                't' => $b['title'],
                's' => $b['subtitle'],
                'i' => $b['image_url'],
                'bt' => $b['button_text'],
                'bl' => $b['button_link'],
                'o' => $b['order_num']
            ]);
        }

        // 5. Seed sample ratings and comments
        $stmtComment = $db->prepare("INSERT INTO comments (user_id, movie_id, content, created_at) VALUES (:u, :m, :c, :date)");
        $stmtRating = $db->prepare("INSERT INTO ratings (user_id, movie_id, rating, review) VALUES (:u, :m, :r, :rev)");

        $stmtRating->execute(['u' => 1, 'm' => 1, 'r' => 9.5, 'rev' => 'Hình ảnh và âm thanh của Hans Zimmer quá xuất sắc, không thể rời mắt!']);
        $stmtRating->execute(['u' => 2, 'm' => 1, 'r' => 9.0, 'rev' => 'Một kiệt tác điện ảnh sci-fi thực thụ. Diễn xuất của Timothée quá đỉnh cao!']);
        $stmtRating->execute(['u' => 2, 'm' => 2, 'r' => 9.8, 'rev' => 'Christopher Nolan lại một lần nữa chứng minh tài năng kể chuyện thiên tài.']);

        $stmtComment->execute(['u' => 1, 'm' => 1, 'c' => 'Phim này xem trên màn hình lớn cùng hệ thống âm thanh vòm thì cực đã luôn mọi người ơi!', 'date' => date('Y-m-d H:i:s', time() - 3600 * 24)]);
        $stmtComment->execute(['u' => 2, 'm' => 1, 'c' => 'Công nhận luôn, kỹ xảo hoang mạc Arrakis nhìn chân thực đến rùng mình.', 'date' => date('Y-m-d H:i:s', time() - 3600 * 5)]);
    }
}
