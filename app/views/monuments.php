<?php
// app/views/monuments.php
$pageTitle = "Famous Historical Monuments";
require __DIR__ . '/layouts/header.php';
?>

<div style="margin-bottom: 28px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div class="hero-pill">
                🏰 Living Architectural Heritage · भारत के ऐतिहासिक स्मारक
            </div>
            <h1 style="font-size: 32px; color: var(--text-main); margin-bottom: 6px;">Famous Historical Monuments of India</h1>
            <p style="color: var(--text-muted); font-size: 15px; max-width: 760px;">
                ताजमहल, लाल किला, हुमायूँ का मकबरा, फतेहपुर सीकरी और प्राचीन नालंदा विश्वविद्यालय — जानें इन विश्व-प्रसिद्ध स्मारकों को किसने बनवाया और क्या है इनकी ऐतिहासिक कहानी।
            </p>
        </div>
        <?php if (Auth::hasRole('researcher', 'admin')): ?>
            <div>
                <a href="/monuments/create" class="btn btn-primary" style="padding: 10px 22px;">+ Add New Monument (नया स्मारक जोड़ें)</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- City Filter Pills -->
<div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px;">
    <button class="btn btn-primary btn-sm mon-filter-btn active" onclick="filterMonuments('all', this)">All Monuments (सभी)</button>
    <button class="btn btn-secondary btn-sm mon-filter-btn" onclick="filterMonuments('Agra', this)">Agra (आगरा)</button>
    <button class="btn btn-secondary btn-sm mon-filter-btn" onclick="filterMonuments('Delhi', this)">Delhi (दिल्ली)</button>
    <button class="btn btn-secondary btn-sm mon-filter-btn" onclick="filterMonuments('Bihar', this)">Bihar / Nalanda (बिहार)</button>
    <button class="btn btn-secondary btn-sm mon-filter-btn" onclick="filterMonuments('Gujarat', this)">Gujarat (गुजरात)</button>
</div>

<!-- Monuments Card Grid -->
<div class="clean-grid" style="grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 24px;">
    <?php foreach ($monuments as $mon): ?>
        <div class="monument-card clean-manuscript-card" data-location="<?= htmlspecialchars($mon['location_text']) ?>" style="border-radius: var(--radius-md);">
            <div class="clean-card-img" style="height: 190px; background-image: url('<?= htmlspecialchars($mon['image_url']) ?>');">
                <span class="clean-card-pill" style="background: rgba(15, 23, 42, 0.88);">
                    <?= htmlspecialchars($mon['built_year_text']) ?>
                </span>
            </div>
            <div class="clean-card-body">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-size: 11px; text-transform: uppercase; color: var(--accent-gold); font-weight: 700;">
                        <?= htmlspecialchars($mon['era_label']) ?>
                    </span>
                    <span style="font-size: 12px; color: var(--text-faint);">
                        📍 <?= htmlspecialchars($mon['location_text']) ?>
                    </span>
                </div>

                <h3 class="clean-card-title" style="font-size: 18px;">
                    <a href="/monument?id=<?= $mon['id'] ?>"><?= htmlspecialchars($mon['name']) ?></a>
                </h3>

                <div style="font-size: 13px; color: var(--text-body); margin-bottom: 8px;">
                    <strong>Built By / निर्माता:</strong> <?= htmlspecialchars($mon['built_by']) ?>
                </div>

                <p class="clean-card-desc" style="font-size: 13.5px; color: var(--text-muted); line-height: 1.5; margin-bottom: 12px;">
                    <?= htmlspecialchars($mon['summary']) ?>
                </p>

                <?php if (!empty($mon['highlights_array'])): ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px;">
                        <?php foreach (array_slice($mon['highlights_array'], 0, 3) as $hl): ?>
                            <span class="clean-pill" style="font-size: 11.5px; padding: 2px 8px;">✓ <?= htmlspecialchars($hl) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="clean-card-footer" style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: var(--text-faint);"><?= htmlspecialchars($mon['architectural_style'] ?? 'Architecture') ?></span>
                    <div style="display: flex; gap: 6px;">
                        <?php if (Auth::hasRole('researcher', 'admin')): ?>
                            <a href="/monuments/edit?id=<?= $mon['id'] ?>" class="btn btn-secondary btn-sm" title="Edit">✏️ Edit</a>
                        <?php endif; ?>
                        <a href="/monument?id=<?= $mon['id'] ?>" class="btn btn-primary btn-sm">Details →</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
function filterMonuments(loc, btn) {
    document.querySelectorAll('.mon-filter-btn').forEach(b => {
        b.classList.remove('btn-primary', 'active');
        b.classList.add('btn-secondary');
    });
    btn.classList.add('btn-primary', 'active');
    btn.classList.remove('btn-secondary');

    document.querySelectorAll('.monument-card').forEach(card => {
        var cardLoc = card.getAttribute('data-location') || '';
        if (loc === 'all' || cardLoc.toLowerCase().indexOf(loc.toLowerCase()) !== -1) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php require __DIR__ . '/layouts/footer.php'; ?>
