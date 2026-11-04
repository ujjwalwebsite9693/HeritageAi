<?php
// public/header.php - Shared Navigation & Header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUser = $_SESSION['user'] ?? null;
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>Bharat Heritage · भारत धरोहर</title>

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Inter:wght@400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Leaflet.js -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- vis-timeline -->
    <link rel="stylesheet" href="https://unpkg.com/vis-timeline@7.7.3/styles/vis-timeline-graph2d.min.css">
    <script src="https://unpkg.com/vis-timeline@7.7.3/standalone/umd/vis-timeline-graph2d.min.js"></script>

    <!-- vis-network -->
    <script src="https://unpkg.com/vis-network@9.1.9/standalone/umd/vis-network.min.js"></script>

    <!-- OpenSeadragon -->
    <script src="https://cdn.jsdelivr.net/npm/openseadragon@4.1.1/build/openseadragon/openseadragon.min.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= time() ?>">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="/index.php" class="brand-area">
                    <div class="brand-emblem">🏛️</div>
                    <div class="brand-text">
                        <span class="brand-name">Bharat Heritage · भारत धरोहर</span>
                        <span class="brand-tagline">Emperors · Monuments · Manuscripts</span>
                    </div>
                </a>

                <nav class="main-nav">
                    <a href="/index.php" class="nav-link <?= in_array($currentUri, ['/', '/index.php', '']) ? 'active' : '' ?>">Home</a>
                    <a href="/monuments.php" class="nav-link <?= str_contains($currentUri, 'monument') ? 'active' : '' ?>">🏰 Monuments</a>
                    <a href="/persons.php" class="nav-link <?= str_contains($currentUri, 'person') ? 'active' : '' ?>">👑 Emperors</a>
                    <a href="/compare.php" class="nav-link <?= str_contains($currentUri, 'compare') ? 'active' : '' ?>">⚖️ Compare</a>
                    <a href="/assistant.php" class="nav-link <?= str_contains($currentUri, 'assistant') ? 'active' : '' ?>" style="color: #7c2d12; font-weight: 700;">🤖 Acharya AI</a>
                    <a href="/search.php" class="nav-link <?= str_contains($currentUri, 'search') || str_contains($currentUri, 'manuscript') ? 'active' : '' ?>">📜 Manuscripts</a>
                    <a href="/map.php" class="nav-link <?= str_contains($currentUri, 'map') ? 'active' : '' ?>">🗺️ Map</a>
                    <a href="/timeline.php" class="nav-link <?= str_contains($currentUri, 'timeline') ? 'active' : '' ?>">⏳ Timeline</a>
                    <a href="/graph.php" class="nav-link <?= str_contains($currentUri, 'graph') ? 'active' : '' ?>">🔗 Connections</a>
                    <a href="/quiz.php" class="nav-link <?= str_contains($currentUri, 'quiz') ? 'active' : '' ?>" style="color: var(--accent-gold); font-weight: 700;">🎯 Quiz</a>
                </nav>

                <div class="header-actions">
                    <button onclick="openPitchModal()" class="btn btn-sm" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; font-weight: 700; box-shadow: 0 2px 6px rgba(217,119,6,0.3); display: flex; align-items: center; gap: 5px;">
                        <span>✨</span> Pitch Walkthrough
                    </button>
                    <?php if ($currentUser): ?>
                        <?php if (in_array($currentUser['role'] ?? '', ['researcher', 'admin'])): ?>
                            <a href="/admin.php" class="btn btn-primary btn-sm" style="background: #0f172a; border-color: #0f172a;">
                                ⚙️ Admin Panel
                            </a>
                        <?php endif; ?>
                        <span class="badge badge-verified" style="font-size: 11px; padding: 5px 12px;">
                            <?= htmlspecialchars(substr($currentUser['name'] ?? 'User', 0, 14)) ?>
                        </span>
                        <a href="/logout.php" class="btn btn-outline btn-sm">Sign Out</a>
                    <?php else: ?>
                        <a href="/login.php" class="btn btn-secondary btn-sm">Sign In</a>
                        <a href="/register.php" class="btn btn-primary btn-sm">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="container flash-container">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="flash-alert flash-success">
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-weight:bold;color:inherit;">✕</button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="flash-alert flash-error">
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-weight:bold;color:inherit;">✕</button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
    </div>

    <main class="site-content">
        <div class="container">
