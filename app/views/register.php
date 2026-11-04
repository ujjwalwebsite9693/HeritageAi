<?php
// app/views/register.php
$pageTitle = "Register Researcher Account";
require __DIR__ . '/layouts/header.php';
?>

<div style="max-width: 480px; margin: 40px auto;">
    <div class="form-card" style="padding: 36px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <div class="brand-emblem" style="margin: 0 auto 12px auto;">अ</div>
            <h1 style="font-size: 24px; color: var(--primary);">Join the Archive Network</h1>
            <p style="font-size: 13px; color: var(--text-muted);">Create your researcher account</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="flash-alert flash-error" style="font-size: 13px; margin-bottom: 16px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="/register" method="POST">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Prof. Arvind Venkat" required>
            </div>

            <div class="form-group">
                <label class="form-label">Academic / Institutional Email *</label>
                <input type="email" name="email" class="form-control" placeholder="e.g. arvind@university.edu" required>
            </div>

            <div class="form-group">
                <label class="form-label">Affiliated Institution / University</label>
                <input type="text" name="institution" class="form-control" placeholder="e.g. Centre for Asian Manuscripts">
            </div>

            <div class="form-group">
                <label class="form-label">Password * (Min. 6 characters)</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
                Create Researcher Account
            </button>
        </form>

        <div style="margin-top: 20px; text-align: center; font-size: 13px; color: var(--text-muted);">
            Already have an account? <a href="/login" style="font-weight: 600;">Sign in here</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

