<?php
// app/views/admin_settings.php
$pageTitle = "System Settings & Health";
require __DIR__ . '/layouts/header.php';
?>

<div style="margin-bottom: 24px;">
    <h1 style="font-size: 30px; color: var(--primary);">System Architecture & Server Environment</h1>
    <p style="color: var(--text-muted); font-size: 14px;">
        Overview of PHP runtime environment, MySQL database configuration, and storage subsystems.
    </p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm);">
        <h3 style="font-size: 18px; margin-bottom: 16px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 8px;">
            Server Environment
        </h3>
        <table style="width: 100%; font-size: 13.5px; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-subtle);">
                <td style="padding: 8px 0; color: var(--text-muted);">PHP Engine Version</td>
                <td style="padding: 8px 0; font-weight: bold;"><?= htmlspecialchars($info['php_version']) ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-subtle);">
                <td style="padding: 8px 0; color: var(--text-muted);">MySQL Engine Version</td>
                <td style="padding: 8px 0; font-weight: bold;"><?= htmlspecialchars($info['mysql_version']) ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-subtle);">
                <td style="padding: 8px 0; color: var(--text-muted);">Upload Max Filesize</td>
                <td style="padding: 8px 0; font-weight: bold;"><?= htmlspecialchars($info['upload_max_filesize']) ?></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-subtle);">
                <td style="padding: 8px 0; color: var(--text-muted);">POST Max Size</td>
                <td style="padding: 8px 0; font-weight: bold;"><?= htmlspecialchars($info['post_max_size']) ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: var(--text-muted);">Memory Limit</td>
                <td style="padding: 8px 0; font-weight: bold;"><?= htmlspecialchars($info['memory_limit']) ?></td>
            </tr>
        </table>
    </div>

    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm);">
        <h3 style="font-size: 18px; margin-bottom: 16px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 8px;">
            Required PHP Extensions
        </h3>
        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13.5px;">
            <?php foreach ($info['extensions'] as $ext => $loaded): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 12px; background: var(--bg-surface-alt); border-radius: 4px;">
                    <code><?= htmlspecialchars($ext) ?></code>
                    <span class="badge <?= $loaded ? 'badge-verified' : 'badge-draft' ?>">
                        <?= $loaded ? 'ACTIVE' : 'MISSING' ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

