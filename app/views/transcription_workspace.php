<?php
// app/views/transcription_workspace.php
$pageTitle = "Transcription Workspace — Folio " . $page['page_no'];
require __DIR__ . '/layouts/header.php';
?>

<div style="margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <a href="/manuscript?id=<?= $page['manuscript_id'] ?>" style="font-size: 13px; font-weight: 600;">← Back to Manuscript Dossier</a>
                <span class="badge <?= $page['status'] === 'verified' ? 'badge-verified' : ($page['status'] === 'corrected' ? 'badge-corrected' : 'badge-draft') ?>">
                    Status: <?= strtoupper($page['status']) ?>
                </span>
                <span class="badge badge-score">
                    Confidence: <?= $page['ocr_confidence'] ?>%
                </span>
            </div>
            <h1 style="font-size: 26px; color: var(--primary);">
                Transcription Workspace: <?= htmlspecialchars($page['manuscript_title']) ?> (Folio <?= $page['page_no'] ?>)
            </h1>
        </div>

        <div style="display: flex; gap: 8px;">
            <button id="btn-run-ocr" class="btn btn-secondary btn-sm" onclick="triggerOcr(<?= $page['id'] ?>)">
                🔍 Re-run OCR Pipeline
            </button>
            <button id="btn-run-ai-trans" class="btn btn-secondary btn-sm" onclick="triggerTranslate(<?= $page['id'] ?>, 'en')">
                🌐 AI Draft Translation (EN)
            </button>
            <button id="btn-run-ai-trans-hi" class="btn btn-secondary btn-sm" onclick="triggerTranslate(<?= $page['id'] ?>, 'hi')">
                🌐 AI Draft Translation (HI)
            </button>
        </div>
    </div>
</div>

<!-- Split Screen: Image Viewer on Left, Line-by-Line Transcription Editor on Right -->
<div class="viewer-workspace">
    <!-- Left: High-Res Zoomable Folio -->
    <div class="viewer-pane">
        <div class="viewer-pane-header">
            <span class="viewer-pane-title">Folio Facsimile Scan</span>
            <div style="display: flex; gap: 6px;">
                <button class="btn btn-secondary btn-sm" id="btn-osd-zoom-in">+</button>
                <button class="btn btn-secondary btn-sm" id="btn-osd-zoom-out">-</button>
                <button class="btn btn-secondary btn-sm" id="btn-osd-home">Fit</button>
            </div>
        </div>
        <div id="workspace-osd-viewer" class="osd-container"></div>
    </div>

    <!-- Right: Editorial Transcription & Transliteration Workspace -->
    <div class="viewer-pane">
        <div class="viewer-pane-header">
            <span class="viewer-pane-title">Palaeographic Text Decipherment</span>
            <div>
                <span style="font-size: 12px; color: var(--text-muted);">Language: <?= htmlspecialchars($page['language_name']) ?> (<?= htmlspecialchars($page['script']) ?>)</span>
            </div>
        </div>

        <form action="/transcribe/save" method="POST" style="flex: 1; display: flex; flex-direction: column; padding: 20px; overflow-y: auto;">
            <?= Csrf::field() ?>
            <input type="hidden" name="page_id" value="<?= $page['id'] ?>">

            <div class="form-group" style="flex: 1; margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="form-label" style="margin: 0;">Original Epigraphic / Script Transcription</label>
                    <span style="font-size: 11px; color: var(--text-faint);">Line breaks preserved</span>
                </div>
                <textarea id="transcription-input" name="transcription" class="form-control" style="height: 180px; font-family: var(--font-indic); font-size: 16px; line-height: 1.8;" placeholder="Deciphered original text..."><?= htmlspecialchars($page['transcription'] ?? $page['ocr_text'] ?? '') ?></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label">Romanized Transliteration (IAST / Standard)</label>
                <textarea id="transliteration-input" name="transliteration" class="form-control" style="height: 100px; font-style: italic; font-size: 14px; line-height: 1.6;" placeholder="Romanized phonetic transliteration..."><?= htmlspecialchars($page['transliteration'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid var(--border-subtle);">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <label class="form-label" style="margin: 0;">Mark Status:</label>
                    <select name="status" class="form-control" style="width: 150px; padding: 6px 10px; font-size: 13px;">
                        <option value="corrected" <?= $page['status'] === 'corrected' ? 'selected' : '' ?>>Corrected</option>
                        <option value="verified" <?= $page['status'] === 'verified' ? 'selected' : '' ?>>Verified (Peer Reviewed)</option>
                        <option value="ocr_draft" <?= $page['status'] === 'ocr_draft' ? 'selected' : '' ?>>OCR Draft</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    💾 Save Decipherment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var wsViewer = OpenSeadragon({
        id: "workspace-osd-viewer",
        prefixUrl: "https://cdn.jsdelivr.net/npm/openseadragon@4.1.1/build/openseadragon/images/",
        tileSources: {
            type: 'image',
            url: '<?= htmlspecialchars($page['image_path']) ?>'
        },
        showNavigationControl: false,
        defaultZoomLevel: 0.95
    });

    document.getElementById('btn-osd-zoom-in').addEventListener('click', function() { wsViewer.viewport.zoomBy(1.2); });
    document.getElementById('btn-osd-zoom-out').addEventListener('click', function() { wsViewer.viewport.zoomBy(0.8); });
    document.getElementById('btn-osd-home').addEventListener('click', function() { wsViewer.viewport.goHome(); });
});

function triggerOcr(pageId) {
    var btn = document.getElementById('btn-run-ocr');
    btn.disabled = true;
    btn.innerText = "Processing OCR...";

    fetch('/api/ocr', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'page_id=' + pageId
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerText = "🔍 Re-run OCR Pipeline";
        if (data.ocr_text) {
            document.getElementById('transcription-input').value = data.ocr_text;
            alert("OCR Processed! Text updated into editor with " + data.confidence + "% confidence.");
        } else {
            alert(data.error || "OCR completed.");
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = "🔍 Re-run OCR Pipeline";
        alert("OCR request failed.");
    });
}

function triggerTranslate(pageId, targetLang) {
    var btn = targetLang === 'en' ? document.getElementById('btn-run-ai-trans') : document.getElementById('btn-run-ai-trans-hi');
    btn.disabled = true;
    btn.innerText = "Translating...";

    fetch('/api/pages/' + pageId + '/translate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ target_lang: targetLang })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerText = targetLang === 'en' ? "🌐 AI Draft Translation (EN)" : "🌐 AI Draft Translation (HI)";
        if (data.translation) {
            alert("Translation generated (" + (data.cached ? "Retrieved from DB cache" : "Generated via AI engine") + ")! View in manuscript detail or translation review queue.");
        } else {
            alert(data.error || "Translation saved.");
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = "🌐 AI Draft Translation";
        alert("Translation request failed.");
    });
}
</script>

<?php require __DIR__ . '/layouts/footer.php'; ?>

