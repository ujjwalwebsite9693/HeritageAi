<?php
// app/views/admin_queue.php
$pageTitle = "Background Queue Worker";
require __DIR__ . '/layouts/header.php';
?>

<div style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 30px; color: var(--primary);">Background Jobs Queue & Worker</h1>
            <p style="color: var(--text-muted); font-size: 14px;">
                Asynchronous task pipeline for resource-heavy operations: Palaeographic OCR, AI Translation, and Link Discovery Scans.
            </p>
        </div>
        <div>
            <form action="/admin/queue" method="POST">
                <?= Csrf::field() ?>
                <input type="hidden" name="action" value="run_worker">
                <button type="submit" class="btn btn-primary btn-sm">
                    ⚡ Execute Pending Queue Jobs Now
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Queue Stats Ribbon -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-number" style="color: var(--status-draft);"><?= $stats['pending'] ?></div>
        <div class="stat-label">Pending In Queue</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: var(--status-verified);"><?= $stats['completed'] ?></div>
        <div class="stat-label">Completed Jobs</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: var(--primary);"><?= $stats['failed'] ?></div>
        <div class="stat-label">Failed / Retried</div>
    </div>
</div>

<!-- Jobs Table -->
<div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm);">
    <h3 style="font-size: 18px; margin-bottom: 16px;">Recent Job Queue Logs</h3>

    <?php if (empty($jobs)): ?>
        <p style="color: var(--text-faint); text-align: center; padding: 20px;">No jobs recorded in queue.</p>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; font-size: 13.5px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-subtle); text-align: left; color: var(--text-muted); font-size: 12px; text-transform: uppercase;">
                    <th style="padding: 10px;">ID</th>
                    <th style="padding: 10px;">Type</th>
                    <th style="padding: 10px;">Payload</th>
                    <th style="padding: 10px;">Status</th>
                    <th style="padding: 10px;">Attempts</th>
                    <th style="padding: 10px;">Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $j): ?>
                    <tr style="border-bottom: 1px solid var(--border-subtle);">
                        <td style="padding: 10px;">#<?= $j['id'] ?></td>
                        <td style="padding: 10px; font-weight: 600;"><code><?= htmlspecialchars($j['type']) ?></code></td>
                        <td style="padding: 10px; font-family: monospace; font-size: 12px; color: var(--text-muted);">
                            <?= htmlspecialchars(substr($j['payload'], 0, 60)) ?>
                        </td>
                        <td style="padding: 10px;">
                            <span class="badge <?= $j['status'] === 'completed' ? 'badge-verified' : ($j['status'] === 'pending' ? 'badge-draft' : 'badge-corrected') ?>">
                                <?= strtoupper($j['status']) ?>
                            </span>
                        </td>
                        <td style="padding: 10px;"><?= $j['attempts'] ?></td>
                        <td style="padding: 10px; font-size: 12px; color: var(--text-faint);">
                            <?= date('M j, H:i', strtotime($j['created_at'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div style="margin-top: 24px; background: var(--bg-surface-alt); padding: 18px; border-radius: 4px; border: 1px solid var(--border-subtle); font-size: 13px; color: var(--text-muted);">
    <strong>CLI Cron Usage:</strong> In production, add this command to crontab to run every 5 minutes:<br>
    <code>* * * * * php /path/to/manuscript-archive/cron/worker.php > /dev/null 2>&1</code>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

