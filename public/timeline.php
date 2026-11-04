<?php
// public/timeline.php - Chronological Timeline (Plain Single-File PHP)
require_once __DIR__ . '/db.php';
$db = getDB();

// FETCH EVENTS DIRECTLY (इतिहास की घटनाएँ लाएं)
$stmt = $db->query("SELECT * FROM events ORDER BY sort_year ASC");
$events = $stmt->fetchAll();

$items = [];
foreach ($events as $e) {
    $yr = (int)$e['sort_year'];
    // Vis-timeline expects standard ISO string format or padded year
    $isoYear = sprintf('%04d', abs($yr));
    $dateStr = ($yr < 0 ? '-' : '') . $isoYear . '-01-01';

    $items[] = [
        'id' => (int)$e['id'],
        'content' => htmlspecialchars($e['title']),
        'start' => $dateStr,
        'title' => htmlspecialchars($e['description']),
        'className' => 'timeline-item-' . htmlspecialchars($e['category'])
    ];
}

$pageTitle = "Interactive Timeline · इतिहास समय-रेखा";
require_once __DIR__ . '/header.php';
?>

<div style="margin-bottom: 24px;">
    <div class="hero-pill">
        ⏳ Chronological Heritage · इतिहास की समय-रेखा
    </div>
    <h1 style="font-size: 30px; color: var(--text-main); margin-bottom: 6px;">Indian History Timeline (इतिहास की समय-रेखा)</h1>
    <p style="color: var(--text-muted); font-size: 14.5px;">
        Scroll and zoom to traverse over 2,000 years of Indian history from Maurya to Mughal eras.
    </p>
</div>

<!-- Timeline Container with Strict Bounds -->
<div id="timeline-container" style="height: 480px; min-height: 480px; max-height: 480px; width: 100%; border: 1px solid var(--border-light); border-radius: var(--radius-md); background: #ffffff; position: relative; overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 32px;"></div>

<!-- Selected Event Details Box -->
<div id="event-detail-box" style="display: none; background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 40px;">
    <h3 id="ev-title" style="font-size: 20px; color: var(--text-main); margin-bottom: 6px;"></h3>
    <p id="ev-desc" style="font-size: 14.5px; color: var(--text-muted); line-height: 1.6; margin: 0;"></p>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var rawItems = <?= json_encode($items, JSON_UNESCAPED_UNICODE) ?>;
    var container = document.getElementById('timeline-container');

    var dataset = new vis.DataSet(rawItems);
    var options = {
        height: '480px',
        zoomMin: 1000 * 60 * 60 * 24 * 365 * 50, // 50 years minimum
        zoomMax: 1000 * 60 * 60 * 24 * 365 * 3000,
        orientation: 'top'
    };

    var timeline = new vis.Timeline(container, dataset, options);

    timeline.on('select', function(props) {
        if (props.items.length > 0) {
            var item = dataset.get(props.items[0]);
            if (item) {
                document.getElementById('event-detail-box').style.display = 'block';
                document.getElementById('ev-title').innerText = item.content;
                document.getElementById('ev-desc').innerText = item.title;
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
