<?php
// public/manuscript.php - Manuscript Codex Reader (Plain Single-File PHP)
require_once __DIR__ . '/db.php';
$db = getDB();

$id = (int)($_GET['id'] ?? 1);

// 1. FETCH MANUSCRIPT DIRECTLY
$stmt = $db->prepare("SELECT m.*, l.name as language_name, p.ancient_name as place_ancient
                      FROM manuscripts m
                      JOIN languages l ON m.language_id = l.id
                      LEFT JOIN places p ON m.origin_place_id = p.id
                      WHERE m.id = ?");
$stmt->execute([$id]);
$manuscript = $stmt->fetch();

if (!$manuscript) {
    header("Location: /search.php");
    exit;
}

// 2. FETCH PAGES
$pageStmt = $db->prepare("SELECT * FROM manuscript_pages WHERE manuscript_id = ? ORDER BY page_no ASC");
$pageStmt->execute([$id]);
$pages = $pageStmt->fetchAll();

// 3. FETCH TRANSLATIONS FOR PAGE 1
$firstPageId = !empty($pages) ? $pages[0]['id'] : 0;
$transStmt = $db->prepare("SELECT * FROM translations WHERE page_id = ?");
$transStmt->execute([$firstPageId]);
$translations = $transStmt->fetchAll();

$transByLang = [];
foreach ($translations as $t) {
    $transByLang[$t['target_language']] = $t;
}

$pageTitle = $manuscript['title'] . " — Ancient Manuscript Codex";
require_once __DIR__ . '/header.php';
?>

<!-- Title & Overview -->
<div style="margin-bottom: 24px;">
    <div style="display: flex; gap: 8px; margin-bottom: 8px;">
        <span class="badge" style="background: var(--accent-gold-light); color: var(--accent-gold);"><?= htmlspecialchars($manuscript['language_name']) ?></span>
        <span class="badge badge-score"><?= $manuscript['era_start'] ? $manuscript['era_start'] . ' CE' : '' ?></span>
    </div>
    <h1 style="font-size: 28px; color: var(--text-main); margin-bottom: 6px;"><?= htmlspecialchars($manuscript['title']) ?></h1>
    <div style="color: var(--text-muted); font-size: 14.5px;">
        Author: <strong><?= htmlspecialchars($manuscript['author']) ?></strong> · Origin: <strong><?= htmlspecialchars($manuscript['place_ancient'] ?? 'India') ?></strong>
    </div>
</div>

<!-- Reader 2-Column Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; margin-bottom: 40px;">
    <!-- Left Column: High-Res Folio Scan Viewer -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm);">
        <div style="background: var(--bg-surface-alt); padding: 12px 18px; border-bottom: 1px solid var(--border-light); font-weight: 700; font-size: 13.5px; color: var(--text-main); display: flex; justify-content: space-between;">
            <span>📜 Scanned Folio 1 (High Resolution)</span>
            <span style="font-size: 11px; color: var(--text-faint);">Deep Zoom Active</span>
        </div>
        <div id="openseadragon-viewer" style="width: 100%; height: 500px; background: #1a1a1a;"></div>
    </div>

    <!-- Right Column: Simple Translations Tabs -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid var(--border-light); padding-bottom: 12px;">
            <button class="btn btn-primary btn-sm trans-tab active" onclick="switchTrans('hi', this)">🇮🇳 हिंदी अनुवाद (Hindi)</button>
            <button class="btn btn-secondary btn-sm trans-tab" onclick="switchTrans('en', this)">🇬🇧 English Translation</button>
            <button class="btn btn-secondary btn-sm trans-tab" onclick="switchTrans('orig', this)">Original Script</button>
        </div>

        <!-- Hindi Tab -->
        <div id="tab-hi" class="trans-content">
            <h3 style="font-size: 16px; color: var(--accent-gold); margin-bottom: 10px;">हिंदी अनुवाद:</h3>
            <div style="font-size: 15.5px; line-height: 1.8; color: var(--text-main); font-family: var(--font-indic);">
                <?= !empty($transByLang['hi']['translated_text']) ? nl2br(htmlspecialchars($transByLang['hi']['translated_text'])) : nl2br(htmlspecialchars($manuscript['summary'])) ?>
            </div>
        </div>

        <!-- English Tab -->
        <div id="tab-en" class="trans-content" style="display: none;">
            <h3 style="font-size: 16px; color: var(--accent-gold); margin-bottom: 10px;">English Translation:</h3>
            <div style="font-size: 15px; line-height: 1.8; color: var(--text-main);">
                <?= !empty($transByLang['en']['translated_text']) ? nl2br(htmlspecialchars($transByLang['en']['translated_text'])) : nl2br(htmlspecialchars($manuscript['summary'])) ?>
            </div>
        </div>

        <!-- Original Script Tab -->
        <div id="tab-orig" class="trans-content" style="display: none;">
            <h3 style="font-size: 16px; color: var(--accent-gold); margin-bottom: 10px;">Original Text & Paleography:</h3>
            <div style="font-size: 15px; line-height: 1.8; color: var(--text-body); background: var(--bg-surface-alt); padding: 14px; border-radius: 6px; font-family: monospace;">
                <?= !empty($pages[0]['transcription']) ? nl2br(htmlspecialchars($pages[0]['transcription'])) : "Primary classical epigraphic text preserved in the folio." ?>
            </div>
        </div>

        <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12.5px; color: var(--text-faint);">Folio 1 of <?= count($pages) ?></span>
            <a href="/search.php" class="btn btn-secondary btn-sm">← Back to Manuscripts</a>
        </div>
    </div>
</div>

<script>
function switchTrans(lang, btn) {
    document.querySelectorAll('.trans-tab').forEach(b => {
        b.classList.remove('btn-primary', 'active');
        b.classList.add('btn-secondary');
    });
    btn.classList.add('btn-primary', 'active');
    btn.classList.remove('btn-secondary');

    document.querySelectorAll('.trans-content').forEach(c => c.style.display = 'none');
    document.getElementById('tab-' + lang).style.display = 'block';
}

document.addEventListener("DOMContentLoaded", function() {
    var viewer = OpenSeadragon({
        id: "openseadragon-viewer",
        prefixUrl: "https://cdn.jsdelivr.net/npm/openseadragon@4.1.1/build/openseadragon/images/",
        tileSources: {
            type: 'image',
            url: '<?= !empty($pages[0]['image_path']) ? htmlspecialchars($pages[0]['image_path']) : "/assets/img/manuscript-ashoka.jpg" ?>'
        },
        showNavigator: true,
        navigatorPosition: "BOTTOM_RIGHT",
        showRotationControl: true
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
