<?php
// app/views/manuscript_detail.php
$pageTitle = cleanDocTitle($manuscript['title']);
require __DIR__ . '/layouts/header.php';

$activePage = !empty($manuscript['pages']) ? $manuscript['pages'][0] : null;
$pageId = isset($_GET['page']) ? (int)$_GET['page'] : ($activePage ? $activePage['page_no'] : 1);

foreach ($manuscript['pages'] as $p) {
    if ((int)$p['page_no'] === $pageId) {
        $activePage = $p;
        break;
    }
}

// Find translations for active page
$transEn = null;
$transHi = null;
if ($activePage && !empty($activePage['translations'])) {
    foreach ($activePage['translations'] as $t) {
        if ($t['target_lang'] === 'en' && !$transEn) $transEn = $t;
        if ($t['target_lang'] === 'hi' && !$transHi) $transHi = $t;
    }
}
?>

<!-- Clean Breadcrumb & Header Bar -->
<div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-xs); margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div style="max-width: 840px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                <span class="badge" style="background: var(--accent-blue-light); color: var(--accent-blue);">
                    <?= Calendar::formatYear((int)$manuscript['era_start']) ?>
                </span>
                <span class="badge" style="background: var(--bg-surface-alt); color: var(--text-body);">
                    <?= htmlspecialchars($manuscript['language_name']) ?> (<?= htmlspecialchars($manuscript['script']) ?>)
                </span>
                <span class="badge badge-verified"><?= strtoupper($manuscript['license']) ?></span>
            </div>

            <h1 style="font-size: 26px; line-height: 1.3; margin-bottom: 8px;"><?= htmlspecialchars($manuscript['title']) ?></h1>
            
            <p style="font-size: 13.5px; color: var(--text-muted);">
                <strong>Author:</strong> <?= htmlspecialchars($manuscript['author']) ?> · 
                <strong>Origin:</strong> <?= htmlspecialchars($manuscript['place_ancient'] ?? 'Unknown') ?> (Modern <?= htmlspecialchars($manuscript['place_modern'] ?? 'N/A') ?>) · 
                <strong>Vault:</strong> <?= htmlspecialchars($manuscript['source_repository']) ?>
            </p>
        </div>

        <div style="display: flex; gap: 8px; align-items: center;">
            <button onclick="document.getElementById('citation-modal').style.display='flex'" class="btn btn-secondary btn-sm">
                📋 Cite
            </button>
            <?php if (Auth::hasRole('researcher', 'admin') && $activePage): ?>
                <a href="/transcribe?page_id=<?= $activePage['id'] ?>" class="btn btn-primary btn-sm">
                    ✏️ Edit Transcription
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Folio / Page Selector Bar -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; background: var(--bg-surface); padding: 8px 16px; border: 1px solid var(--border-light); border-radius: var(--radius-md);">
    <div style="display: flex; gap: 6px; align-items: center;">
        <span style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-right: 6px;">Select Folio:</span>
        <?php foreach ($manuscript['pages'] as $p): ?>
            <a href="/manuscript?id=<?= $manuscript['id'] ?>&page=<?= $p['page_no'] ?>" 
               class="btn <?= (int)$p['page_no'] === (int)$pageId ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                Folio <?= $p['page_no'] ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($activePage): ?>
        <div style="font-size: 12.5px; color: var(--text-muted);">
            Decipherment: 
            <span class="badge <?= $activePage['status'] === 'verified' ? 'badge-verified' : 'badge-draft' ?>">
                <?= ucfirst($activePage['status']) ?> (<?= $activePage['ocr_confidence'] ?>% Confidence)
            </span>
        </div>
    <?php endif; ?>
</div>

