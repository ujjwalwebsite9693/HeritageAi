<?php
// app/views/timeline.php
$pageTitle = "Manuscript Events Timeline";
require __DIR__ . '/layouts/header.php';
?>

<div style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 32px; color: var(--primary);">Historical Events Timeline</h1>
            <p style="color: var(--text-muted); font-size: 15px;">
                Chronological visualization of historical events extracted from the manuscript archive. Zoom and drag horizontally across centuries; dates automatically map between Gregorian, Vikram Samvat, Śaka, and Hijri calendars.
            </p>
        </div>
        <div>
            <?php if (Auth::hasRole('researcher', 'admin')): ?>
                <a href="/events/manage" class="btn btn-primary btn-sm">+ Add / Approve Events</a>
            <?php endif; ?>
            <a href="/api/export/events-csv/0" class="btn btn-secondary btn-sm">Export Events (CSV)</a>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div style="background: var(--bg-surface); padding: 16px 20px; border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 16px; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm);">
    <form action="/timeline" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
        <div>
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 4px;">Category Filter:</label>
            <select name="category" class="form-control" style="padding: 6px 12px; font-size: 13px;" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <option value="politics" <?= ($_GET['category'] ?? '') === 'politics' ? 'selected' : '' ?>>Politics & Statecraft</option>
                <option value="war" <?= ($_GET['category'] ?? '') === 'war' ? 'selected' : '' ?>>War & Military Campaigns</option>
                <option value="religion" <?= ($_GET['category'] ?? '') === 'religion' ? 'selected' : '' ?>>Religion & Dharma</option>
                <option value="trade" <?= ($_GET['category'] ?? '') === 'trade' ? 'selected' : '' ?>>Trade, Maritime & Economy</option>
                <option value="culture" <?= ($_GET['category'] ?? '') === 'culture' ? 'selected' : '' ?>>Culture, Literature & Epigraphy</option>
            </select>
        </div>

        <div>
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 4px;">Historical Place:</label>
            <select name="place_id" class="form-control" style="padding: 6px 12px; font-size: 13px;" onchange="this.form.submit()">
                <option value="">All Historical Sites</option>
                <?php foreach ($places as $pl): ?>
                    <option value="<?= $pl['id'] ?>" <?= ((int)($_GET['place_id'] ?? 0) === (int)$pl['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pl['ancient_name']) ?> (<?= htmlspecialchars($pl['modern_name']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <div style="display: flex; gap: 8px;">
        <button id="btn-zoom-in-tl" class="btn btn-secondary btn-sm">Zoom In (+)</button>
        <button id="btn-zoom-out-tl" class="btn btn-secondary btn-sm">Zoom Out (-)</button>
        <button id="btn-fit-tl" class="btn btn-secondary btn-sm">Fit All Events</button>
    </div>
</div>

<!-- Interactive vis-timeline Container -->
<div id="timeline-container" style="height: 480px; min-height: 480px; max-height: 480px; width: 100%; border: 1px solid var(--border-light); border-radius: var(--radius-md); background: #ffffff; position: relative; overflow: hidden; box-shadow: var(--shadow-sm);"></div>

<!-- Event Details Inspector Panel -->
<div id="event-detail-card" style="margin-top: 24px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm); display: none;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
        <div>
            <span id="ev-category-badge" class="badge" style="background: var(--primary-light); color: var(--primary); margin-bottom: 6px;">Category</span>
            <h3 id="ev-title" style="font-size: 22px; color: var(--text-main);"></h3>
            <div id="ev-date" style="font-size: 14px; font-weight: 600; color: var(--accent-gold); margin-top: 4px;"></div>
        </div>
        <div id="ev-place" style="font-size: 13px; color: var(--text-muted); text-align: right;"></div>
    </div>

    <p id="ev-description" style="font-size: 15px; color: #374151; line-height: 1.6; margin-bottom: 16px;"></p>

    <div id="ev-sources-box" style="border-top: 1px solid var(--border-subtle); padding-top: 12px; font-size: 13px; color: var(--text-muted);">
        <strong>Manuscript Citation Sources:</strong>
        <div id="ev-sources-list" style="margin-top: 6px;"></div>
    </div>
</div>

<!-- Chronological Event Cards List -->
<div style="margin-top: 40px;">
    <h3 style="font-size: 20px; margin-bottom: 16px;">Chronological Event Index (<?= count($events) ?> Events)</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 16px;">
        <?php foreach ($events as $ev): ?>
            <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 18px; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span class="badge" style="background: var(--bg-surface-alt);"><?= strtoupper($ev['category']) ?></span>
                        <span style="font-size: 12px; font-weight: bold; color: var(--accent-gold);"><?= htmlspecialchars($ev['date_text']) ?></span>
                    </div>
                    <h4 style="font-size: 16px; line-height: 1.35; margin-bottom: 8px;">
                        <?= htmlspecialchars($ev['title']) ?>
                    </h4>
                    <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 12px;">
                        <?= htmlspecialchars(substr($ev['description'] ?? '', 0, 150)) ?>...
                    </p>
                </div>
                <div style="font-size: 12px; color: var(--text-faint); border-top: 1px solid var(--border-subtle); padding-top: 8px; display: flex; justify-content: space-between;">
                    <span>📍 <?= htmlspecialchars($ev['place_ancient'] ?? 'Ancient Site') ?></span>
                    <span>Confidence: <?= round($ev['confidence'] * 100) ?>%</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var rawItems = <?= json_encode($timelineItems, JSON_UNESCAPED_UNICODE) ?>;
    var container = document.getElementById('timeline-container');

    var items = new vis.DataSet(rawItems);

    var options = {
        stack: true,
        horizontalScroll: true,
        zoomKey: 'ctrlKey',
        maxHeight: '520px',
        minHeight: '440px',
        start: '-000350-01-01',
        end: '1700-01-01',
        zoomMin: 1000 * 60 * 60 * 24 * 365 * 10, // 10 years
        zoomMax: 1000 * 60 * 60 * 24 * 365 * 2500, // 2500 years
        orientation: 'both',
        tooltip: {
            followMouse: true,
            overflowMethod: 'cap'
        }
    };

    var timeline = new vis.Timeline(container, items, options);

    timeline.on('select', function (properties) {
        if (properties.items.length > 0) {
            var selectedId = properties.items[0];
            var item = items.get(selectedId);
            if (item && item.data) {
                var d = item.data;
                var panel = document.getElementById('event-detail-card');
                document.getElementById('ev-title').innerText = d.title;
                document.getElementById('ev-date').innerText = d.date_text;
                document.getElementById('ev-category-badge').innerText = d.category.toUpperCase();
                document.getElementById('ev-place').innerText = "Location: " + (d.place_ancient ? d.place_ancient + ' (' + d.place_modern + ')' : 'Regional site');
                document.getElementById('ev-description').innerText = d.description || 'No extended description recorded.';

                var srcHtml = "";
                if (d.sources && d.sources.length > 0) {
                    d.sources.forEach(function(s) {
                        srcHtml += "<div style='margin-top:4px;'>📜 <a href='/manuscript?id=" + s.manuscript_id + "'><strong>" + s.manuscript_title + "</strong></a>: <em>" + (s.quote_ref || 'Primary codex source') + "</em></div>";
                    });
                } else {
                    srcHtml = "<em>Derived from regional epigraphic chronicles.</em>";
                }
                document.getElementById('ev-sources-list').innerHTML = srcHtml;

                panel.style.display = 'block';
                panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    });

    document.getElementById('btn-zoom-in-tl').addEventListener('click', function() { timeline.zoomIn(0.4); });
    document.getElementById('btn-zoom-out-tl').addEventListener('click', function() { timeline.zoomOut(0.4); });
    document.getElementById('btn-fit-tl').addEventListener('click', function() { timeline.fit(); });
});
</script>

<?php require __DIR__ . '/layouts/footer.php'; ?>

