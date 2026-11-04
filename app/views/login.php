<?php
// app/views/login.php
$pageTitle = "Sign In";
require __DIR__ . '/layouts/header.php';
?>

<div style="max-width: 440px; margin: 40px auto;">
    <div class="form-card" style="padding: 36px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <div class="brand-emblem" style="margin: 0 auto 12px auto;">अ</div>
            <h1 style="font-size: 24px; color: var(--primary);">Researcher Sign In</h1>
            <p style="font-size: 13px; color: var(--text-muted);">Access the Manuscript Intelligence Archive</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="flash-alert flash-error" style="font-size: 13px; margin-bottom: 16px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="admin@archive.org" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" value="admin123" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
                Sign In
            </button>
        </form>

        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-subtle); font-size: 13px; text-align: center; color: var(--text-muted);">
            Demo Accounts Available:<br>
            <span style="font-size: 12px; color: var(--text-main);">
                Admin: <code>admin@archive.org</code> (pass: <code>admin123</code>)<br>
                Researcher: <code>dr.anand@nalanda.edu</code> (pass: <code>researcher123</code>)
            </span>
            <div style="margin-top: 12px;">
                Need an account? <a href="/register" style="font-weight: 600;">Register here</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

