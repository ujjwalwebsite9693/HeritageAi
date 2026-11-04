<?php
// app/views/archive_search.php
$pageTitle = "Browse Manuscripts";
require __DIR__ . '/layouts/header.php';
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
    <!-- Simple Filter Sidebar -->
    <aside style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 20px; box-shadow: var(--shadow-xs);">
        <form action="/search" method="GET">
            <h3 style="font-size: 16px; margin-bottom: 14px;">Search & Filters (खोजें)</h3>

            <div class="form-group">
                <label class="form-label">Search Keyword (शब्द)</label>
                <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($params['q']) ?>" placeholder="e.g. Akbar, Ashoka, Chanakya...">
            </div>

            <div class="form-group">
                <label class="form-label">Language (भाषा)</label>
                <select name="lang" class="form-control">
                    <option value="">All Languages (सभी भाषाएँ)</option>
                    <?php foreach ($facets['languages'] as $lang): ?>
                        <option value="<?= $lang['id'] ?>" <?= $params['lang'] == $lang['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lang['name']) ?> (<?= $lang['count'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Historical Place (स्थान)</label>
                <select name="place" class="form-control">
                    <option value="">All Places (सभी स्थान)</option>
                    <?php foreach ($facets['places'] as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $params['place'] == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['ancient_name']) ?> (<?= htmlspecialchars($p['modern_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 6px;">Filter Results</button>
            <a href="/search" class="btn btn-secondary" style="width: 100%; margin-top: 8px;">Reset (रीसेट)</a>
        </form>
    </aside>

    <!-- Results Grid -->
    <main>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div style="font-size: 14px; color: var(--text-muted);">
                Found <strong><?= $results['total'] ?></strong> manuscript(s)
                <?php if ($params['q']): ?> for "<em><?= htmlspecialchars($params['q']) ?></em>"<?php endif; ?>
            </div>
            <?php if (Auth::hasRole('researcher', 'admin')): ?>
                <a href="/upload" class="btn btn-primary btn-sm">+ Upload Codex</a>
            <?php endif; ?>
        </div>

        <?php if (empty($results['results'])): ?>
            <div style="background: var(--bg-surface); padding: 40px; text-align: center; border-radius: var(--radius-md); border: 1px solid var(--border-light);">
                <p style="color: var(--text-muted); font-size: 15px;">No documents match your query.</p>
                <a href="/search" class="btn btn-secondary btn-sm" style="margin-top: 10px;">Reset Search</a>
            </div>
        <?php else: ?>
            <div class="clean-grid">
                <?php foreach ($results['results'] as $m): ?>
                    <?php 
                        $shortTitle = cleanDocTitle($m['title']);
                    ?>
                    <div class="clean-manuscript-card">
                        <div class="clean-card-img" style="height: 180px; background-image: url('<?= htmlspecialchars($m['thumbnail'] ?: '/assets/img/manuscript-ashoka.jpg') ?>');">
                            <span class="clean-card-pill"><?= Calendar::formatYear((int)$m['era_start']) ?></span>
                        </div>
                        <div class="clean-card-body">
                            <div style="font-size: 11px; text-transform: uppercase; color: var(--text-faint); font-weight: 700; margin-bottom: 4px;">
                                <?= htmlspecialchars($m['language_name']) ?> · <?= htmlspecialchars($m['place_ancient'] ?? 'India') ?>
                            </div>
                            <h3 class="clean-card-title" style="font-size: 17px;">
                                <a href="/manuscript?id=<?= $m['id'] ?>"><?= htmlspecialchars($shortTitle) ?></a>
                            </h3>
                            <p class="clean-card-desc" style="font-size: 13px; line-height: 1.5; color: var(--text-muted); margin-bottom: 14px;">
                                <?= htmlspecialchars(substr($m['summary'] ?? '', 0, 115)) ?>...
                            </p>
                            <div class="clean-card-footer">
                                <span style="font-size: 12px;"><?= $m['page_count'] ?> Folios</span>
                                <a href="/manuscript?id=<?= $m['id'] ?>" class="btn btn-primary btn-sm">Read in Hindi & English →</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>
