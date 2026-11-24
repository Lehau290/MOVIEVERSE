<?php
/**
 * MovieVerse - Authentication & Session Helper
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

class Auth {
    public static function login(string $usernameOrEmail, string $password): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE (username = :u OR email = :u) AND status = 'active' LIMIT 1");
        $stmt->execute(['u' => $usernameOrEmail]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'] ?? $user['username'];
            $_SESSION['avatar'] = $user['avatar'] ?? 'assets/images/default-avatar.png';
            $_SESSION['role'] = $user['role'];
            return true;
        }

        return false;
    }

    public static function register(string $username, string $email, string $password, string $fullName = ''): array {
        $db = Database::getConnection();

        // Validation
        if (strlen($username) < 3) {
            return ['success' => false, 'message' => 'Tên đăng nhập phải có ít nhất 3 ký tự.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email không đúng định dạng.'];
        }
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự.'];
        }

        // Check duplicates
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1");
        $stmt->execute(['u' => $username, 'e' => $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Tên đăng nhập hoặc Email đã tồn tại.'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $name = !empty($fullName) ? $fullName : $username;

        $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (:u, :e, :p, :f, 'user')");
        $stmt->execute([
            'u' => $username,
            'e' => $email,
            'p' => $hash,
            'f' => $name
        ]);

        return ['success' => true, 'message' => 'Đăng ký tài khoản thành công! Hãy đăng nhập.'];
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function isAdmin(): bool {
        return self::isLoggedIn() && ($_SESSION['role'] === 'admin');
    }

    public static function user(): ?array {
        if (!self::isLoggedIn()) return null;
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, username, email, full_name, avatar, role, created_at FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        return $stmt->fetch() ?: null;
    }

    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            $currentUrl = urlencode($_SERVER['REQUEST_URI']);
            header("Location: login.php?redirect=" . $currentUrl);
            exit;
        }
    }

    public static function requireAdmin(): void {
        if (!self::isAdmin()) {
            header("Location: ../login.php?error=unauthorized");
            exit;
        }
    }

    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
