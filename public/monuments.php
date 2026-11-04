<?php
// public/monuments.php - Famous Historical Monuments (Plain Single-File PHP)
require_once __DIR__ . '/db.php';
$db = getDB();
$currentUser = $_SESSION['user'] ?? null;
$isResearcher = in_array($currentUser['role'] ?? '', ['researcher', 'admin']);

// 1. HANDLE ADD MONUMENT FORM SUBMISSION (अगर कोई नया स्मारक जोड़े)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_monument') {
    if (!$isResearcher) {
        die("Unauthorized access. Researchers only.");
    }

    $name = trim($_POST['name'] ?? '');
    $built_by = trim($_POST['built_by'] ?? '');
    $built_year = !empty($_POST['built_year']) ? (int)$_POST['built_year'] : 1600;
    $built_year_text = trim($_POST['built_year_text'] ?? 'Historical Era');
    $location_text = trim($_POST['location_text'] ?? 'India');
    $era_label = trim($_POST['era_label'] ?? 'Mughal / Classical');
    $style = trim($_POST['architectural_style'] ?? 'Indo-Islamic');
    $image_url = trim($_POST['image_url'] ?? '/assets/img/monuments/taj_mahal.jpg');
    $summary = trim($_POST['summary'] ?? '');
    $history = trim($_POST['history'] ?? $summary);
    $visiting_info = trim($_POST['visiting_info'] ?? 'Open daily sunrise to sunset.');
    $highlights = !empty($_POST['highlights']) ? json_encode(array_filter(array_map('trim', explode(',', $_POST['highlights'])))) : '[]';

    if (!empty($name) && !empty($built_by)) {
        $stmt = $db->prepare("INSERT INTO monuments (name, built_by, built_year, built_year_text, location_text, era_label, architectural_style, image_url, summary, history, visiting_info, highlights) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $built_by, $built_year, $built_year_text, $location_text, $era_label, $style, $image_url, $summary, $history, $visiting_info, $highlights]);
        $_SESSION['flash_success'] = "नया स्मारक '" . htmlspecialchars($name) . "' सफलतापूर्वक जुड़ गया!";
        header("Location: /monuments.php");
        exit;
    }
}

// 2. FETCH ALL MONUMENTS DIRECTLY (डेटाबेस से सभी स्मारक लाएं)
$stmt = $db->query("SELECT * FROM monuments ORDER BY built_year DESC");
$monuments = $stmt->fetchAll();

$pageTitle = "Famous Historical Monuments (ऐतिहासिक स्मारक)";
require_once __DIR__ . '/header.php';
?>

<!-- Title & Header Strip -->
<div style="margin-bottom: 28px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div class="hero-pill">
                🏰 Living Architectural Heritage · भारत के ऐतिहासिक स्मारक
            </div>
            <h1 style="font-size: 32px; color: var(--text-main); margin-bottom: 6px;">Famous Historical Monuments of India</h1>
            <p style="color: var(--text-muted); font-size: 15px; max-width: 760px;">
                ताजमहल, लाल किला, हुमायूँ का मकबरा, फतेहपुर सीकरी और नालंदा विश्वविद्यालय — जानें किसने बनवाया, कब बना, और इनका क्या इतिहास है।
            </p>
        </div>
        <?php if ($isResearcher): ?>
            <div>
                <button onclick="toggleAddForm()" class="btn btn-primary" style="padding: 10px 22px;">
                    + Add New Monument (नया स्मारक जोड़ें)
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Monument Form Drawer (Hidden by default, opens when clicking + Add New Monument) -->
<?php if ($isResearcher): ?>
    <div id="add-monument-drawer" style="display: none; background: #fffbeb; border: 2px solid #fdba74; border-radius: var(--radius-lg); padding: 28px; margin-bottom: 32px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 20px; color: #7c2d12; margin: 0;">🏰 Add New Monument (नया स्मारक जोड़ने का फॉर्म)</h3>
            <button onclick="toggleAddForm()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #78350f;">✕</button>
        </div>

        <form action="/monuments.php" method="POST">
            <input type="hidden" name="action" value="add_monument">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Monument Name (स्मारक का नाम) *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Agra Fort, Qutub Minar" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Built By / Builder (निर्माता) *</label>
                    <input type="text" name="built_by" class="form-control" placeholder="e.g. Emperor Akbar, Shah Jahan" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Year (CE)</label>
                    <input type="number" name="built_year" class="form-control" placeholder="1571">
                </div>
                <div class="form-group">
                    <label class="form-label">Year Text (e.g. 1571 – 1575 CE)</label>
                    <input type="text" name="built_year_text" class="form-control" placeholder="1571 – 1575 CE" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Location (e.g. Agra, Delhi)</label>
                    <input type="text" name="location_text" class="form-control" placeholder="Agra, Uttar Pradesh" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Architectural Style</label>
                    <input type="text" name="architectural_style" class="form-control" value="Indo-Islamic Architectural Marvel">
                </div>
                <div class="form-group">
                    <label class="form-label">Photo Image URL</label>
                    <input type="text" name="image_url" class="form-control" value="/assets/img/monuments/taj_mahal.jpg">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label">Short Summary (संक्षिप्त परिचय) *</label>
                <textarea name="summary" class="form-control" rows="2" placeholder="Quick 2-line summary..." required></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label">Detailed Story & History (विस्तृत इतिहास) *</label>
                <textarea name="history" class="form-control" rows="4" placeholder="Full historical story of the monument..." required></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Key Highlights (comma separated)</label>
                <input type="text" name="highlights" class="form-control" placeholder="Red Sandstone, UNESCO Site, Buland Darwaza">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="toggleAddForm()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">Publish Monument (स्मारक जोड़ें)</button>
            </div>
        </form>
    </div>

    <script>
    function toggleAddForm() {
        var drawer = document.getElementById('add-monument-drawer');
        drawer.style.display = (drawer.style.display === 'none' || drawer.style.display === '') ? 'block' : 'none';
        if (drawer.style.display === 'block') {
            drawer.scrollIntoView({ behavior: 'smooth' });
        }
    }
    </script>
<?php endif; ?>

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
        <?php $highlightsArr = json_decode($mon['highlights'] ?? '[]', true) ?: []; ?>
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
                    <a href="/monument.php?id=<?= $mon['id'] ?>"><?= htmlspecialchars($mon['name']) ?></a>
                </h3>

                <div style="font-size: 13px; color: var(--text-body); margin-bottom: 8px;">
                    <strong>Built By / निर्माता:</strong> <?= htmlspecialchars($mon['built_by']) ?>
                </div>

                <p class="clean-card-desc" style="font-size: 13.5px; color: var(--text-muted); line-height: 1.5; margin-bottom: 12px;">
                    <?= htmlspecialchars($mon['summary']) ?>
                </p>

                <?php if (!empty($highlightsArr)): ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px;">
                        <?php foreach (array_slice($highlightsArr, 0, 3) as $hl): ?>
                            <span class="clean-pill" style="font-size: 11.5px; padding: 2px 8px;">✓ <?= htmlspecialchars($hl) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="clean-card-footer" style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: var(--text-faint);"><?= htmlspecialchars($mon['architectural_style'] ?? 'Architecture') ?></span>
                    <a href="/monument.php?id=<?= $mon['id'] ?>" class="btn btn-primary btn-sm">Explore Monument →</a>
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

<?php require_once __DIR__ . '/footer.php'; ?>
