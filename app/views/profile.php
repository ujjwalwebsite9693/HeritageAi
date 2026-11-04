<?php
// app/views/profile.php
$pageTitle = "Researcher Profile";
require __DIR__ . '/layouts/header.php';
?>

<div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 32px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-verified"><?= strtoupper($user['role']) ?> ACCOUNT</span>
                <?php if ($user['institution']): ?>
                    <span class="badge" style="background: var(--bg-surface-alt);"><?= htmlspecialchars($user['institution']) ?></span>
                <?php endif; ?>
            </div>
            <h1 style="font-size: 28px; color: var(--primary);"><?= htmlspecialchars($user['name']) ?></h1>
            <p style="color: var(--text-muted); font-size: 14px;"><?= htmlspecialchars($user['email']) ?></p>
        </div>

        <div style="display: flex; gap: 8px;">
            <?php if (Auth::hasRole('researcher', 'admin')): ?>
                <a href="/admin" class="btn btn-primary btn-sm">⚙️ Open Admin Control Center</a>
            <?php endif; ?>
            <a href="/logout" class="btn btn-outline btn-sm">Sign Out</a>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Public REST API Key Section -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm);">
        <h3 style="font-size: 18px; margin-bottom: 12px; color: var(--primary);">Developer REST API Access</h3>
        <p style="font-size: 13.5px; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px;">
            Use your personal API Key to query read-only endpoints, fetch GeoJSON coordinates, or integrate digital archive metadata into external applications.
        </p>

        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Active API Key</label>
            <div style="display: flex; gap: 8px;">
                <input type="text" id="api-key-field" class="form-control" value="<?= htmlspecialchars($user['api_key'] ?? 'No API key generated yet') ?>" readonly style="font-family: monospace; background: var(--bg-surface-alt);">
                <button onclick="navigator.clipboard.writeText(document.getElementById('api-key-field').value); alert('API Key copied to clipboard!');" class="btn btn-secondary btn-sm">
                    Copy
                </button>
            </div>
        </div>

        <form action="/profile/api-key" method="POST">
            <?= Csrf::field() ?>
            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Regenerate API Key? Any existing applications using the old key will need to be updated.')">
                🔄 Regenerate New API Key
            </button>
            <a href="/api-docs" class="btn btn-secondary btn-sm" style="margin-left: 8px;">View API Documentation →</a>
        </form>
    </div>

    <!-- My Scholarly Annotations -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm);">
        <h3 style="font-size: 18px; margin-bottom: 14px; color: var(--primary);">
            My Scholarly Annotations (<?= count($annotations) ?>)
        </h3>

        <?php if (empty($annotations)): ?>
            <p style="color: var(--text-faint); font-size: 13.5px;">You have not published any annotations yet. Annotations can be added directly while viewing any manuscript folio.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 12px; max-height: 380px; overflow-y: auto;">
                <?php foreach ($annotations as $ann): ?>
                    <div style="background: var(--bg-surface-alt); padding: 12px; border-radius: 4px; border: 1px solid var(--border-subtle); font-size: 13px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <a href="/manuscript?id=<?= $ann['manuscript_id'] ?>&page=<?= $ann['page_no'] ?>" style="font-weight: 600;">
                                <?= htmlspecialchars($ann['manuscript_title']) ?> (Folio <?= $ann['page_no'] ?>)
                            </a>
                            <span class="badge" style="background:#fff;"><?= strtoupper($ann['type']) ?></span>
                        </div>
                        <p style="color: #374151; margin-bottom: 4px;">
                            <?= htmlspecialchars(substr($ann['note'], 0, 110)) ?>...
                        </p>
                        <div style="font-size: 11px; color: var(--text-faint);">
                            Posted on <?= date('M j, Y H:i', strtotime($ann['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

