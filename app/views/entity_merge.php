<?php
// app/views/entity_merge.php
$pageTitle = "Entity Resolution & Merge";
require __DIR__ . '/layouts/header.php';
?>

<div style="margin-bottom: 24px;">
    <h1 style="font-size: 30px; color: var(--primary);">Entity Resolution & Deduplication Workspace</h1>
    <p style="color: var(--text-muted); font-size: 14px;">
        Identify variant spellings, phonetic equivalents (Soundex), and cross-script aliases across multilingual manuscripts. Merging variants unifies knowledge graphs and boosts link discovery precision.
    </p>
</div>

<!-- Suggested Duplicate Pairs (Algorithmic Fuzzy Matching) -->
<div style="margin-bottom: 36px;">
    <h3 style="font-size: 20px; margin-bottom: 16px;">
        🤖 Algorithmic Duplicate Candidates (<?= count($duplicates) ?> Suggested Pairs)
    </h3>

    <?php if (empty($duplicates)): ?>
        <div style="background: var(--bg-surface); padding: 32px; border-radius: 4px; border: 1px solid var(--border-subtle); text-align: center;">
            <p style="color: var(--text-muted);">No unresolved duplicate entity pairs detected above the 65% similarity threshold.</p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($duplicates as $dup): ?>
                <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 20px; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                        <div>
                            <span class="badge badge-score">Similarity: <?= $dup['similarity_score'] ?>%</span>
                            <span class="badge" style="background: var(--bg-surface-alt); margin-left: 6px;">Type: <?= strtoupper($dup['type']) ?></span>
                            <span style="font-size: 12px; color: var(--text-faint); margin-left: 10px;">
                                <?= htmlspecialchars($dup['reason']) ?>
                            </span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: var(--bg-surface-alt); padding: 16px; border-radius: 4px; margin-bottom: 16px;">
                        <div>
                            <span style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: var(--text-faint);">Entity A (#<?= $dup['entity_a']['id'] ?>)</span>
                            <h4 style="font-size: 17px; margin: 4px 0; color: var(--text-main);"><?= htmlspecialchars($dup['entity_a']['name']) ?></h4>
                            <div style="font-size: 12px; color: var(--text-muted);">Normalized: <code><?= htmlspecialchars($dup['entity_a']['normalized_name']) ?></code></div>
                        </div>

                        <div>
                            <span style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: var(--text-faint);">Entity B (#<?= $dup['entity_b']['id'] ?>)</span>
                            <h4 style="font-size: 17px; margin: 4px 0; color: var(--text-main);"><?= htmlspecialchars($dup['entity_b']['name']) ?></h4>
                            <div style="font-size: 12px; color: var(--text-muted);">Normalized: <code><?= htmlspecialchars($dup['entity_b']['normalized_name']) ?></code></div>
                        </div>
                    </div>

                    <!-- Merge Execution Forms -->
                    <div style="display: flex; gap: 12px; justify-content: flex-end;">
                        <form action="/entities/merge" method="POST">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="keep_id" value="<?= $dup['entity_a']['id'] ?>">
                            <input type="hidden" name="merge_id" value="<?= $dup['entity_b']['id'] ?>">
                            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Merge Entity B into Entity A?')">
                                Merge B into A ("<?= htmlspecialchars($dup['entity_a']['name']) ?>")
                            </button>
                        </form>

                        <form action="/entities/merge" method="POST">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="keep_id" value="<?= $dup['entity_b']['id'] ?>">
                            <input type="hidden" name="merge_id" value="<?= $dup['entity_a']['id'] ?>">
                            <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Merge Entity A into Entity B?')">
                                Merge A into B ("<?= htmlspecialchars($dup['entity_b']['name']) ?>")
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Manual Entity Merge Tool -->
<div class="form-card" style="margin: 0;">
    <h3 style="font-size: 18px; margin-bottom: 14px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 8px;">
        Manual Entity Pair Resolution
    </h3>
    <form action="/entities/merge" method="POST">
        <?= Csrf::field() ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Canonical Entity to Keep *</label>
                <select name="keep_id" class="form-control" required>
                    <option value="">Select Canonical Entity</option>
                    <?php foreach ($allEntities as $ent): ?>
                        <option value="<?= $ent['id'] ?>"><?= htmlspecialchars($ent['name']) ?> (<?= ucfirst($ent['type']) ?>, #<?= $ent['id'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Duplicate Entity to Merge & Fold *</label>
                <select name="merge_id" class="form-control" required>
                    <option value="">Select Duplicate Entity</option>
                    <?php foreach ($allEntities as $ent): ?>
                        <option value="<?= $ent['id'] ?>"><?= htmlspecialchars($ent['name']) ?> (<?= ucfirst($ent['type']) ?>, #<?= $ent['id'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" onclick="return confirm('Execute manual entity merge?')">
            Execute Entity Merge & Repoint Mentions
        </button>
    </form>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

