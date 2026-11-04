<?php
// public/admin.php - Archive & Heritage Control Center (Plain Single-File PHP)
require_once __DIR__ . '/db.php';
$db = getDB();
$currentUser = $_SESSION['user'] ?? null;

// Require researcher or admin role
if (!in_array($currentUser['role'] ?? '', ['researcher', 'admin'])) {
    header("Location: /login.php");
    exit;
}

// FETCH STATS DIRECTLY (डेटाबेस से सीधे आँकड़े लाएं)
$stats = [
    'manuscripts' => (int)$db->query("SELECT COUNT(*) FROM manuscripts WHERE status = 'published'")->fetchColumn(),
    'monuments' => (int)$db->query("SELECT COUNT(*) FROM monuments")->fetchColumn(),
    'persons' => (int)$db->query("SELECT COUNT(*) FROM persons")->fetchColumn(),
    'pending_links' => (int)$db->query("SELECT COUNT(*) FROM links WHERE status = 'suggested'")->fetchColumn(),
    'pending_jobs' => (int)$db->query("SELECT COUNT(*) FROM jobs WHERE status = 'queued'")->fetchColumn(),
    'users' => (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
];

$pageTitle = "Admin & Researcher Control Center (नियंत्रण केंद्र)";
require_once __DIR__ . '/header.php';
?>

<div style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <span class="badge badge-verified" style="margin-bottom: 6px;">ADMINISTRATIVE PORTAL · शोधकर्ता नियंत्रण केंद्र</span>
            <h1 style="font-size: 28px; color: var(--text-main);">Archive & Heritage Control Center</h1>
            <p style="color: var(--text-muted); font-size: 14px;">
                Manage monuments, emperors, manuscript deposits, background OCR/HTR workers, and historical link reviews.
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="/monuments.php" class="btn btn-primary btn-sm" style="background: var(--accent-gold); border-color: var(--accent-gold);">+ Add Monument 🏰</a>
            <a href="/persons.php" class="btn btn-primary btn-sm" style="background: #1e3a8a; border-color: #1e3a8a;">+ Add Emperor 👑</a>
            <a href="/upload.php" class="btn btn-primary btn-sm">+ Upload Codex 📜</a>
        </div>
    </div>
</div>

<!-- Stats Ribbon Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $stats['manuscripts'] ?></div>
        <div class="stat-label">Manuscripts</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['monuments'] ?></div>
        <div class="stat-label">Monuments</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['persons'] ?></div>
        <div class="stat-label">Rulers & Figures</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: var(--accent-gold);"><?= $stats['pending_links'] ?></div>
        <div class="stat-label">Pending Links</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: <?= $stats['pending_jobs'] > 0 ? 'var(--status-draft)' : 'var(--status-verified)' ?>;">
            <?= $stats['pending_jobs'] ?>
        </div>
        <div class="stat-label">Queue Jobs</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['users'] ?></div>
        <div class="stat-label">Registered Users</div>
    </div>
</div>

<!-- Administrative Tools Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 32px;">
    <!-- 1. Add / Manage Monuments -->
    <a href="/monuments.php" class="quick-feature-box" style="text-align: left; text-decoration: none; border-left: 4px solid var(--accent-gold);">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <div style="font-size: 24px;">🏰</div>
            <div>
                <h3 style="font-size: 16px; color: var(--text-main); margin: 0;">Monument Registry</h3>
                <span style="font-size: 11px; color: var(--accent-gold); font-weight: bold;">Manage Architecture</span>
            </div>
        </div>
        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">
            Add iconic palaces, forts, and tombs with photos, builder details, construction era, and visitor guides.
        </p>
    </a>

    <!-- 2. Add / Manage Emperors & Figures -->
    <a href="/persons.php" class="quick-feature-box" style="text-align: left; text-decoration: none; border-left: 4px solid #1e3a8a;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <div style="font-size: 24px;">👑</div>
            <div>
                <h3 style="font-size: 16px; color: var(--text-main); margin: 0;">Emperors & Figures</h3>
                <span style="font-size: 11px; color: #1e3a8a; font-weight: bold;">Manage Rulers</span>
            </div>
        </div>
        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">
            Register monarchs, prime ministers, philosophers, and court scholars with portraits and biographies.
        </p>
    </a>

    <!-- 3. Upload Manuscript -->
    <a href="/upload.php" class="quick-feature-box" style="text-align: left; text-decoration: none; border-left: 4px solid var(--primary);">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <div style="font-size: 24px;">📄</div>
            <div>
                <h3 style="font-size: 16px; color: var(--text-main); margin: 0;">Deposit Manuscript</h3>
                <span style="font-size: 11px; color: var(--text-faint); text-transform: uppercase;">Folio Ingestion</span>
            </div>
        </div>
        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">
            Upload high-res scans (JPG, PNG, PDF), set codicological metadata, and queue automated OCR.
        </p>
    </a>

    <!-- 4. Interactive Quiz Challenge -->
    <a href="/quiz.php" class="quick-feature-box" style="text-align: left; text-decoration: none; border-left: 4px solid #10b981;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <div style="font-size: 24px;">🎯</div>
            <div>
                <h3 style="font-size: 16px; color: var(--text-main); margin: 0;">History Quiz Challenge</h3>
                <span style="font-size: 11px; color: #10b981; font-weight: bold;">Interactive Learning</span>
            </div>
        </div>
        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">
            Review interactive quiz questions and test heritage knowledge on emperors and monuments.
        </p>
    </a>

    <!-- 5. User Roles (Admins only) -->
    <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
        <a href="/admin/users" class="quick-feature-box" style="text-align: left; text-decoration: none;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <div style="font-size: 24px;">👥</div>
                <div>
                    <h3 style="font-size: 16px; color: var(--text-main); margin: 0;">User Roles & Access</h3>
                    <span style="font-size: 11px; color: var(--text-faint); text-transform: uppercase;">Permissions</span>
                </div>
            </div>
            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">
                Manage researcher credentials, reviewer privileges, and user access levels.
            </p>
        </a>
    <?php endif; ?>

    <!-- 6. Developer API Documentation -->
    <a href="/api-docs" class="quick-feature-box" style="text-align: left; text-decoration: none;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <div style="font-size: 24px;">📡</div>
            <div>
                <h3 style="font-size: 16px; color: var(--text-main); margin: 0;">REST API & OpenAPI</h3>
                <span style="font-size: 11px; color: var(--text-faint); text-transform: uppercase;">Developer Endpoints</span>
            </div>
        </div>
        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">
            Programmatic access to manuscripts, monuments, persons, IIIF manifests, and GeoJSON endpoints.
        </p>
    </a>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
