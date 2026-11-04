<?php
// public/monument.php - Monument Detail & Management (Plain Single-File PHP)
require_once __DIR__ . '/db.php';
$db = getDB();
$currentUser = $_SESSION['user'] ?? null;
$isResearcher = in_array($currentUser['role'] ?? '', ['researcher', 'admin']);

$id = (int)($_GET['id'] ?? 0);

// 1. HANDLE EDIT OR DELETE FORM (अगर कोई स्मारक अपडेट या डिलीट करे)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!$isResearcher) {
        die("Unauthorized access. Researchers only.");
    }

    if ($_POST['action'] === 'delete') {
        $stmt = $db->prepare("DELETE FROM monuments WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash_success'] = "Monument deleted successfully.";
        header("Location: /monuments.php");
        exit;
    }

    if ($_POST['action'] === 'edit') {
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

        $stmt = $db->prepare("UPDATE monuments SET name=?, built_by=?, built_year=?, built_year_text=?, location_text=?, era_label=?, architectural_style=?, image_url=?, summary=?, history=?, visiting_info=?, highlights=? WHERE id=?");
        $stmt->execute([$name, $built_by, $built_year, $built_year_text, $location_text, $era_label, $style, $image_url, $summary, $history, $visiting_info, $highlights, $id]);
        $_SESSION['flash_success'] = "Monument '" . htmlspecialchars($name) . "' updated successfully!";
        header("Location: /monument.php?id=" . $id);
        exit;
    }
}

// 2. FETCH MONUMENT DETAILS (स्मारक का डेटा लाएं)
$stmt = $db->prepare("SELECT * FROM monuments WHERE id = ?");
$stmt->execute([$id]);
$monument = $stmt->fetch();

if (!$monument) {
    header("Location: /monuments.php");
    exit;
}

$highlightsArr = json_decode($monument['highlights'] ?? '[]', true) ?: [];

$pageTitle = $monument['name'] . " — Historical Monument";
require_once __DIR__ . '/header.php';
?>

<!-- Hero Banner -->
<div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 32px;">
    <div style="height: 360px; background-size: cover; background-position: center; position: relative; background-image: url('<?= htmlspecialchars($monument['image_url']) ?>');">
        <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(15,23,42,0.25) 0%, rgba(15,23,42,0.88) 100%); display: flex; flex-direction: column; justify-content: flex-end; padding: 36px;">
            <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                <span class="badge" style="background: var(--accent-gold); color: #fff;"><?= htmlspecialchars($monument['era_label']) ?></span>
                <span class="badge" style="background: rgba(255,255,255,0.25); color: #fff;"><?= htmlspecialchars($monument['built_year_text']) ?></span>
            </div>
            <h1 style="color: #ffffff; font-size: 36px; margin-bottom: 6px;"><?= htmlspecialchars($monument['name']) ?></h1>
            <div style="color: #f1f5f9; font-size: 15px;">
                📍 <?= htmlspecialchars($monument['location_text']) ?> · Commissioned by <strong><?= htmlspecialchars($monument['built_by']) ?></strong>
            </div>
        </div>
    </div>

    <!-- Quick Facts Strip & Action Controls -->
    <div style="background: var(--bg-surface-alt); padding: 18px 36px; border-bottom: 1px solid var(--border-light); display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between; align-items: center; font-size: 13.5px;">
        <div style="display: flex; flex-wrap: wrap; gap: 24px;">
            <div>
                <span style="color: var(--text-muted); display: block; font-size: 11px; text-transform: uppercase;">Builder / निर्माता</span>
                <strong><?= htmlspecialchars($monument['built_by']) ?></strong>
            </div>
            <div>
                <span style="color: var(--text-muted); display: block; font-size: 11px; text-transform: uppercase;">Construction Period / निर्माण काल</span>
                <strong><?= htmlspecialchars($monument['built_year_text']) ?></strong>
            </div>
            <div>
                <span style="color: var(--text-muted); display: block; font-size: 11px; text-transform: uppercase;">Style / शैली</span>
                <strong><?= htmlspecialchars($monument['architectural_style'] ?? 'Indo-Islamic') ?></strong>
            </div>
            <div>
                <span style="color: var(--text-muted); display: block; font-size: 11px; text-transform: uppercase;">Location / स्थान</span>
                <strong><?= htmlspecialchars($monument['location_text']) ?></strong>
            </div>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <button id="btn-audio-narrate" class="btn btn-secondary btn-sm" onclick="toggleAudioNarration()">
                🔊 <span id="audio-btn-text">Listen Story (कहानी सुनें)</span>
            </button>
            <a href="/compare.php?mode=monument&p1=<?= $monument['id'] ?>" class="btn btn-secondary btn-sm">
                ⚖️ Compare
            </a>
            <button class="btn btn-secondary btn-sm" onclick="openQrModal()">📱 Mobile QR</button>
            <button class="btn btn-secondary btn-sm" onclick="window.print()">🖨️ Print</button>
            <?php if ($isResearcher): ?>
                <button onclick="toggleEditDrawer()" class="btn btn-primary btn-sm">✏️ Edit Monument</button>
                <form action="/monument.php?id=<?= $monument['id'] ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this monument?');">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: #fca5a5;">🗑️ Delete</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Drawer (Hidden by default, shown when clicking ✏️ Edit) -->
