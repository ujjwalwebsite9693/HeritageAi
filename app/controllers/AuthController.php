<?php
// app/controllers/AuthController.php - User authentication and Profile

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Csrf.php';
require_once __DIR__ . '/../helpers/Response.php';

class AuthController {
    public function login(): void {
        if (Auth::check()) {
            Response::redirect('/profile');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate()) {
                $error = "Session expired or invalid CSRF token.";
                require __DIR__ . '/../views/login.php';
                return;
            }

            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (Auth::attempt($email, $password)) {
                Response::redirect('/profile');
            } else {
                $error = "Invalid email address or password.";
                require __DIR__ . '/../views/login.php';
                return;
            }
        }

        require __DIR__ . '/../views/login.php';
    }

    public function register(): void {
        if (Auth::check()) {
            Response::redirect('/profile');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate()) {
                $error = "Invalid CSRF token.";
                require __DIR__ . '/../views/register.php';
                return;
            }

            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $institution = trim($_POST['institution'] ?? '');

            if (empty($name) || empty($email) || strlen($password) < 6) {
                $error = "Please fill in all fields (password must be at least 6 characters).";
                require __DIR__ . '/../views/register.php';
                return;
            }

            $pdo = DB::getConnection();
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $error = "A user with this email address already exists.";
                require __DIR__ . '/../views/register.php';
                return;
            }

            $hash = password_hash($password, PASSWORD_BCRYPT);
            $apiKey = 'mia_key_' . bin2hex(random_bytes(16));

            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, api_key, institution, created_at) VALUES (?, ?, ?, 'user', ?, ?, NOW())");
            $stmt->execute([$name, $email, $hash, $apiKey, $institution]);

            Auth::attempt($email, $password);
            $_SESSION['flash_success'] = "Welcome to Manuscript Intelligence Archive! Your account is active.";
            Response::redirect('/profile');
            return;
        }

        require __DIR__ . '/../views/register.php';
    }

    public function logout(): void {
        Auth::logout();
        Response::redirect('/');
    }

    public function profile(): void {
        Auth::requireRole('user', 'researcher', 'admin');
        $user = Auth::user();

        $pdo = DB::getConnection();
        // Fetch user's annotations
        $annStmt = $pdo->prepare("SELECT a.*, p.page_no, m.id as manuscript_id, m.title as manuscript_title 
                                  FROM annotations a
                                  JOIN manuscript_pages p ON a.page_id = p.id
                                  JOIN manuscripts m ON p.manuscript_id = m.id
                                  WHERE a.user_id = ?
                                  ORDER BY a.created_at DESC");
        $annStmt->execute([$user['id']]);
        $annotations = $annStmt->fetchAll();

        require __DIR__ . '/../views/profile.php';
    }

    public function generateApiKey(): void {
        Auth::requireRole('user', 'researcher', 'admin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Csrf::validate()) {
            Auth::generateApiKey(Auth::id());
            $_SESSION['flash_success'] = "New REST API Key generated successfully!";
        }
        Response::redirect('/profile');
    }

    public function apiDocs(): void {
        require __DIR__ . '/../views/api_docs.php';
    }
}

