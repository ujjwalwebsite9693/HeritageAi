<?php
// app/helpers/Auth.php - Authentication and Role-based Access Control

require_once __DIR__ . '/../../config/db.php';

class Auth {
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                @ini_set('session.cookie_httponly', '1');
                @ini_set('session.use_only_cookies', '1');
            }
            @session_start();
        }
    }

    public static function check(): bool {
        self::init();
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array {
        self::init();
        if (!self::check()) {
            return null;
        }

        // Return cached session info or fetch latest from DB
        if (!isset($_SESSION['user_cached'])) {
            $pdo = DB::getConnection();
            $stmt = $pdo->prepare("SELECT id, name, email, role, api_key, institution FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            if ($user) {
                $_SESSION['user_cached'] = $user;
            } else {
                self::logout();
                return null;
            }
        }
        return $_SESSION['user_cached'];
    }

    public static function id(): ?int {
        $u = self::user();
        return $u ? (int)$u['id'] : null;
    }

    public static function role(): string {
        $u = self::user();
        return $u['role'] ?? 'visitor';
    }

    public static function hasRole(string ...$allowedRoles): bool {
        $currentRole = self::role();
        if ($currentRole === 'admin') {
            return true; // Admin has all rights
        }
        return in_array($currentRole, $allowedRoles, true);
    }

    public static function requireRole(string ...$allowedRoles): void {
        if (!self::hasRole(...$allowedRoles)) {
            if (self::isApiRequest()) {
                Response::error("Forbidden: Insufficient permissions for role [" . self::role() . "]", 403);
            } else {
                $_SESSION['flash_error'] = "You do not have access to this resource.";
                Response::redirect('/login');
            }
        }
    }

    public static function attempt(string $email, string $password): bool {
        self::init();
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([trim($email)]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Regenerate session id to prevent fixation if session is active
            if (session_status() === PHP_SESSION_ACTIVE) {
                @session_regenerate_id(true);
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_cached'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'api_key' => $user['api_key'],
                'institution' => $user['institution'],
            ];
            return true;
        }
        return false;
    }

    public static function logout(): void {
        self::init();
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

    public static function authenticateApi(): ?array {
        $key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;
        if (!$key) {
            // Fallback to active session user if present
            if (self::check()) {
                return self::user();
            }
            return null;
        }

        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, role, api_key, institution FROM users WHERE api_key = ?");
        $stmt->execute([$key]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function generateApiKey(int $userId): string {
        $key = 'mia_' . bin2hex(random_bytes(24));
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET api_key = ? WHERE id = ?");
        $stmt->execute([$key, $userId]);
        if (isset($_SESSION['user_cached'])) {
            $_SESSION['user_cached']['api_key'] = $key;
        }
        return $key;
    }

    private static function isApiRequest(): bool {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return str_starts_with($uri, '/api');
    }
}
