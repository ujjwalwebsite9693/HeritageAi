<?php
// public/login.php - Simple Direct Sign In (Plain Single-File PHP)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
$db = getDB();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_cached'] = $user;
            $_SESSION['flash_success'] = "Welcome back, " . htmlspecialchars($user['name']) . "!";
            header("Location: " . (in_array($user['role'], ['researcher', 'admin']) ? '/admin.php' : '/index.php'));
            exit;
        } else {
            $error = "Invalid email or password. Please try again.";
        }
    } else {
        $error = "Please enter both email and password.";
    }
}

$pageTitle = "Sign In — Bharat Heritage Archive";
require_once __DIR__ . '/header.php';
?>

<div style="max-width: 420px; margin: 40px auto; background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: 36px; box-shadow: var(--shadow-md);">
    <div style="text-align: center; margin-bottom: 24px;">
        <div class="brand-emblem" style="margin: 0 auto 12px auto;">🏛️</div>
        <h1 style="font-size: 24px; color: var(--text-main); margin-bottom: 4px;">Sign In to Archive</h1>
        <p style="font-size: 13.5px; color: var(--text-muted);">Access researcher workflows & monument administration</p>
    </div>

    <?php if ($error): ?>
        <div class="flash-alert flash-error" style="margin-bottom: 18px;">
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form action="/login.php" method="POST">
        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="admin@archive.org" required>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px;">Sign In →</button>
    </form>

    <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border-light); font-size: 12.5px; color: var(--text-muted);">
        <strong>Demo Accounts:</strong><br>
        • Admin: <code>admin@archive.org</code> / <code>admin123</code><br>
        • Researcher: <code>dr.anand@nalanda.edu</code> / <code>researcher123</code>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
