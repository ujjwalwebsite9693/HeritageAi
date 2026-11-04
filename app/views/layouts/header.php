<?php
// app/views/layouts/header.php
require_once __DIR__ . '/../../helpers/Auth.php';
$currentUser = Auth::user();
$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>Bharat Heritage Archive · भारतीय धरोहर</title>

    <!-- Google Web Fonts: Cinzel, Inter, and Noto Sans Devanagari -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Inter:wght@400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Leaflet.js (Historical Cartography) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- vis-timeline (Interactive Zoomable Timeline) -->
    <link rel="stylesheet" href="https://unpkg.com/vis-timeline@7.7.3/styles/vis-timeline-graph2d.min.css">
    <script src="https://unpkg.com/vis-timeline@7.7.3/standalone/umd/vis-timeline-graph2d.min.js"></script>

    <!-- vis-network (Connection Graph & Genealogy Trees) -->
    <script src="https://unpkg.com/vis-network@9.1.9/standalone/umd/vis-network.min.js"></script>

    <!-- OpenSeadragon (Deep Zoom High-Resolution Manuscript Viewer) -->
    <script src="https://cdn.jsdelivr.net/npm/openseadragon@4.1.1/build/openseadragon/openseadragon.min.js"></script>

    <!-- Core Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= time() ?>">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="/" class="brand-area">
                    <div class="brand-emblem">🏛️</div>
                    <div class="brand-text">
                        <span class="brand-name">Bharat Heritage · भारत धरोहर</span>
                        <span class="brand-tagline">Emperors · Monuments · Manuscripts</span>
                    </div>
                </a>

                <nav class="main-nav">
                    <a href="/" class="nav-link <?= $currentUri === '/' ? 'active' : '' ?>">Home</a>
                    <a href="/monuments" class="nav-link <?= str_starts_with($currentUri, '/monuments') || str_starts_with($currentUri, '/monument') ? 'active' : '' ?>">🏰 Monuments</a>
                    <a href="/persons" class="nav-link <?= str_starts_with($currentUri, '/persons') || str_starts_with($currentUri, '/person') ? 'active' : '' ?>">👑 Emperors</a>
                    <a href="/search" class="nav-link <?= str_starts_with($currentUri, '/search') || str_starts_with($currentUri, '/manuscript') ? 'active' : '' ?>">📜 Manuscripts</a>
                    <a href="/map" class="nav-link <?= str_starts_with($currentUri, '/map') ? 'active' : '' ?>">🗺️ Map</a>
                    <a href="/timeline" class="nav-link <?= str_starts_with($currentUri, '/timeline') ? 'active' : '' ?>">⏳ Timeline</a>
                    <a href="/graph" class="nav-link <?= str_starts_with($currentUri, '/graph') ? 'active' : '' ?>">🔗 Connections</a>
                    <a href="/quiz" class="nav-link highlight <?= str_starts_with($currentUri, '/quiz') ? 'active' : '' ?>" style="color: var(--accent-gold); font-weight: 700;">🎯 Quiz</a>
                </nav>

                <div class="header-actions">
                    <?php if ($currentUser): ?>
                        <?php if (Auth::hasRole('researcher', 'admin')): ?>
                            <a href="/admin" class="btn btn-primary btn-sm" style="background: #0f172a; border-color: #0f172a;">
                                ⚙️ Admin Panel
                            </a>
                        <?php endif; ?>
                        <a href="/profile" class="btn btn-secondary btn-sm">
                            <span><?= htmlspecialchars(substr($currentUser['name'], 0, 14)) ?></span>
                            <span class="badge badge-verified" style="font-size: 10px;"><?= ucfirst($currentUser['role']) ?></span>
                        </a>
                        <a href="/logout" class="btn btn-outline btn-sm">Sign Out</a>
                    <?php else: ?>
                        <a href="/login" class="btn btn-secondary btn-sm">Sign In</a>
                        <a href="/register" class="btn btn-primary btn-sm">Register</a>
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