<!-- Reader Workspace (Left: High-Res Zoomable Facsimile | Right: Clean Reader) -->
<?php if ($activePage): ?>
    <div class="reader-container">
        <!-- Left: OpenSeadragon Zoom/Pan Box -->
        <div class="reader-viewer-box">
            <div style="background: rgba(17, 24, 39, 0.95); padding: 10px 16px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #1f2937;">
                <span style="color: #f3f4f6; font-size: 13px; font-weight: 600;">Folio <?= $activePage['page_no'] ?> Facsimile</span>
                <div style="display: flex; gap: 6px;">
                    <button class="btn btn-secondary btn-sm" id="btn-zoom-in" style="background:#374151;color:#fff;border:none;">Zoom In (+)</button>
                    <button class="btn btn-secondary btn-sm" id="btn-zoom-out" style="background:#374151;color:#fff;border:none;">Zoom Out (-)</button>
                    <button class="btn btn-secondary btn-sm" id="btn-home" style="background:#374151;color:#fff;border:none;">Fit</button>
                    <button class="btn btn-secondary btn-sm" id="btn-full" style="background:#374151;color:#fff;border:none;">⛶ Fullscreen</button>
                </div>
            </div>
            <div id="openseadragon-viewer" style="flex: 1; background: #0b0f19; min-height: 480px;"></div>
        </div>

        <!-- Right: Uncluttered Clean Reader -->
        <div class="reader-text-box">
            <div class="reader-tab-bar">
                <button class="reader-tab-btn active" onclick="switchTab('trans-view', this)">English Translation</button>
                <button class="reader-tab-btn" onclick="switchTab('hindi-view', this)">हिंदी अनुवाद (Hindi)</button>
                <button class="reader-tab-btn" onclick="switchTab('original-view', this)">Original Script</button>
                <button class="reader-tab-btn" onclick="switchTab('side-view', this)">Side-by-Side Comparison</button>
                <button class="reader-tab-btn" onclick="switchTab('notes-view', this)">Notes (<?= count($activePage['annotations']) ?>)</button>
            </div>

            <!-- Tab 1: English Translation (Default, Easy to Read) -->
            <div id="tab-trans-view" class="tab-pane reader-body-content" style="display: block;">
                <div style="background: #fafaf9; border-left: 4px solid var(--primary); padding: 20px; border-radius: 6px; font-size: 16px; line-height: 1.8;">
                    <?php if ($transEn): ?>
                        <?php
                            $textWithGlossary = htmlspecialchars($transEn['text']);
                            foreach ($glossaryMap as $item) {
                                $pattern = '/\b(' . preg_quote($item['term'], '/') . ')\b/iu';
                                $tooltip = htmlspecialchars("{$item['term']}: {$item['meaning_en']} (हिंदी: {$item['meaning_hi']})");
                                $replacement = '<span class="glossary-word" data-tooltip="' . $tooltip . '">$1</span>';
                                $textWithGlossary = preg_replace($pattern, $replacement, $textWithGlossary, 1);
                            }
                            echo nl2br($textWithGlossary);
                        ?>
                    <?php else: ?>
                        <em>Translation in progress. Check back soon.</em>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 14px; font-size: 12px; color: var(--text-faint);">
                    💡 <em>Tip: Words with dotted underlines have instant definitions on mouse hover.</em>
                </div>
            </div>

            <!-- Tab 2: Hindi Translation -->
            <div id="tab-hindi-view" class="tab-pane reader-body-content" style="display: none;">
                <div style="font-family: var(--font-indic); background: #fafaf9; border-left: 4px solid var(--accent-gold); padding: 20px; border-radius: 6px; font-size: 17px; line-height: 1.9;">
                    <?php if ($transHi): ?>
                        <?= nl2br(htmlspecialchars($transHi['text'])) ?>
                    <?php else: ?>
                        <em>इस पृष्ठ का हिंदी अनुवाद अभी उपलब्ध नहीं है।</em>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab 3: Original Script & Transliteration -->
            <div id="tab-original-view" class="tab-pane reader-body-content" style="display: none;">
                <h4 style="font-size: 14px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px;">
                    Original Inscription (<?= htmlspecialchars($manuscript['script']) ?>)
                </h4>
                <div style="font-family: var(--font-indic); font-size: 17px; line-height: 1.9; background: #fafaf9; padding: 18px; border-radius: 6px; margin-bottom: 20px; border: 1px solid var(--border-light);">
                    <?= nl2br(htmlspecialchars($activePage['transcription'] ?? 'No transcription available.')) ?>
                </div>

                <?php if ($activePage['transliteration']): ?>
                    <h4 style="font-size: 14px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px;">
                        Phonetic Transliteration (IAST)
                    </h4>
                    <div style="font-style: italic; font-size: 14.5px; line-height: 1.8; color: #475569; background: #fafaf9; padding: 18px; border-radius: 6px; border: 1px solid var(--border-light);">
                        <?= nl2br(htmlspecialchars($activePage['transliteration'])) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab 4: Side by Side Comparison -->
            <div id="tab-side-view" class="tab-pane" style="display: none; padding: 16px; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="background: #fafaf9; padding: 16px; border-radius: 6px; border: 1px solid var(--border-light);">
                        <div style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: var(--primary); margin-bottom: 8px;">
                            Original Script
                        </div>
                        <div style="font-family: var(--font-indic); font-size: 14px; line-height: 1.7;">
                            <?= nl2br(htmlspecialchars($activePage['transcription'] ?? '')) ?>
                        </div>
                    </div>

                    <div style="background: #fafaf9; padding: 16px; border-radius: 6px; border: 1px solid var(--border-light);">
                        <div style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: var(--accent-gold); margin-bottom: 8px;">
                            English Translation
                        </div>
                        <div style="font-size: 14px; line-height: 1.7;">
                            <?= nl2br(htmlspecialchars($transEn['text'] ?? '')) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 5: Notes & Discussion -->
            <div id="tab-notes-view" class="tab-pane reader-body-content" style="display: none;">
                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
                    <?php if (empty($activePage['annotations'])): ?>
                        <p style="color: var(--text-muted);">No notes added yet for this folio.</p>
                    <?php else: ?>
                        <?php foreach ($activePage['annotations'] as $ann): ?>
                            <div style="background: #f8fafc; border: 1px solid var(--border-light); border-radius: 6px; padding: 14px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 12px;">
                                    <strong><?= htmlspecialchars($ann['author_name']) ?></strong>
                                    <span class="badge" style="background:#fff;"><?= strtoupper($ann['type']) ?></span>
                                </div>
                                <p style="font-size: 13.5px; color: #334155; line-height: 1.5;">
                                    <?= nl2br(htmlspecialchars($ann['note'])) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if (Auth::check()): ?>
                    <form action="/api/pages/<?= $activePage['id'] ?>/annotations" method="POST" style="background: #ffffff; border: 1px solid var(--border-light); padding: 16px; border-radius: 6px;">
                        <?= Csrf::field() ?>
                        <h4 style="font-size: 14px; margin-bottom: 8px;">Add Note to this Folio</h4>
                        <textarea name="note" class="form-control" style="min-height: 70px; margin-bottom: 8px;" placeholder="Write a note or observation..." required></textarea>
                        <button type="submit" class="btn btn-primary btn-sm">Submit Note</button>
                    </form>
                <?php else: ?>
                    <p style="font-size: 13px; color: var(--text-muted);"><a href="/login" style="font-weight: 600;">Log in</a> to leave a note or question on this folio.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Connected Manuscripts -->
