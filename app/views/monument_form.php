<?php
// app/views/monument_form.php - Add / Edit Monument
$isEdit = !empty($monument['id']);
$pageTitle = $isEdit ? "Edit Monument: " . $monument['name'] : "Add New Historical Monument";
require __DIR__ . '/layouts/header.php';
?>

<div style="max-width: 800px; margin: 0 auto; background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: 36px; box-shadow: var(--shadow-sm);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px solid var(--border-light); padding-bottom: 16px;">
        <div>
            <span class="badge" style="background: var(--accent-gold-light); color: var(--accent-gold); margin-bottom: 6px;">
                🏰 Monument Management · स्मारक संपादन
            </span>
            <h1 style="font-size: 24px; color: var(--text-main); margin: 0;"><?= $isEdit ? 'Edit Monument' : 'Add New Monument (नया स्मारक जोड़ें)' ?></h1>
        </div>
        <a href="/monuments" class="btn btn-secondary btn-sm">← Back to Monuments</a>
    </div>

    <form action="<?= $isEdit ? '/monuments/edit?id=' . $monument['id'] : '/monuments/create' ?>" method="POST">
        <?= Csrf::field() ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 16px;">
            <div class="form-group">
                <label class="form-label">Monument Name (स्मारक का नाम) *</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($monument['name'] ?? '') ?>" placeholder="e.g. Taj Mahal, Agra Fort" required>
            </div>
            <div class="form-group">
                <label class="form-label">Alternate / Hindi Name (वैकल्पिक नाम)</label>
                <input type="text" name="alternate_name" class="form-control" value="<?= htmlspecialchars($monument['alternate_name'] ?? '') ?>" placeholder="e.g. ताजमहल, लाल किला">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 16px;">
            <div class="form-group">
                <label class="form-label">Commissioned By / Builder (निर्माता) *</label>
                <input type="text" name="built_by" class="form-control" value="<?= htmlspecialchars($monument['built_by'] ?? '') ?>" placeholder="e.g. Emperor Shah Jahan, Emperor Akbar" required>
            </div>
            <div class="form-group">
                <label class="form-label">Associated Emperor / Ruler in Archive</label>
                <select name="person_id" class="form-control">
                    <option value="">-- None / General Monarch --</option>
                    <?php foreach ($persons as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($monument['person_id'] ?? 0) == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['role']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; margin-bottom: 16px;">
            <div class="form-group">
                <label class="form-label">Construction Year (CE) *</label>
                <input type="number" name="built_year" class="form-control" value="<?= htmlspecialchars($monument['built_year'] ?? '1600') ?>" placeholder="1632">
            </div>
            <div class="form-group">
                <label class="form-label">Period Text (e.g. 1632 – 1653 CE)</label>
                <input type="text" name="built_year_text" class="form-control" value="<?= htmlspecialchars($monument['built_year_text'] ?? '') ?>" placeholder="1632 – 1653 CE" required>
            </div>
            <div class="form-group">
                <label class="form-label">Era Label (युग)</label>
                <input type="text" name="era_label" class="form-control" value="<?= htmlspecialchars($monument['era_label'] ?? 'Mughal Golden Age') ?>" placeholder="e.g. Mughal Golden Age, Classical">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 16px;">
            <div class="form-group">
                <label class="form-label">Location (स्थान) *</label>
                <input type="text" name="location_text" class="form-control" value="<?= htmlspecialchars($monument['location_text'] ?? '') ?>" placeholder="e.g. Agra, Uttar Pradesh" required>
            </div>
            <div class="form-group">
                <label class="form-label">Architectural Style (वास्तुकला शैली)</label>
                <input type="text" name="architectural_style" class="form-control" value="<?= htmlspecialchars($monument['architectural_style'] ?? 'Indo-Islamic Architectural Marvel') ?>" placeholder="e.g. Indo-Islamic, Classical Mauryan">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Image URL / Path (चित्र का लिंक)</label>
            <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($monument['image_url'] ?? '/assets/img/monuments/taj_mahal.jpg') ?>" placeholder="/assets/img/monuments/taj_mahal.jpg">
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Short Summary (संक्षिप्त परिचय) *</label>
            <textarea name="summary" class="form-control" rows="3" placeholder="A 2-sentence quick description of the monument..." required><?= htmlspecialchars($monument['summary'] ?? '') ?></textarea>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Full History & Story (विस्तृत इतिहास व कहानी) *</label>
            <textarea name="history" class="form-control" rows="5" placeholder="Detailed architectural and historical story of the monument..." required><?= htmlspecialchars($monument['history'] ?? '') ?></textarea>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Visiting & Ticket Info (दर्शन समय व नियम)</label>
            <input type="text" name="visiting_info" class="form-control" value="<?= htmlspecialchars($monument['visiting_info'] ?? 'Open sunrise to sunset. Friday closed for prayers.') ?>" placeholder="Open sunrise to sunset.">
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label class="form-label">Key Highlights (Comma separated, e.g. White Marble, UNESCO Site, Onion Dome)</label>
            <?php
                $hlString = '';
                if (!empty($monument['highlights_array'])) {
                    $hlString = implode(', ', $monument['highlights_array']);
                }
            ?>
            <input type="text" name="highlights" class="form-control" value="<?= htmlspecialchars($hlString) ?>" placeholder="White Marble Dome, UNESCO World Heritage, Taj Museum">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <a href="/monuments" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding: 10px 28px;">
                <?= $isEdit ? 'Save Changes (बदलाव सुरक्षित करें)' : 'Publish Monument (स्मारक जोड़ें)' ?>
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>
