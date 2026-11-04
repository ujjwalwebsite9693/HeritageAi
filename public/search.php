<?php
// public/search.php - Search & Browse Rare Manuscripts (Plain Single-File PHP)
require_once __DIR__ . '/db.php';
$db = getDB();

$q = trim($_GET['q'] ?? '');
$langId = !empty($_GET['lang']) ? (int)$_GET['lang'] : null;

// BUILD DIRECT SQL QUERY (सीधे SQL क्वेरी चलाएं)
$sql = "SELECT m.*, l.name as language_name, p.ancient_name as place_ancient
        FROM manuscripts m
        JOIN languages l ON m.language_id = l.id
        LEFT JOIN places p ON m.origin_place_id = p.id
        WHERE m.status = 'published'";
$params = [];

if (!empty($q)) {
    $sql .= " AND (m.title LIKE ? OR m.summary LIKE ? OR m.author LIKE ?)";
    $term = "%$q%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($langId) {
    $sql .= " AND m.language_id = ?";
    $params[] = $langId;
}

$sql .= " ORDER BY m.era_start ASC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$manuscripts = $stmt->fetchAll();

// Fetch languages for dropdown
$languages = $db->query("SELECT id, name FROM languages ORDER BY name ASC")->fetchAll();

$pageTitle = "Browse Rare Manuscripts (प्राचीन ग्रन्थ)";
require_once __DIR__ . '/header.php';
?>

<div style="margin-bottom: 24px;">
    <div class="hero-pill">
        📜 Ancient Manuscripts & Texts · प्राचीन पाण्डुलिपियां
    </div>
    <h1 style="font-size: 30px; color: var(--text-main); margin-bottom: 6px;">Browse Ancient Books & Manuscripts (प्राचीन ग्रन्थ)</h1>
    <p style="color: var(--text-muted); font-size: 14.5px;">
        आईन-ए-अकबरी, कौटिल्य का अर्थशास्त्र, और सम्राट अशोक के शिलालेख — मूल पाण्डुलिपियों के पन्ने देखें और आसान हिंदी व अंग्रेजी अनुवाद पढ़ें।
    </p>
</div>

<div style="display: grid; grid-template-columns: 260px 1fr; gap: 24px; align-items: start;">
    <!-- Filter Sidebar -->
    <aside style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 20px; box-shadow: var(--shadow-xs);">
        <form action="/search.php" method="GET">
            <h3 style="font-size: 16px; margin-bottom: 14px;">Search & Filters (खोजें)</h3>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label">Search Keyword</label>
                <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($q) ?>" placeholder="e.g. Akbar, Ashoka, Arthashastra...">
            </div>

            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label">Language (भाषा)</label>
                <select name="lang" class="form-control">
                    <option value="">All Languages (सभी भाषाएँ)</option>
                    <?php foreach ($languages as $l): ?>
                        <option value="<?= $l['id'] ?>" <?= $langId == $l['id'] ? 'selected' : '' ?>><?= htmlspecialchars($l['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 8px;">Filter Results</button>
            <a href="/search.php" class="btn btn-secondary" style="width: 100%;">Reset (रीसेट)</a>
        </form>
    </aside>

    <!-- Results Grid -->
    <main>
        <div style="font-size: 14px; color: var(--text-muted); margin-bottom: 16px;">
            Found <strong><?= count($manuscripts) ?></strong> manuscript(s)
            <?php if ($q): ?> for "<em><?= htmlspecialchars($q) ?></em>"<?php endif; ?>
        </div>

        <?php if (empty($manuscripts)): ?>
            <div style="background: var(--bg-surface); padding: 40px; text-align: center; border-radius: var(--radius-md); border: 1px solid var(--border-light);">
                <p style="color: var(--text-muted); font-size: 15px;">No documents match your query.</p>
                <a href="/search.php" class="btn btn-secondary btn-sm" style="margin-top: 10px;">Reset Search</a>
            </div>
        <?php else: ?>
            <div class="clean-grid">
                <?php foreach ($manuscripts as $m): ?>
                    <div class="clean-manuscript-card">
                        <div class="clean-card-img" style="height: 180px; background-image: url('<?= htmlspecialchars($m['thumbnail'] ?: '/assets/img/manuscript-ashoka.jpg') ?>');">
                            <span class="clean-card-pill"><?= $m['era_start'] ? $m['era_start'] . ' CE' : '' ?></span>
                        </div>
                        <div class="clean-card-body">
                            <div style="font-size: 11px; text-transform: uppercase; color: var(--text-faint); font-weight: 700; margin-bottom: 4px;">
                                <?= htmlspecialchars($m['language_name']) ?> · <?= htmlspecialchars($m['place_ancient'] ?? 'India') ?>
                            </div>
                            <h3 class="clean-card-title" style="font-size: 17px;">
                                <a href="/manuscript.php?id=<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></a>
                            </h3>
                            <p class="clean-card-desc" style="font-size: 13px; line-height: 1.5; color: var(--text-muted); margin-bottom: 14px;">
                                <?= htmlspecialchars(substr($m['summary'] ?? '', 0, 115)) ?>...
                            </p>
                            <div class="clean-card-footer">
                                <span style="font-size: 12px;">By <?= htmlspecialchars(substr($m['author'], 0, 16)) ?></span>
                                <a href="/manuscript.php?id=<?= $m['id'] ?>" class="btn btn-primary btn-sm">Read in Hindi & English →</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
