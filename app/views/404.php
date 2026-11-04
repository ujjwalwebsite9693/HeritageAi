<?php
// app/views/404.php
$pageTitle = "Document Not Found";
require __DIR__ . '/layouts/header.php';
?>

<div style="text-align: center; padding: 64px 20px;">
    <div style="font-size: 64px; color: var(--primary); font-family: var(--font-serif); margin-bottom: 12px;">४०४</div>
    <h1 style="font-size: 28px; margin-bottom: 12px;">Codex Not Found</h1>
    <p style="color: var(--text-muted); font-size: 15px; max-width: 500px; margin: 0 auto 24px auto;">
        The requested manuscript, page, or historical record could not be found in the archive repository.
    </p>
    <a href="/search" class="btn btn-primary">Browse Digital Archive</a>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

