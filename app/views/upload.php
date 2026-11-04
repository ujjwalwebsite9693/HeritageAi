<?php
// app/views/upload.php
$pageTitle = "Upload Manuscript Scan";
require __DIR__ . '/layouts/header.php';
?>

<div style="margin-bottom: 24px;">
    <h1 style="font-size: 30px; color: var(--primary);">Deposit Rare Manuscript Codex</h1>
    <p style="color: var(--text-muted); font-size: 14px;">
        Upload digitized high-resolution scans (JPG, PNG, TIFF, PDF) and index codicological metadata. Automatic OCR decipherment and cross-corpus link discovery will trigger automatically.
    </p>
</div>

<div class="form-card" style="margin: 0 auto 40px auto;">
    <form action="/upload" method="POST" enctype="multipart/form-data">
        <?= Csrf::field() ?>

        <div class="form-group">
            <label class="form-label">Manuscript Title *</label>
            <input type="text" name="title" class="form-control" placeholder="e.g. Copper Plate Charter of Samudragupta" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Author / Attributed Chronicler</label>
                <input type="text" name="author" class="form-control" placeholder="e.g. Harishena or Anonymous Royal Scribe">
            </div>

            <div class="form-group">
                <label class="form-label">Classical Language *</label>
                <select name="language_id" class="form-control" required>
                    <?php foreach ($languages as $l): ?>
                        <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['name']) ?> (<?= htmlspecialchars($l['script']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Palaeographic Script *</label>
                <input type="text" name="script" class="form-control" placeholder="e.g. Gupta Brahmi, Devanagari, Sharada, Nastaliq" required>
            </div>

            <div class="form-group">
                <label class="form-label">Origin Place / Epigraphic Center</label>
                <select name="origin_place_id" class="form-control">
                    <option value="">Select Origin Center</option>
                    <?php foreach ($places as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['ancient_name']) ?> (<?= htmlspecialchars($p['modern_name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Normalized Start Year *</label>
                <input type="number" name="era_start" class="form-control" placeholder="e.g. -250 for 250 BCE, or 630" required>
                <small style="color: var(--text-faint); font-size: 11px;">Negative integer for BCE</small>
            </div>

            <div class="form-group">
                <label class="form-label">Normalized End Year</label>
                <input type="number" name="era_end" class="form-control" placeholder="e.g. 645">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Custodial Repository / Source Vault *</label>
                <input type="text" name="source_repository" class="form-control" placeholder="e.g. National Archives of India, Epigraphy Branch" required>
            </div>

            <div class="form-group">
                <label class="form-label">Call / Accession Number</label>
                <input type="text" name="call_number" class="form-control" placeholder="e.g. ASI-EP-4421-B">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Copyright License / Access Level</label>
            <select name="license" class="form-control">
                <option value="Public Domain (CC0)">Public Domain (Creative Commons Zero)</option>
                <option value="Creative Commons Attribution (CC-BY 4.0)">Creative Commons Attribution (CC-BY 4.0)</option>
                <option value="Restricted Scholarly Access">Restricted Scholarly Access (Educational Use)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Folio Scan File (JPG / PNG / TIFF / PDF) *</label>
            <input type="file" name="scan_file" class="form-control" accept="image/jpeg,image/png,image/tiff,application/pdf" required>
            <small style="color: var(--text-faint); font-size: 11px;">Max upload size: 25MB. High-resolution captures recommended.</small>
        </div>

        <div class="form-group">
            <label class="form-label">Codicological Summary / Inscription Overview</label>
            <textarea name="summary" class="form-control" placeholder="Describe the material support (palm leaf, birch-bark, copper plate, paper), physical condition, and historical contents..." style="min-height: 90px;"></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Initial Transcription (Optional)</label>
            <textarea name="transcription_initial" class="form-control" placeholder="If transcription text is already partially deciphered, paste here; otherwise OCR will process it automatically..." style="min-height: 80px;"></textarea>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                🚀 Upload Codex & Start Analysis
            </button>
            <a href="/search" class="btn btn-secondary btn-lg">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

