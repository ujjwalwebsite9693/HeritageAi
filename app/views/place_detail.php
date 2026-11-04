<?php
// app/views/place_detail.php
$pageTitle = $place['ancient_name'] . " (" . $place['modern_name'] . ")";
require __DIR__ . '/layouts/header.php';
?>

<div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 32px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <span class="badge" style="background: var(--accent-navy-light); color: var(--accent-navy); margin-bottom: 8px;">Historical Epigraphic Site</span>
            <h1 style="font-size: 34px; color: var(--primary); margin-bottom: 4px;"><?= htmlspecialchars($place['ancient_name']) ?></h1>
            <div style="font-size: 16px; font-weight: 600; color: var(--text-muted); margin-bottom: 12px;">
                Modern Equivalent: <?= htmlspecialchars($place['modern_name']) ?> · Region: <?= htmlspecialchars($place['region']) ?> · <?= htmlspecialchars($place['country']) ?>
            </div>
            <p style="font-size: 15px; color: #4a5568; line-height: 1.6; max-width: 850px;">
                <?= htmlspecialchars($place['notes']) ?>
            </p>
        </div>
        <div>
            <a href="/map" class="btn btn-secondary btn-sm">← Back to Map</a>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Associated Manuscripts -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm);">
        <h3 style="font-size: 18px; margin-bottom: 16px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 8px;">
            📜 Manuscripts Originating at <?= htmlspecialchars($place['ancient_name']) ?>
        </h3>

        <?php if (empty($place['manuscripts'])): ?>
            <p style="color: var(--text-faint); font-size: 14px;">No surviving primary codices assigned directly to this provenance center.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <?php foreach ($place['manuscripts'] as $m): ?>
                    <div style="background: var(--bg-surface-alt); padding: 14px; border-radius: 4px; border: 1px solid var(--border-subtle);">
                        <h4 style="font-size: 15px; margin-bottom: 4px;">
                            <a href="/manuscript?id=<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></a>
                        </h4>
                        <div style="font-size: 12px; color: var(--text-muted);">
                            Author: <?= htmlspecialchars($m['author']) ?> · Era: <?= Calendar::formatYear((int)$m['era_start']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Chronological Events -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm);">
        <h3 style="font-size: 18px; margin-bottom: 16px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 8px;">
            ⏳ Historical Events at <?= htmlspecialchars($place['ancient_name']) ?>
        </h3>

        <?php if (empty($place['events'])): ?>
            <p style="color: var(--text-faint); font-size: 14px;">No chronological milestones cataloged for this location.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <?php foreach ($place['events'] as $e): ?>
                    <div style="background: var(--bg-surface-alt); padding: 14px; border-radius: 4px; border: 1px solid var(--border-subtle);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <h4 style="font-size: 14px; color: var(--text-main);"><?= htmlspecialchars($e['title']) ?></h4>
                            <span style="font-size: 11px; font-weight: bold; color: var(--accent-gold);"><?= htmlspecialchars($e['date_text']) ?></span>
                        </div>
                        <p style="font-size: 12.5px; color: var(--text-muted); line-height: 1.4;">
                            <?= htmlspecialchars($e['description']) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

