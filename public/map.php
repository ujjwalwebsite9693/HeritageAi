<?php
// public/map.php - Historical Cartography Map (Plain Single-File PHP)
require_once __DIR__ . '/db.php';
$db = getDB();

// FETCH PLACES DIRECTLY (स्थान लाएं)
$stmt = $db->query("SELECT * FROM places WHERE lat IS NOT NULL AND lng IS NOT NULL");
$places = $stmt->fetchAll();

$pageTitle = "Historical GIS Map · ऐतिहासिक नक्शा";
require_once __DIR__ . '/header.php';
?>

<div style="margin-bottom: 24px;">
    <div class="hero-pill">
        🗺️ Historical Geography · ऐतिहासिक भारत का नक्शा
    </div>
    <h1 style="font-size: 30px; color: var(--text-main); margin-bottom: 6px;">Historical Map of India (ऐतिहासिक नक्शा)</h1>
    <p style="color: var(--text-muted); font-size: 14.5px;">
        Click on any historical city marker (Agra, Delhi, Pataliputra, Nalanda, Junagadh) to inspect ancient heritage.
    </p>
</div>

<!-- Map and Details Grid -->
<div style="display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; margin-bottom: 40px;">
    <!-- Map Container with Strict Bounds -->
    <div id="map-container" style="height: 640px; min-height: 640px; max-height: 640px; width: 100%; border: 1px solid var(--border-light); border-radius: var(--radius-md); background: #ffffff; position: relative; overflow: hidden; box-shadow: var(--shadow-sm); z-index: 1;"></div>

    <!-- Right Drawer: Place Details -->
    <div id="place-sidebar" style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-sm); overflow-y: auto; height: 640px; max-height: 640px;">
        <h3 id="sidebar-place-ancient" style="font-size: 22px; color: var(--primary); margin-bottom: 4px;">Pāṭaliputra</h3>
        <div id="sidebar-place-modern" style="font-size: 14px; font-weight: 600; color: var(--text-muted); margin-bottom: 12px;">Modern Patna, Bihar</div>
        <p id="sidebar-place-notes" style="font-size: 13.5px; color: var(--text-body); line-height: 1.6; margin-bottom: 16px;">
            Select any marker on the map to see monuments and history associated with that city.
        </p>

        <div style="border-top: 1px solid var(--border-light); padding-top: 16px;">
            <a href="/monuments.php" class="btn btn-secondary btn-sm" style="width: 100%; margin-bottom: 8px;">Explore All Monuments 🏰</a>
            <a href="/search.php" class="btn btn-outline btn-sm" style="width: 100%;">Browse Manuscripts 📜</a>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var rawPlaces = <?= json_encode($places, JSON_UNESCAPED_UNICODE) ?>;
    
    // Initialize Leaflet Map centered on India
    var map = L.map('map-container').setView([23.5937, 78.9629], 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    rawPlaces.forEach(function(place) {
        var lat = parseFloat(place.lat);
        var lng = parseFloat(place.lng);
        if (isNaN(lat) || isNaN(lng)) return;

        var marker = L.marker([lat, lng]).addTo(map);
        marker.bindPopup("<strong>" + place.ancient_name + "</strong><br>" + (place.modern_name || ''));

        marker.on('click', function() {
            document.getElementById('sidebar-place-ancient').innerText = place.ancient_name;
            document.getElementById('sidebar-place-modern').innerText = place.modern_name ? "Modern: " + place.modern_name : "";
            document.getElementById('sidebar-place-notes').innerText = place.notes || "Historic center of administration and learning.";
        });
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
