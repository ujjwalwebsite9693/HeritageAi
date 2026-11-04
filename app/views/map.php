<?php
// app/views/map.php
$pageTitle = "Historical GIS Cartography";
require __DIR__ . '/layouts/header.php';
?>

<div style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 32px; color: var(--primary);">Historical GIS Cartography</h1>
            <p style="color: var(--text-muted); font-size: 15px;">
                Interactive geographical mapping of ancient centers (Pāṭaliputra, Takṣaśilā, Kānyakubja) linked with imperial trade corridors, epigraphic pilgrimage highways, and manuscript provenances.
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="/api/export/places-geojson/0" class="btn btn-secondary btn-sm">Export GeoJSON</a>
        </div>
    </div>
</div>

<!-- Map Interactive Control Panel -->
<div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 14px 20px; margin-bottom: 16px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; box-shadow: var(--shadow-sm);">
    <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
        <div>
            <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Historical Route Layers:</span>
        </div>
        <label style="font-size: 13px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
            <input type="checkbox" id="toggle-uttarapatha" checked>
            <span style="color: #b7791f; font-weight: 600;">■ Uttarāpatha Highway</span>
        </label>
        <label style="font-size: 13px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
            <input type="checkbox" id="toggle-mughal" checked>
            <span style="color: #805ad5; font-weight: 600;">■ Agra-Surat Trade Corridor</span>
        </label>
        <label style="font-size: 13px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
            <input type="checkbox" id="toggle-ashoka" checked>
            <span style="color: #c53030; font-weight: 600;">■ Ashokan Epigraphic Route</span>
        </label>
    </div>

    <div style="display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Era Filter:</span>
        <input type="range" id="era-slider" min="-350" max="1700" value="1700" step="50" style="width: 140px; cursor: pointer;">
        <span id="slider-year-label" class="badge badge-score" style="font-size: 12px; min-width: 90px; text-align: center;">Up to 1700 CE</span>
    </div>
</div>

