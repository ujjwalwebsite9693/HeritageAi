<?php
// public/graph.php - Historical Connections Network Graph (Plain Single-File PHP)
require_once __DIR__ . '/db.php';
$db = getDB();

// 1. FETCH MANUSCRIPTS AND PERSONS DIRECTLY (ग्रन्थ और शासक लाएं)
$manuscripts = $db->query("SELECT id, title, author, era_start FROM manuscripts WHERE status = 'published'")->fetchAll();
$persons = $db->query("SELECT id, name, role FROM persons LIMIT 15")->fetchAll();
$links = $db->query("SELECT * FROM links WHERE status = 'confirmed'")->fetchAll();

$nodes = [];
$edges = [];

// Add Manuscripts Nodes (Red circles)
foreach ($manuscripts as $m) {
    $nodes[] = [
        'id' => 'm_' . $m['id'],
        'label' => $m['title'],
        'group' => 'manuscript',
        'title' => "Manuscript: {$m['title']}<br>Author: {$m['author']}",
        'shape' => 'dot',
        'size' => 22,
        'color' => '#8b2635'
    ];
}

// Add Person Nodes (Blue diamonds)
foreach ($persons as $p) {
    $nodes[] = [
        'id' => 'p_' . $p['id'],
        'label' => $p['name'],
        'group' => 'person',
        'title' => "Historical Figure: {$p['name']}<br>Role: {$p['role']}",
        'shape' => 'diamond',
        'size' => 18,
        'color' => '#1a365d'
    ];
}

// Add Confirmed Links between Manuscripts (Green lines)
foreach ($links as $l) {
    $edges[] = [
        'from' => 'm_' . $l['manuscript_a'],
        'to' => 'm_' . $l['manuscript_b'],
        'value' => (int)$l['score'],
        'title' => "Historical Match: {$l['score']}%",
        'color' => '#2e7d32'
    ];
}

$pageTitle = "Connections Network · ऐतिहासिक संबंध";
require_once __DIR__ . '/header.php';
?>

<div style="margin-bottom: 24px;">
    <div class="hero-pill">
        ⚡ Interactive Network · इतिहास के संबंधों का संजाल
    </div>
    <h1 style="font-size: 30px; color: var(--text-main); margin-bottom: 6px;">Historical Connections Network (ऐतिहासिक संबंध)</h1>
    <p style="color: var(--text-muted); font-size: 14.5px;">
        Click on any ruler (blue diamond) or manuscript (red circle) to explore connected events and historical documents.
    </p>
</div>

<!-- Legend & Controls -->
<div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 14px 20px; margin-bottom: 18px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; box-shadow: var(--shadow-xs);">
    <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; font-size: 13px;">
        <span style="font-weight: 600; color: var(--text-muted);">Symbols:</span>
        <span style="display: flex; align-items: center; gap: 6px;">
            <span style="width: 14px; height: 14px; border-radius: 50%; background: #8b2635; display: inline-block;"></span> 📜 Manuscript Codex
        </span>
        <span style="display: flex; align-items: center; gap: 6px;">
            <span style="width: 14px; height: 14px; transform: rotate(45deg); background: #1a365d; display: inline-block;"></span> 👑 Emperor / Figure
        </span>
        <span style="display: flex; align-items: center; gap: 6px; margin-left: 8px;">
            <span style="width: 24px; height: 3px; background: #2e7d32; display: inline-block;"></span> Verified Link
        </span>
    </div>

    <div style="display: flex; gap: 8px;">
        <button id="btn-fit-network" class="btn btn-secondary btn-sm">Reset View</button>
    </div>
</div>

<!-- Graph Container with Strict 650px Height Bounds -->
<div style="display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; margin-bottom: 40px;">
    <div id="network-container" style="height: 650px; min-height: 650px; max-height: 650px; width: 100%; border: 1px solid var(--border-light); border-radius: var(--radius-md); background: #ffffff; position: relative; overflow: hidden; box-shadow: var(--shadow-sm);"></div>

    <!-- Inspector Drawer -->
    <div id="graph-inspector" style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-sm); overflow-y: auto; height: 650px; max-height: 650px;">
        <h3 id="inspector-title" style="font-size: 20px; color: var(--text-main); margin-bottom: 6px;">Graph Explorer</h3>
        <div id="inspector-subtitle" style="font-size: 13px; font-weight: 600; color: var(--accent-gold); margin-bottom: 14px;">Select any node on the left</div>
        <p id="inspector-desc" style="font-size: 13.5px; color: var(--text-muted); line-height: 1.6; margin-bottom: 16px;">
            Click on any circle or diamond node on the left to see connected people and records.
        </p>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var rawNodes = <?= json_encode($nodes, JSON_UNESCAPED_UNICODE) ?>;
    var rawEdges = <?= json_encode($edges, JSON_UNESCAPED_UNICODE) ?>;
    var container = document.getElementById('network-container');

    var data = {
        nodes: new vis.DataSet(rawNodes),
        edges: new vis.DataSet(rawEdges)
    };

    var options = {
        height: '650px',
        width: '100%',
        autoResize: true,
        physics: {
            enabled: true,
            barnesHut: {
                gravitationalConstant: -3800,
                centralGravity: 0.35,
                springLength: 140
            },
            stabilization: { iterations: 120 }
        }
    };

    var network = new vis.Network(container, data, options);

    network.on("selectNode", function(params) {
        if (params.nodes.length > 0) {
            var node = data.nodes.get(params.nodes[0]);
            if (node) {
                document.getElementById('inspector-title').innerText = node.label;
                document.getElementById('inspector-subtitle').innerText = "Category: " + node.group.toUpperCase();
                document.getElementById('inspector-desc').innerHTML = node.title || 'Central entity in the archive.';
            }
        }
    });

    document.getElementById('btn-fit-network').addEventListener('click', function() {
        network.fit({ animation: { duration: 500 } });
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
