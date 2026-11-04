<?php
// app/views/persons.php
$pageTitle = "Emperors & Historical Figures";
require __DIR__ . '/layouts/header.php';
?>

<div style="margin-bottom: 28px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div class="hero-pill">
                👑 Legendary Rulers & Thinkers · महान शासक और व्यक्तित्व
            </div>
            <h1 style="font-size: 32px; color: var(--text-main); margin-bottom: 6px;">Emperors, Kings & Scholars of India</h1>
            <p style="color: var(--text-muted); font-size: 15px; max-width: 760px;">
                सम्राट अकबर, हुमायूँ, शाहजहाँ, बाबर, अशोक और चाणक्य — भारत के महान शासकों का जीवन, उनकी नीतियां, और उनके द्वारा निर्मित ऐतिहासिक धरोहरें।
            </p>
        </div>
        <?php if (Auth::hasRole('researcher', 'admin')): ?>
            <div>
                <a href="/persons/create" class="btn btn-primary" style="padding: 10px 22px;">+ Add New Emperor / Figure (नया शासक जोड़ें)</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Category Filter Pills -->
<div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px;">
    <button class="btn btn-primary btn-sm filter-figure-btn active" onclick="filterFigures('all', this)">All Figures (सभी)</button>
    <button class="btn btn-secondary btn-sm filter-figure-btn" onclick="filterFigures('mughal', this)">Mughal Dynasty (मुग़ल शासक)</button>
    <button class="btn btn-secondary btn-sm filter-figure-btn" onclick="filterFigures('maurya', this)">Maurya Dynasty (मौर्य शासक)</button>
    <button class="btn btn-secondary btn-sm filter-figure-btn" onclick="filterFigures('classical', this)">Scholars & Ministers (विद्वान व मंत्री)</button>
</div>

<!-- Figures Grid -->
<div class="clean-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
    <?php foreach ($persons as $p): ?>
        <?php
            $dynastyTag = 'classical';
            if (stripos($p['role'], 'Mughal') !== false || in_array($p['id'], [6, 7, 8, 10, 11, 12, 13])) {
                $dynastyTag = 'mughal';
            } elseif (stripos($p['role'], 'Maurya') !== false || in_array($p['id'], [1, 2, 3])) {
                $dynastyTag = 'maurya';
            }
        ?>
        <div class="figure-card clean-manuscript-card" data-dynasty="<?= $dynastyTag ?>" style="padding: 24px; text-align: center; border-radius: var(--radius-md);">
            <div style="position: relative; width: 110px; height: 110px; margin: 0 auto 16px auto;">
                <img src="<?= htmlspecialchars($p['portrait_url'] ?: '/assets/img/persons/ashoka.png') ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width: 110px; height: 110px; border-radius: 50%; object-fit: cover; border: 3px solid #cbd5e1; box-shadow: var(--shadow-sm);">
            </div>

            <span class="badge" style="background: var(--bg-surface-alt); color: var(--text-muted); margin-bottom: 8px;">
                <?= Calendar::formatYear((int)$p['birth_year']) ?> – <?= Calendar::formatYear((int)$p['death_year']) ?>
            </span>

            <h3 style="font-size: 18px; margin-bottom: 4px;">
                <a href="/person?id=<?= $p['id'] ?>"><?= htmlspecialchars(cleanPersonName($p['name'])) ?></a>
            </h3>

            <div style="font-size: 12.5px; font-weight: 600; color: var(--accent-gold); margin-bottom: 12px;">
                <?= htmlspecialchars(cleanRole($p['role'])) ?>
            </div>

            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 18px; text-align: left; flex: 1;">
                <?= htmlspecialchars(substr($p['bio'] ?? '', 0, 130)) ?>...
            </p>

            <div style="display: flex; gap: 6px; width: 100%;">
                <?php if (Auth::hasRole('researcher', 'admin')): ?>
                    <a href="/persons/edit?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm" style="flex: 1;" title="Edit">✏️ Edit</a>
                <?php endif; ?>
                <a href="/person?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" style="flex: 2;">
                    View Life & Heritage →
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
function filterFigures(category, btn) {
    document.querySelectorAll('.filter-figure-btn').forEach(b => {
        b.classList.remove('btn-primary', 'active');
        b.classList.add('btn-secondary');
    });
    btn.classList.add('btn-primary', 'active');
    btn.classList.remove('btn-secondary');

    document.querySelectorAll('.figure-card').forEach(card => {
        if (category === 'all' || card.getAttribute('data-dynasty') === category) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php require __DIR__ . '/layouts/footer.php'; ?>