<div style="margin-top: 40px;">
    <h3 style="font-size: 20px; margin-bottom: 14px;">Connected Manuscripts & Cross-References</h3>
    <?php if (empty($connectedLinks)): ?>
        <p style="color: var(--text-muted);">No historical connections established yet.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 14px;">
            <?php foreach ($connectedLinks as $link): ?>
                <?php 
                    $otherId = ($link['manuscript_a'] == $manuscript['id']) ? $link['manuscript_b'] : $link['manuscript_a'];
                    $otherTitle = ($link['manuscript_a'] == $manuscript['id']) ? $link['doc_b_title'] : $link['doc_a_title'];
                    $reason = json_decode($link['reason_json'], true) ?: [];
                    $cleanReason = cleanExplanation($reason['explanation'] ?? '', $link['manuscript_a'], $link['manuscript_b']);
                ?>
                <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 18px; box-shadow: var(--shadow-xs);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <div style="font-size: 15px; font-weight: 700;">
                            🔗 Linked with: <a href="/manuscript?id=<?= $otherId ?>"><?= htmlspecialchars(cleanDocTitle($otherTitle)) ?></a>
                        </div>
                        <span class="badge badge-score"><?= $link['score'] ?>% Connection Match</span>
                    </div>
                    <p style="font-size: 13.5px; color: var(--text-body); line-height: 1.5; margin-bottom: 8px;">
                        <?= htmlspecialchars($cleanReason) ?>
                    </p>
                    <a href="/manuscript?id=<?= $otherId ?>" class="btn btn-secondary btn-sm">Inspect Connected Codex →</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Simple Citation Modal -->
<div id="citation-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; width: 100%; max-width: 580px; border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-lg);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border-light); padding-bottom: 10px;">
            <h3 style="font-size: 18px;">How to Cite this Manuscript</h3>
            <button onclick="document.getElementById('citation-modal').style.display='none'" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px;">
            <div>
                <label class="form-label">Chicago Style</label>
                <div style="background: var(--bg-surface-alt); padding: 10px; border-radius: 6px; font-size: 13px;">
                    <?= htmlspecialchars($citations['chicago']) ?>
                </div>
            </div>
            <div>
                <label class="form-label">APA Style</label>
                <div style="background: var(--bg-surface-alt); padding: 10px; border-radius: 6px; font-size: 13px;">
                    <?= htmlspecialchars($citations['apa']) ?>
                </div>
            </div>
        </div>

        <div style="text-align: right; margin-top: 20px;">
            <button onclick="document.getElementById('citation-modal').style.display='none'" class="btn btn-secondary btn-sm">Close</button>
        </div>
    </div>
</div>

<?php if ($activePage): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var viewer = OpenSeadragon({
        id: "openseadragon-viewer",
        prefixUrl: "https://cdn.jsdelivr.net/npm/openseadragon@4.1.1/build/openseadragon/images/",
        tileSources: {
            type: 'image',
            url: '<?= htmlspecialchars($activePage['image_path']) ?>'
        },
        showNavigationControl: false,
        maxZoomPixelRatio: 2.5,
        defaultZoomLevel: 0.95
    });

    document.getElementById('btn-zoom-in').addEventListener('click', function() { viewer.viewport.zoomBy(1.25); });
    document.getElementById('btn-zoom-out').addEventListener('click', function() { viewer.viewport.zoomBy(0.8); });
    document.getElementById('btn-home').addEventListener('click', function() { viewer.viewport.goHome(); });
    document.getElementById('btn-full').addEventListener('click', function() { viewer.setFullScreen(!viewer.isFullScreen()); });
});

function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-pane').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.reader-tab-btn').forEach(el => el.classList.remove('active'));

    var target = document.getElementById('tab-' + tabId);
    if (target) {
        target.style.display = 'block';
    }
    btn.classList.add('active');
}
</script>
<?php endif; ?>

<?php require __DIR__ . '/layouts/footer.php'; ?>
