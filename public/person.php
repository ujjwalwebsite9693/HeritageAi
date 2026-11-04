<?php
// public/person.php - Emperor / Historical Figure Detail & Edit (Plain Single-File PHP)
require_once __DIR__ . '/db.php';
$db = getDB();
$currentUser = $_SESSION['user'] ?? null;
$isResearcher = in_array($currentUser['role'] ?? '', ['researcher', 'admin']);

$id = (int)($_GET['id'] ?? 0);

// 1. HANDLE EDIT OR DELETE FORM (अगर कोई शासक को एडिट या डिलीट करे)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!$isResearcher) {
        die("Unauthorized access. Researchers only.");
    }

    if ($_POST['action'] === 'delete') {
        $stmt = $db->prepare("DELETE FROM persons WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash_success'] = "Historical figure removed successfully.";
        header("Location: /persons.php");
        exit;
    }

    if ($_POST['action'] === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $alt_names = trim($_POST['alt_names'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $birth_year = !empty($_POST['birth_year']) ? (int)$_POST['birth_year'] : null;
        $death_year = !empty($_POST['death_year']) ? (int)$_POST['death_year'] : null;
        $portrait_url = trim($_POST['portrait_url'] ?? '/assets/img/persons/ashoka.png');
        $bio = trim($_POST['bio'] ?? '');

        $stmt = $db->prepare("UPDATE persons SET name=?, alt_names=?, birth_year=?, death_year=?, role=?, bio=?, portrait_url=? WHERE id=?");
        $stmt->execute([$name, $alt_names, $birth_year, $death_year, $role, $bio, $portrait_url, $id]);
        $_SESSION['flash_success'] = "Figure '" . htmlspecialchars($name) . "' updated successfully!";
        header("Location: /person.php?id=" . $id);
        exit;
    }
}

// 2. FETCH PERSON DETAILS DIRECTLY (शासक का डेटा लाएं)
$stmt = $db->prepare("SELECT * FROM persons WHERE id = ?");
$stmt->execute([$id]);
$person = $stmt->fetch();

if (!$person) {
    header("Location: /persons.php");
    exit;
}

// 3. FETCH MONUMENTS BUILT BY THIS EMPEROR (इस शासक द्वारा बनाए गए स्मारक)
$monStmt = $db->prepare("SELECT * FROM monuments WHERE person_id = ? OR built_by LIKE ?");
$monStmt->execute([$id, "%" . $person['name'] . "%"]);
$monuments = $monStmt->fetchAll();

// 4. FETCH RELATIONS (संबंधी व दरबारी)
$relStmt = $db->prepare("SELECT pr.*, 
                                 pa.name as person_a_name, pa.role as person_a_role,
                                 pb.name as person_b_name, pb.role as person_b_role
                          FROM person_relations pr
                          JOIN persons pa ON pr.person_a = pa.id
                          JOIN persons pb ON pr.person_b = pb.id
                          WHERE pr.person_a = ? OR pr.person_b = ?");
$relStmt->execute([$id, $id]);
$rawRelations = $relStmt->fetchAll();

$relations = [];
foreach ($rawRelations as $r) {
    $isA = ($r['person_a'] == $id);
    $relations[] = [
        'id' => (int)$r['id'],
        'related_person_id' => (int)($isA ? $r['person_b'] : $r['person_a']),
        'related_person_name' => $isA ? $r['person_b_name'] : $r['person_a_name'],
        'related_person_role' => $isA ? $r['person_b_role'] : $r['person_a_role'],
        'relationship_type' => str_replace('-', ' ', $r['relation_type'] ?? 'ally'),
        'notes' => $r['notes'] ?? ''
    ];
}

$pageTitle = $person['name'] . " — Historical Figure";
require_once __DIR__ . '/header.php';
?>

<!-- Person Header Card -->
<div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 28px;">
    <div style="display: flex; gap: 28px; align-items: flex-start; flex-wrap: wrap;">
        <img src="<?= htmlspecialchars($person['portrait_url'] ?: '/assets/img/persons/ashoka.png') ?>" alt="<?= htmlspecialchars($person['name']) ?>" style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid var(--border-light); object-fit: cover; box-shadow: var(--shadow-sm);">
        
        <div style="flex: 1; min-width: 300px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span class="badge" style="background: var(--primary-light); color: var(--primary);">
                    <?= htmlspecialchars($person['role']) ?>
                </span>
                <span class="badge badge-score">
                    <?= $person['birth_year'] ? $person['birth_year'] . ' CE' : '' ?> – <?= $person['death_year'] ? $person['death_year'] . ' CE' : '' ?>
                </span>
            </div>

            <h1 style="font-size: 32px; color: var(--text-main); margin-bottom: 6px;"><?= htmlspecialchars($person['name']) ?></h1>
            
            <?php if (!empty($person['alt_names'])): ?>
                <div style="font-size: 13.5px; color: var(--text-muted); margin-bottom: 12px;">
                    <strong>Titles & Aliases (उपाधियाँ व अन्य नाम):</strong> <?= htmlspecialchars($person['alt_names']) ?>
                </div>
            <?php endif; ?>

            <p id="narrate-bio" style="font-size: 15px; color: var(--text-body); line-height: 1.7; margin-bottom: 16px;">
                <?= htmlspecialchars($person['bio']) ?>
            </p>

            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button id="btn-audio-narrate" class="btn btn-secondary btn-sm" onclick="toggleAudioNarration()">
                    🔊 <span id="audio-btn-text">Listen Biography (जीवनी सुनें)</span>
                </button>
                <a href="/compare.php?mode=person&p1=<?= $person['id'] ?>" class="btn btn-secondary btn-sm">
                    ⚖️ Compare
                </a>
                <button class="btn btn-secondary btn-sm" onclick="openQrModal()">📱 Mobile QR</button>
                <button class="btn btn-secondary btn-sm" onclick="window.print()">🖨️ Print</button>
                <?php if ($isResearcher): ?>
                    <button onclick="toggleEditPersonDrawer()" class="btn btn-primary btn-sm">✏️ Edit Figure</button>
                    <form action="/person.php?id=<?= $person['id'] ?>" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this historical figure?');">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: #fca5a5;">🗑️ Delete</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <a href="/persons.php" class="btn btn-secondary btn-sm">← Back to All Emperors</a>
        </div>
    </div>
</div>

<!-- Edit Drawer (Hidden by default) -->
<?php if ($isResearcher): ?>
    <div id="edit-person-drawer" style="display: none; background: #eff6ff; border: 2px solid #93c5fd; border-radius: var(--radius-lg); padding: 28px; margin-bottom: 32px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 20px; color: #1e3a8a; margin: 0;">✏️ Edit Emperor / Figure (शासक संपादन)</h3>
            <button onclick="toggleEditPersonDrawer()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #1e3a8a;">✕</button>
        </div>

        <form action="/person.php?id=<?= $person['id'] ?>" method="POST">
            <input type="hidden" name="action" value="edit">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($person['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Alternate Titles</label>
                    <input type="text" name="alt_names" class="form-control" value="<?= htmlspecialchars($person['alt_names'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label">Role / Title *</label>
                <input type="text" name="role" class="form-control" value="<?= htmlspecialchars($person['role']) ?>" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Birth Year</label>
                    <input type="number" name="birth_year" class="form-control" value="<?= htmlspecialchars($person['birth_year'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Death Year</label>
                    <input type="number" name="death_year" class="form-control" value="<?= htmlspecialchars($person['death_year'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Portrait URL</label>
                    <input type="text" name="portrait_url" class="form-control" value="<?= htmlspecialchars($person['portrait_url']) ?>">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Biography *</label>
                <textarea name="bio" class="form-control" rows="5" required><?= htmlspecialchars($person['bio']) ?></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="toggleEditPersonDrawer()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">Save Changes (बदलाव सुरक्षित करें)</button>
            </div>
        </form>
    </div>

    <script>
    function toggleEditPersonDrawer() {
        var d = document.getElementById('edit-person-drawer');
        d.style.display = (d.style.display === 'none' || d.style.display === '') ? 'block' : 'none';
        if (d.style.display === 'block') d.scrollIntoView({ behavior: 'smooth' });
    }
    </script>
<?php endif; ?>

<!-- Monuments Built by this Emperor -->
<?php if (!empty($monuments)): ?>
    <div style="margin-bottom: 32px;">
        <div style="margin-bottom: 16px;">
            <span class="badge" style="background: var(--accent-gold-light); color: var(--accent-gold); margin-bottom: 6px;">
                🏰 Masterpieces Commissioned
            </span>
            <h3 style="font-size: 22px; color: var(--text-main);">Architectural Monuments Built by <?= htmlspecialchars($person['name']) ?></h3>
        </div>
        <div class="clean-grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));">
            <?php foreach ($monuments as $m): ?>
                <div class="clean-manuscript-card">
                    <div class="clean-card-img" style="height: 160px; background-image: url('<?= htmlspecialchars($m['image_url']) ?>');">
                        <span class="clean-card-pill"><?= htmlspecialchars($m['built_year_text']) ?></span>
                    </div>
                    <div class="clean-card-body">
                        <h4 class="clean-card-title">
                            <a href="/monument.php?id=<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></a>
                        </h4>
                        <p class="clean-card-desc"><?= htmlspecialchars($m['summary']) ?></p>
                        <div class="clean-card-footer">
                            <span>📍 <?= htmlspecialchars($m['location_text']) ?></span>
                            <a href="/monument.php?id=<?= $m['id'] ?>" class="btn btn-primary btn-sm">Explore Monument →</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Relationships -->
<?php if (!empty($relations)): ?>
    <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-xs); margin-bottom: 28px;">
        <h3 style="font-size: 18px; margin-bottom: 14px; border-bottom: 1px solid var(--border-light); padding-bottom: 8px;">
            🤝 Historical Allies, Courtiers & Lineages (संबंधी व दरबारी)
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px;">
            <?php foreach ($relations as $rel): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface-alt); padding: 12px 16px; border-radius: 6px;">
                    <div>
                        <span class="badge" style="background: var(--primary-light); color: var(--primary); font-size: 10.5px;">
                            <?= htmlspecialchars(ucfirst($rel['relationship_type'])) ?>
                        </span>
                        <div style="margin-top: 4px;">
                            <a href="/person.php?id=<?= $rel['related_person_id'] ?>" style="font-weight: 700; color: var(--text-main);">
                                <?= htmlspecialchars($rel['related_person_name'] ?? 'Historical Figure') ?>
                            </a>
                        </div>
                    </div>
                    <a href="/person.php?id=<?= $rel['related_person_id'] ?>" class="btn btn-outline btn-sm">View →</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<script>
var isSpeaking = false;
function toggleAudioNarration() {
    if (!('speechSynthesis' in window)) {
        alert("Text-to-speech is not supported in this browser.");
        return;
    }

    if (isSpeaking) {
        window.speechSynthesis.cancel();
        isSpeaking = false;
        document.getElementById('audio-btn-text').innerText = "Listen Biography (जीवनी सुनें)";
        return;
    }

    var bio = document.getElementById('narrate-bio').innerText;
    var text = "<?= addslashes($person['name']) ?>. <?= addslashes($person['role']) ?>. " + bio;

    var utterance = new SpeechSynthesisUtterance(text);
    utterance.rate = 0.95;

    utterance.onend = function() {
        isSpeaking = false;
        document.getElementById('audio-btn-text').innerText = "Listen Biography (जीवनी सुनें)";
    };

    window.speechSynthesis.speak(utterance);
    isSpeaking = true;
    document.getElementById('audio-btn-text').innerText = "⏹️ Stop Audio (रोकें)";
}

function openQrModal() {
    var modal = document.getElementById('qr-modal');
    if (modal) modal.style.display = 'flex';
}
function closeQrModal() {
    var modal = document.getElementById('qr-modal');
    if (modal) modal.style.display = 'none';
}
</script>

<!-- QR Code Modal -->
<div id="qr-modal" style="display: none; position: fixed; top:0; left:0; right:0; bottom:0; background: rgba(15,23,42,0.7); z-index: 2000; align-items: center; justify-content: center; padding: 20px;" onclick="if(event.target===this) closeQrModal()">
    <div style="background: #fff; border-radius: var(--radius-lg); padding: 28px; max-width: 360px; width: 100%; text-align: center; box-shadow: var(--shadow-lg);">
        <h3 style="font-size: 18px; margin-bottom: 6px; color: var(--text-main);">📱 Scan on Mobile</h3>
        <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 16px;">Scan this QR code with your smartphone camera to open this emperor dossier live!</p>
        <div style="background: #f8fafc; padding: 16px; border-radius: 12px; display: inline-block; border: 1px solid var(--border-light);">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode('http://' . ($_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000') . '/person.php?id=' . $person['id']) ?>" 
                 alt="QR Code" style="width: 200px; height: 200px; display: block;">
        </div>
        <div style="margin-top: 18px;">
            <button onclick="closeQrModal()" class="btn btn-secondary btn-sm" style="width: 100%;">Close</button>
        </div>
    </div>
</div>
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
