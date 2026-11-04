<?php
// app/views/events_manage.php
$pageTitle = "Manage Historical Events";
require __DIR__ . '/layouts/header.php';
?>

<div style="margin-bottom: 24px;">
    <h1 style="font-size: 28px; color: var(--primary);">Event Catalog & Verification Editor</h1>
    <p style="color: var(--text-muted); font-size: 14px;">
        Add new historical milestones extracted from manuscripts or verify candidate events.
    </p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 32px; align-items: start;">
    <!-- Add Event Form -->
    <div class="form-card" style="margin: 0;">
        <h3 style="font-size: 18px; margin-bottom: 16px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 8px;">
            + Add Historical Event
        </h3>

        <form action="/events/manage" method="POST">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label class="form-label">Event Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Erection of Ashokan Pillar at Prayaga" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Date String *</label>
                    <input type="text" name="date_text" class="form-control" placeholder="e.g. c. 250 BCE or 1582 CE" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Calendar Type</label>
                    <select name="calendar_type" class="form-control">
                        <option value="Gregorian">Gregorian (CE / BCE)</option>
                        <option value="Vikram Samvat">Vikram Samvat (VS)</option>
                        <option value="Shaka">Śaka Era</option>
                        <option value="Hijri">Hijri (AH)</option>
                        <option value="Regnal">Regnal Year</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Numeric Year Value *</label>
                    <input type="number" name="year_value" class="form-control" placeholder="e.g. -250 for 250 BCE" required>
                    <small style="color: var(--text-faint); font-size: 11px;">Negative integer for BCE</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control">
                        <option value="politics">Politics</option>
                        <option value="war">War</option>
                        <option value="religion">Religion</option>
                        <option value="trade">Trade</option>
                        <option value="culture">Culture</option>
                        <option value="science">Science</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Historical Place / Location</label>
                <select name="place_id" class="form-control">
                    <option value="">Select Ancient Center</option>
                    <?php foreach ($places as $pl): ?>
                        <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['ancient_name']) ?> (<?= htmlspecialchars($pl['modern_name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Description & Historical Context</label>
                <textarea name="description" class="form-control" placeholder="Detailed scholarly explanation of the event..." style="min-height: 90px;"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Source Manuscript</label>
                    <select name="manuscript_id" class="form-control">
                        <option value="">Link Manuscript Codex</option>
                        <?php foreach ($manuscripts as $man): ?>
                            <option value="<?= $man['id'] ?>"><?= htmlspecialchars($man['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Quote / Inscription Citation</label>
                    <input type="text" name="quote_ref" class="form-control" placeholder="e.g. Line 4, Rock Inscription Edict XIII">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Create & Catalog Event</button>
        </form>
    </div>

    <!-- Existing Events List -->
    <div>
        <h3 style="font-size: 18px; margin-bottom: 16px;">Current Cataloged Events</h3>
        <div style="display: flex; flex-direction: column; gap: 12px; max-height: 800px; overflow-y: auto;">
            <?php foreach ($events as $e): ?>
                <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 16px; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <span class="badge" style="background: var(--bg-surface-alt); margin-bottom: 4px;"><?= strtoupper($e['category']) ?></span>
                            <h4 style="font-size: 15px; color: var(--text-main);"><?= htmlspecialchars($e['title']) ?></h4>
                        </div>
                        <span style="font-size: 12px; font-weight: bold; color: var(--accent-gold);"><?= htmlspecialchars($e['date_text']) ?></span>
                    </div>
                    <p style="font-size: 13px; color: var(--text-muted); margin: 6px 0;">
                        <?= htmlspecialchars(substr($e['description'] ?? '', 0, 110)) ?>...
                    </p>
                    <div style="font-size: 11px; color: var(--text-faint); display: flex; justify-content: space-between;">
                        <span>Site: <?= htmlspecialchars($e['place_ancient'] ?? 'Regional') ?></span>
                        <span>Normalized: <?= Calendar::formatYear((int)$e['sort_year']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

