<?php
// public/register.php - Plain Single-File Registration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
$db = getDB();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    if (!in_array($role, ['user', 'researcher'])) {
        $role = 'user';
    }

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if email already registered
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "An account with this email already exists. Please sign in.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $db->prepare("INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            $ins->execute([$name, $email, $hash, $role]);

            // Auto-login
            $userId = $db->lastInsertId();
            $_SESSION['user'] = [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => $role
            ];
            $_SESSION['flash_success'] = "Account created successfully! Welcome, " . htmlspecialchars($name) . ".";
            header("Location: " . ($role === 'researcher' ? '/admin.php' : '/index.php'));
            exit;
        }
    }
}

$pageTitle = "Register — Bharat Heritage Archive";
require_once __DIR__ . '/header.php';
?>

<div style="max-width: 440px; margin: 40px auto; background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: 36px; box-shadow: var(--shadow-md);">
    <div style="text-align: center; margin-bottom: 24px;">
        <div class="brand-emblem" style="margin: 0 auto 12px auto;">🏛️</div>
        <h1 style="font-size: 24px; color: var(--text-main); margin-bottom: 4px;">Join Bharat Heritage</h1>
        <p style="font-size: 13.5px; color: var(--text-muted);">Explore ancient codices, emperors, and monuments</p>
    </div>

    <?php if ($error): ?>
        <div class="flash-alert flash-error" style="margin-bottom: 18px;">
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form action="/register.php" method="POST">
        <div class="form-group" style="margin-bottom: 14px;">
            <label class="form-label">Full Name *</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Dr. Sunita Verma" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>

        <div class="form-group" style="margin-bottom: 14px;">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-control" placeholder="sunita@university.edu" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-group" style="margin-bottom: 14px;">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control" placeholder="At least 6 characters" required>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label">Account Type</label>
            <select name="role" class="form-control">
                <option value="user">General Scholar / Enthusiast</option>
                <option value="researcher">Historical Researcher (Can add/edit monuments)</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px;">Create Account</button>
    </form>

    <div style="margin-top: 20px; text-align: center; font-size: 13.5px; color: var(--text-muted);">
        Already have an account? <a href="/login.php" style="font-weight: 600;">Sign in here</a>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
