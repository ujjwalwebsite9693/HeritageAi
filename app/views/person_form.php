<?php
// app/views/person_form.php - Add / Edit Historical Figure
$isEdit = !empty($person['id']);
$pageTitle = $isEdit ? "Edit Figure: " . $person['name'] : "Add New Emperor / Figure";
require __DIR__ . '/layouts/header.php';
?>

<div style="max-width: 800px; margin: 0 auto; background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: 36px; box-shadow: var(--shadow-sm);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px solid var(--border-light); padding-bottom: 16px;">
        <div>
            <span class="badge" style="background: var(--primary-light); color: var(--primary); margin-bottom: 6px;">
                👑 Sovereign Figures · महान शासक संपादन
            </span>
            <h1 style="font-size: 24px; color: var(--text-main); margin: 0;"><?= $isEdit ? 'Edit Figure' : 'Add New Emperor / Figure (नया शासक जोड़ें)' ?></h1>
        </div>
        <a href="/persons" class="btn btn-secondary btn-sm">← Back to Figures</a>
    </div>

    <form action="<?= $isEdit ? '/persons/edit?id=' . $person['id'] : '/persons/create' ?>" method="POST">
        <?= Csrf::field() ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 16px;">
            <div class="form-group">
                <label class="form-label">Full Name (नाम) *</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($person['name'] ?? '') ?>" placeholder="e.g. Akbar, Babur, Ashoka Maurya" required>
            </div>
            <div class="form-group">
                <label class="form-label">Alternate Titles & Aliases (उपाधियाँ व अन्य नाम)</label>
                <input type="text" name="alt_names" class="form-control" value="<?= htmlspecialchars($person['alt_names'] ?? '') ?>" placeholder="e.g. Jalal al-Din, Akbar the Great, Devanampriya">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Imperial Role / Title (उपाधि व पद) *</label>
            <input type="text" name="role" class="form-control" value="<?= htmlspecialchars($person['role'] ?? '') ?>" placeholder="e.g. Third Mughal Emperor, Founder of Maurya Empire" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 16px;">
            <div class="form-group">
                <label class="form-label">Birth Year (CE or negative for BCE)</label>
                <input type="number" name="birth_year" class="form-control" value="<?= htmlspecialchars($person['birth_year'] ?? '') ?>" placeholder="e.g. 1542 or -304">
            </div>
            <div class="form-group">
                <label class="form-label">Death Year (CE or negative for BCE)</label>
                <input type="number" name="death_year" class="form-control" value="<?= htmlspecialchars($person['death_year'] ?? '') ?>" placeholder="e.g. 1605 or -232">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label">Portrait Image URL / Path (चित्र लिंक)</label>
            <input type="text" name="portrait_url" class="form-control" value="<?= htmlspecialchars($person['portrait_url'] ?? '/assets/img/persons/ashoka.png') ?>" placeholder="/assets/img/persons/akbar.png">
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label class="form-label">Biography & Deeds (जीवन परिचय व कार्य) *</label>
            <textarea name="bio" class="form-control" rows="6" placeholder="Describe the ruler's achievements, architectural works, policies, and impact on Indian history..." required><?= htmlspecialchars($person['bio'] ?? '') ?></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <a href="/persons" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding: 10px 28px;">
                <?= $isEdit ? 'Save Changes (बदलाव सुरक्षित करें)' : 'Publish Figure (शासक जोड़ें)' ?>
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>