<!-- Leaflet Map Container -->
<div style="display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start;">
    <div id="map-container" style="height: 640px; min-height: 640px; max-height: 640px; width: 100%; border: 1px solid var(--border-light); border-radius: var(--radius-md); background: #ffffff; position: relative; overflow: hidden; box-shadow: var(--shadow-sm); z-index: 1;"></div>

    <!-- Right Drawer: Selected Place Details -->
    <div id="place-sidebar" style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm); overflow-y: auto; max-height: 640px;">
        <h3 id="sidebar-place-ancient" style="font-size: 22px; color: var(--primary); margin-bottom: 4px;">Pāṭaliputra</h3>
        <div id="sidebar-place-modern" style="font-size: 14px; font-weight: 600; color: var(--text-muted); margin-bottom: 12px;">Modern Patna, Bihar</div>
        <p id="sidebar-place-notes" style="font-size: 13.5px; color: #4a5568; line-height: 1.5; margin-bottom: 16px;">
            Select any historical marker on the map to explore manuscripts and recorded events connected to that ancient city.
        </p>

        <div style="border-top: 1px solid var(--border-subtle); padding-top: 14px; margin-bottom: 16px;">
            <h4 style="font-size: 14px; margin-bottom: 10px; color: var(--text-main);">Connected Manuscripts:</h4>
            <div id="sidebar-manuscripts-list" style="display: flex; flex-direction: column; gap: 8px;">
                <span style="font-size: 12px; color: var(--text-faint);">Click on a marker to inspect provenances.</span>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border-subtle); padding-top: 14px;">
            <h4 style="font-size: 14px; margin-bottom: 10px; color: var(--text-main);">Chronological Events:</h4>
            <div id="sidebar-events-list" style="display: flex; flex-direction: column; gap: 8px;">
                <span style="font-size: 12px; color: var(--text-faint);">Click on a marker to inspect events.</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var placesData = <?= json_encode($places, JSON_UNESCAPED_UNICODE) ?>;
    var routesData = <?= json_encode($routes, JSON_UNESCAPED_UNICODE) ?>;

    // Center map around northern/central subcontinent
    var map = L.map('map-container').setView([24.5, 78.5], 5);

    // OpenStreetMap free tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Custom antique map icon styling
    var ancientIcon = L.divIcon({
        className: 'custom-map-pin',
        html: '<div style="background:#8b2635;color:#fff;border-radius:50%;width:26px;height:26px;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:12px;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3);">🏛️</div>',
        iconSize: [26, 26],
        iconAnchor: [13, 13]
    });

    var markers = [];

    placesData.forEach(function(place) {
        var marker = L.marker([place.lat, place.lng], { icon: ancientIcon }).addTo(map);
        marker.placeData = place;

        var popupHtml = "<div style='font-family:serif;min-width:180px;'>" +
                        "<h4 style='margin:0;color:#8b2635;font-size:16px;'>" + place.ancient_name + "</h4>" +
                        "<div style='font-size:12px;color:#666;'>Modern: " + place.modern_name + " (" + (place.region || '') + ")</div>" +
                        "<div style='margin-top:6px;font-size:11px;'>" +
                        "📜 " + place.manuscript_count + " Manuscripts · ⏳ " + place.event_count + " Events" +
                        "</div>" +
                        "<div style='margin-top:8px;'><a href='/place?id=" + place.id + "' style='font-size:11px;font-weight:bold;'>View Full Dossier →</a></div>" +
                        "</div>";

        marker.bindPopup(popupHtml);

        marker.on('click', function() {
            showPlaceInSidebar(place);
        });

        markers.push(marker);
    });

    // Draw route polylines
    var routeLayers = {};
    routesData.forEach(function(r, idx) {
        var latlngs = r.points.map(p => [p.lat, p.lng]);
        var polyline = L.polyline(latlngs, {
            color: r.color,
            weight: 4,
            opacity: 0.8,
            dashArray: '8, 8'
        }).addTo(map);

        polyline.bindTooltip("<strong>" + r.name + "</strong><br>" + r.description);

        if (idx === 0) routeLayers['uttarapatha'] = polyline;
        if (idx === 1) routeLayers['mughal'] = polyline;
        if (idx === 2) routeLayers['ashoka'] = polyline;
    });

    document.getElementById('toggle-uttarapatha').addEventListener('change', function(e) {
        if (e.target.checked) map.addLayer(routeLayers['uttarapatha']);
        else map.removeLayer(routeLayers['uttarapatha']);
    });
    document.getElementById('toggle-mughal').addEventListener('change', function(e) {
        if (e.target.checked) map.addLayer(routeLayers['mughal']);
        else map.removeLayer(routeLayers['mughal']);
    });
    document.getElementById('toggle-ashoka').addEventListener('change', function(e) {
        if (e.target.checked) map.addLayer(routeLayers['ashoka']);
        else map.removeLayer(routeLayers['ashoka']);
    });

    // Slider filter
    var slider = document.getElementById('era-slider');
    var sliderLabel = document.getElementById('slider-year-label');
    slider.addEventListener('input', function() {
        var val = parseInt(this.value);
        sliderLabel.innerText = val < 0 ? Math.abs(val) + " BCE" : val + " CE";
    });

    function showPlaceInSidebar(place) {
        document.getElementById('sidebar-place-ancient').innerText = place.ancient_name;
        document.getElementById('sidebar-place-modern').innerText = "Modern " + place.modern_name + ", " + (place.region || '') + " (" + place.country + ")";
        document.getElementById('sidebar-place-notes').innerText = place.notes || 'No description recorded.';

        // Fetch connected details via API
        fetch('/api/places/' + place.id)
            .then(res => res.json())
            .then(data => {
                if (data.place) {
                    var mList = "";
                    if (data.place.manuscripts && data.place.manuscripts.length > 0) {
                        data.place.manuscripts.forEach(function(m) {
                            mList += "<div style='font-size:12.5px;'><a href='/manuscript?id=" + m.id + "'>📜 " + m.title + "</a> (" + m.era_start + ")</div>";
                        });
                    } else {
                        mList = "<span style='font-size:12px;color:#999;'>No primary codices assigned directly.</span>";
                    }
                    document.getElementById('sidebar-manuscripts-list').innerHTML = mList;

                    var eList = "";
                    if (data.place.events && data.place.events.length > 0) {
                        data.place.events.forEach(function(ev) {
                            eList += "<div style='font-size:12px;'><strong>" + ev.date_text + "</strong>: " + ev.title + "</div>";
                        });
                    } else {
                        eList = "<span style='font-size:12px;color:#999;'>No major events cataloged.</span>";
                    }
                    document.getElementById('sidebar-events-list').innerHTML = eList;
                }
            });
    }

    // Default show first place in drawer
    if (placesData.length > 0) {
        showPlaceInSidebar(placesData[0]);
    }
});
</script>

<?php require __DIR__ . '/layouts/footer.php'; ?>

