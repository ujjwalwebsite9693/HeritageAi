<?php
// app/views/api_docs.php
$pageTitle = "Public REST API Documentation";
require __DIR__ . '/layouts/header.php';
?>

<div style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <span class="badge badge-verified">DEVELOPER PORTAL</span>
        <a href="/admin" class="btn btn-secondary btn-sm">← Back to Admin Panel</a>
    </div>
    <h1 style="font-size: 30px; color: var(--primary);">Developer REST API Documentation</h1>
    <p style="color: var(--text-muted); font-size: 15px;">
        Integrate rare manuscript collections, historical GIS geometries, timeline chronology, and knowledge graph linkages into your digital humanities projects, museum displays, and academic applications.
    </p>
</div>

<!-- Authentication & Rate Limit Information -->
<div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 32px;">
    <h3 style="font-size: 18px; margin-bottom: 8px; color: var(--primary);">Authentication & Headers</h3>
    <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 14px;">
        Read-only public endpoints are open to all visitors. For mutation endpoints or increased rate limit allowances, pass your personal API key via the <code>X-API-KEY</code> header or as an <code>?api_key=</code> URL query parameter.
    </p>

    <div style="background: #1e1e1e; color: #d4d4d4; padding: 14px 18px; border-radius: 4px; font-family: monospace; font-size: 13px;">
        curl -H "X-API-KEY: mia_key_adm_9876543210abcdef990011" http://localhost:8000/api/manuscripts
    </div>
</div>

<!-- API Endpoints Specifications Table & Examples -->
<div style="display: flex; flex-direction: column; gap: 24px;">
    <!-- 1. Manuscripts -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: bold;">GET</span>
            <code style="font-size: 16px; font-weight: bold;">/api/manuscripts</code>
        </div>
        <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 12px;">
            Search and paginate rare manuscripts with multi-facet filters.
        </p>
        <div style="font-size: 13px; color: #4b5563; margin-bottom: 8px;">
            <strong>Query Parameters:</strong> <code>q</code> (search query), <code>lang</code> (language ID), <code>place</code> (origin place ID), <code>era_from</code>, <code>era_to</code>, <code>limit</code> (default: 20)
        </div>
        <div style="background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 4px; font-family: monospace; font-size: 12px;">
            curl "http://localhost:8000/api/manuscripts?q=Ashoka&lang=2"
        </div>
    </div>

    <!-- 2. Single Manuscript & Folios -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: bold;">GET</span>
            <code style="font-size: 16px; font-weight: bold;">/api/manuscripts/{id}</code>
        </div>
        <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 12px;">
            Fetch complete codicological metadata for a specific manuscript, including all page transcriptions, translations, and annotations.
        </p>
        <div style="background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 4px; font-family: monospace; font-size: 12px;">
            curl "http://localhost:8000/api/manuscripts/1"
        </div>
    </div>

    <!-- 3. Chronological Events -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: bold;">GET</span>
            <code style="font-size: 16px; font-weight: bold;">/api/events</code>
        </div>
        <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 12px;">
            Retrieve chronological events normalized to integer CE sort years with Gregorian, Vikram Samvat, and Hijri calendar equivalents.
        </p>
        <div style="font-size: 13px; color: #4b5563; margin-bottom: 8px;">
            <strong>Query Parameters:</strong> <code>from</code> (year), <code>to</code> (year), <code>place</code> (place ID), <code>category</code> (war, treaty, trade, religion, politics, culture)
        </div>
        <div style="background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 4px; font-family: monospace; font-size: 12px;">
            curl "http://localhost:8000/api/events?category=politics"
        </div>
    </div>

    <!-- 4. Historical Places -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: bold;">GET</span>
            <code style="font-size: 16px; font-weight: bold;">/api/places</code>
        </div>
        <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 12px;">
            List all historical epigraphic and origin centers with geographical coordinates, modern and ancient names, and linked codex counts.
        </p>
        <div style="background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 4px; font-family: monospace; font-size: 12px;">
            curl "http://localhost:8000/api/places"
        </div>
    </div>

    <!-- 5. Network Graph -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: bold;">GET</span>
            <code style="font-size: 16px; font-weight: bold;">/api/graph</code>
        </div>
        <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 12px;">
            Export vis-network / D3 compatible JSON containing nodes (manuscripts, persons, places) and weighted connection edges.
        </p>
        <div style="background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 4px; font-family: monospace; font-size: 12px;">
            curl "http://localhost:8000/api/graph?root=1&depth=2"
        </div>
    </div>

    <!-- 6. Discovered Links -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: bold;">GET</span>
            <code style="font-size: 16px; font-weight: bold;">/api/links</code>
        </div>
        <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 12px;">
            Query cross-manuscript discovered connections, scoring metrics, and shared entity explanations.
        </p>
        <div style="background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 4px; font-family: monospace; font-size: 12px;">
            curl "http://localhost:8000/api/links?status=confirmed"
        </div>
    </div>

    <!-- 7. Data Exports (CSV, GeoJSON, Citations) -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <span class="badge" style="background: #fef3c7; color: #d97706; font-weight: bold;">EXPORT</span>
            <code style="font-size: 16px; font-weight: bold;">/api/export/{type}/{id}</code>
        </div>
        <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 12px;">
            Multi-format scholarly export utilities.
        </p>
        <div style="font-size: 13px; color: #4b5563; margin-bottom: 8px;">
            Supported types: <code>events-csv</code>, <code>places-geojson</code>, <code>citations</code> (APA, MLA, Chicago).
        </div>
        <div style="background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 4px; font-family: monospace; font-size: 12px;">
            curl "http://localhost:8000/api/export/places-geojson/0"<br>
            curl "http://localhost:8000/api/export/citations/1"
        </div>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>