<?php if ($isResearcher): ?>
    <div id="edit-monument-drawer" style="display: none; background: #fffbeb; border: 2px solid #fdba74; border-radius: var(--radius-lg); padding: 28px; margin-bottom: 32px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 20px; color: #7c2d12; margin: 0;">✏️ Edit Monument Details (स्मारक संपादन)</h3>
            <button onclick="toggleEditDrawer()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #78350f;">✕</button>
        </div>

        <form action="/monument.php?id=<?= $monument['id'] ?>" method="POST">
            <input type="hidden" name="action" value="edit">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Monument Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($monument['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Built By *</label>
                    <input type="text" name="built_by" class="form-control" value="<?= htmlspecialchars($monument['built_by']) ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Year (CE)</label>
                    <input type="number" name="built_year" class="form-control" value="<?= htmlspecialchars($monument['built_year'] ?? '1600') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Period Text</label>
                    <input type="text" name="built_year_text" class="form-control" value="<?= htmlspecialchars($monument['built_year_text']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location_text" class="form-control" value="<?= htmlspecialchars($monument['location_text']) ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Style</label>
                    <input type="text" name="architectural_style" class="form-control" value="<?= htmlspecialchars($monument['architectural_style'] ?? 'Indo-Islamic') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Image URL</label>
                    <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($monument['image_url']) ?>">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label">Summary *</label>
                <textarea name="summary" class="form-control" rows="2" required><?= htmlspecialchars($monument['summary']) ?></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label">Full History *</label>
                <textarea name="history" class="form-control" rows="5" required><?= htmlspecialchars($monument['history']) ?></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Highlights (comma separated)</label>
                <input type="text" name="highlights" class="form-control" value="<?= htmlspecialchars(implode(', ', $highlightsArr)) ?>">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="toggleEditDrawer()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">Save Changes (बदलाव सुरक्षित करें)</button>
            </div>
        </form>
    </div>

    <script>
    function toggleEditDrawer() {
        var d = document.getElementById('edit-monument-drawer');
        d.style.display = (d.style.display === 'none' || d.style.display === '') ? 'block' : 'none';
        if (d.style.display === 'block') d.scrollIntoView({ behavior: 'smooth' });
    }
    </script>
<?php endif; ?>

<!-- Overview and Details Grid -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 28px; align-items: start;">
    <!-- Left Column: History & Highlights -->
    <div>
        <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 28px; box-shadow: var(--shadow-xs); margin-bottom: 24px;">
            <h2 style="font-size: 20px; color: var(--text-main); margin-bottom: 12px;">Historical Overview (ऐतिहासिक परिचय)</h2>
            <p id="narrate-summary" style="font-size: 15.5px; color: var(--text-body); line-height: 1.8; margin-bottom: 20px;">
                <?= htmlspecialchars($monument['summary']) ?>
            </p>

            <h3 style="font-size: 17px; color: var(--text-main); margin-bottom: 10px;">The Story of Construction (निर्माण की कहानी)</h3>
            <p id="narrate-history" style="font-size: 14.5px; color: var(--text-muted); line-height: 1.8;">
                <?= nl2br(htmlspecialchars($monument['history'])) ?>
            </p>
        </div>

        <?php if (!empty($highlightsArr)): ?>
            <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-xs); margin-bottom: 24px;">
                <h3 style="font-size: 17px; color: var(--text-main); margin-bottom: 14px;">Key Architectural Highlights (मुख्य विशेषताएं)</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <?php foreach ($highlightsArr as $h): ?>
                        <div style="background: var(--bg-surface-alt); padding: 12px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                            <span style="color: var(--status-verified); font-weight: bold;">✓</span>
                            <?= htmlspecialchars($h) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Visiting Guide & Map Link -->
    <div>
        <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 22px; box-shadow: var(--shadow-xs); margin-bottom: 24px;">
            <h3 style="font-size: 16px; color: var(--text-main); margin-bottom: 10px;">🕒 Visiting Guide (दर्शन समय)</h3>
            <p style="font-size: 13.5px; color: var(--text-body); line-height: 1.6; margin-bottom: 14px;">
                <?= htmlspecialchars($monument['visiting_info'] ?? 'Open daily from sunrise to sunset.') ?>
            </p>
            <a href="/map.php" class="btn btn-secondary btn-sm" style="width: 100%;">View on Historical Map 🗺️</a>
        </div>

        <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 22px; box-shadow: var(--shadow-xs);">
            <h3 style="font-size: 16px; color: var(--text-main); margin-bottom: 10px;">📜 Historical Codices & Texts</h3>
            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 12px;">
                Find ancient decrees and manuscripts connected to this site and its royal court.
            </p>
            <a href="/search.php?q=<?= urlencode($monument['name']) ?>" class="btn btn-outline btn-sm" style="width: 100%;">Search Archive Manuscripts →</a>
        </div>
    </div>
</div>

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
        document.getElementById('audio-btn-text').innerText = "Listen Story (कहानी सुनें)";
        return;
    }

    var summary = document.getElementById('narrate-summary').innerText;
    var history = document.getElementById('narrate-history').innerText;
    var text = "<?= addslashes($monument['name']) ?>. Built by <?= addslashes($monument['built_by']) ?>. " + summary + " " + history;

    var utterance = new SpeechSynthesisUtterance(text);
    utterance.rate = 0.95;

    utterance.onend = function() {
        isSpeaking = false;
        document.getElementById('audio-btn-text').innerText = "Listen Story (कहानी सुनें)";
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
        <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 16px;">Scan this QR code with your smartphone camera to open this monument dossier live!</p>
        <div style="background: #f8fafc; padding: 16px; border-radius: 12px; display: inline-block; border: 1px solid var(--border-light);">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode('http://' . ($_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000') . '/monument.php?id=' . $monument['id']) ?>" 
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
