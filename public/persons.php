<?php
// public/persons.php - Emperors & Historical Figures (Plain Single-File PHP)
require_once __DIR__ . '/db.php';
$db = getDB();
$currentUser = $_SESSION['user'] ?? null;
$isResearcher = in_array($currentUser['role'] ?? '', ['researcher', 'admin']);

// 1. HANDLE ADD EMPEROR / FIGURE FORM (नया शासक जोड़ें)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_person') {
    if (!$isResearcher) {
        die("Unauthorized access. Researchers only.");
    }

    $name = trim($_POST['name'] ?? '');
    $alt_names = trim($_POST['alt_names'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $birth_year = !empty($_POST['birth_year']) ? (int)$_POST['birth_year'] : null;
    $death_year = !empty($_POST['death_year']) ? (int)$_POST['death_year'] : null;
    $portrait_url = trim($_POST['portrait_url'] ?? '/assets/img/persons/ashoka.png');
    $bio = trim($_POST['bio'] ?? '');

    if (!empty($name) && !empty($role)) {
        $stmt = $db->prepare("INSERT INTO persons (name, alt_names, birth_year, death_year, role, bio, portrait_url, created_at)
                              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $alt_names, $birth_year, $death_year, $role, $bio, $portrait_url]);
        $_SESSION['flash_success'] = "शासक / व्यक्तित्व '" . htmlspecialchars($name) . "' सफलतापूर्वक जुड़ गया!";
        header("Location: /persons.php");
        exit;
    }
}

// 2. FETCH ALL HISTORICAL PERSONS (डेटाबेस से सभी शासक लाएं)
$stmt = $db->query("SELECT * FROM persons ORDER BY birth_year ASC");
$persons = $stmt->fetchAll();

$pageTitle = "Emperors & Historical Figures (महान शासक और व्यक्तित्व)";
require_once __DIR__ . '/header.php';
?>

<!-- Title & Action -->
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
        <?php if ($isResearcher): ?>
            <div>
                <button onclick="toggleAddPersonDrawer()" class="btn btn-primary" style="padding: 10px 22px;">
                    + Add Emperor / Figure (नया शासक जोड़ें)
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Person Drawer (Hidden by default) -->
<?php if ($isResearcher): ?>
    <div id="add-person-drawer" style="display: none; background: #eff6ff; border: 2px solid #93c5fd; border-radius: var(--radius-lg); padding: 28px; margin-bottom: 32px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 20px; color: #1e3a8a; margin: 0;">👑 Add New Emperor / Figure (नया शासक जोड़ने का फॉर्म)</h3>
            <button onclick="toggleAddPersonDrawer()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #1e3a8a;">✕</button>
        </div>

        <form action="/persons.php" method="POST">
            <input type="hidden" name="action" value="add_person">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Full Name (नाम) *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Akbar, Babur, Sher Shah Suri" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Alternate Titles (उपाधियाँ व अन्य नाम)</label>
                    <input type="text" name="alt_names" class="form-control" placeholder="e.g. Akbar the Great, Devanampriya">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label">Imperial Role / Title (उपाधि व पद) *</label>
                <input type="text" name="role" class="form-control" placeholder="e.g. Third Mughal Emperor, Founder of Maurya Empire" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Birth Year (CE or negative for BCE)</label>
                    <input type="number" name="birth_year" class="form-control" placeholder="1542">
                </div>
                <div class="form-group">
                    <label class="form-label">Death Year (CE or negative for BCE)</label>
                    <input type="number" name="death_year" class="form-control" placeholder="1605">
                </div>
                <div class="form-group">
                    <label class="form-label">Portrait Image Path</label>
                    <input type="text" name="portrait_url" class="form-control" value="/assets/img/persons/ashoka.png">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Biography & Achievements (जीवन परिचय व कार्य) *</label>
                <textarea name="bio" class="form-control" rows="4" placeholder="Detailed bio of the ruler..." required></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="toggleAddPersonDrawer()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">Publish Figure (शासक जोड़ें)</button>
            </div>
        </form>
    </div>

    <script>
    function toggleAddPersonDrawer() {
        var d = document.getElementById('add-person-drawer');
        d.style.display = (d.style.display === 'none' || d.style.display === '') ? 'block' : 'none';
        if (d.style.display === 'block') d.scrollIntoView({ behavior: 'smooth' });
    }
    </script>
<?php endif; ?>

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
                <?= $p['birth_year'] ? $p['birth_year'] . ' CE' : '' ?> – <?= $p['death_year'] ? $p['death_year'] . ' CE' : '' ?>
            </span>

            <h3 style="font-size: 18px; margin-bottom: 4px;">
                <a href="/person.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
            </h3>

            <div style="font-size: 12.5px; font-weight: 600; color: var(--accent-gold); margin-bottom: 12px;">
                <?= htmlspecialchars($p['role']) ?>
            </div>

            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 18px; text-align: left; flex: 1;">
                <?= htmlspecialchars(substr($p['bio'] ?? '', 0, 130)) ?>...
            </p>

            <a href="/person.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" style="width: 100%;">
                View Life & Heritage →
            </a>
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

<?php require_once __DIR__ . '/footer.php'; ?>
