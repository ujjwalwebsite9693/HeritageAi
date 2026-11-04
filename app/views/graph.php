<?php
// app/views/graph.php
$pageTitle = "Historical Connections Network Graph";
require __DIR__ . '/layouts/header.php';
?>

<div style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div class="hero-pill">
                ⚡ Interactive Network · इतिहास के संबंधों का संजाल
            </div>
            <h1 style="font-size: 30px; color: var(--text-main); margin-bottom: 6px;">Historical Connections Network (ऐतिहासिक संबंध)</h1>
            <p style="color: var(--text-muted); font-size: 14.5px;">
                किसी भी शासक (नीले हीरे) या ग्रन्थ (लाल वृत्त) पर क्लिक करें और देखें कि वे आपस में कैसे जुड़े हुए हैं।
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <?php if (Auth::hasRole('researcher', 'admin')): ?>
                <a href="/admin" class="btn btn-secondary btn-sm">⚙️ Admin Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Legend & Controls -->
<div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 14px 20px; margin-bottom: 18px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; box-shadow: var(--shadow-xs);">
    <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; font-size: 13px;">
        <span style="font-weight: 600; color: var(--text-muted);">Symbols (चिह्न):</span>
        <span style="display: flex; align-items: center; gap: 6px;">
            <span style="width: 14px; height: 14px; border-radius: 50%; background: #8b2635; display: inline-block;"></span> 📜 Manuscript (प्राचीन ग्रन्थ)
        </span>
        <span style="display: flex; align-items: center; gap: 6px;">
            <span style="width: 14px; height: 14px; transform: rotate(45deg); background: #1a365d; display: inline-block;"></span> 👑 Emperor / Figure (शासक व व्यक्ति)
        </span>
        <span style="display: flex; align-items: center; gap: 6px; margin-left: 8px;">
            <span style="width: 24px; height: 3px; background: #2e7d32; display: inline-block;"></span> Verified Link (पुष्ट संबंध)
        </span>
    </div>

    <div style="display: flex; gap: 8px;">
        <button id="btn-fit-network" class="btn btn-secondary btn-sm">Reset View</button>
        <button id="btn-toggle-physics" class="btn btn-secondary btn-sm">Stabilize / Freeze</button>
    </div>
</div>

<!-- Graph Canvas and Inspector -->
<div style="display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start;">
    <div id="network-container" style="height: 650px; min-height: 650px; max-height: 650px; width: 100%; border: 1px solid var(--border-light); border-radius: var(--radius-md); background: #ffffff; position: relative; overflow: hidden; box-shadow: var(--shadow-sm);"></div>

    <!-- Inspector Drawer -->
    <div id="graph-inspector" style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-sm); overflow-y: auto; height: 650px; max-height: 650px;">
        <h3 id="inspector-title" style="font-size: 20px; color: var(--text-main); margin-bottom: 6px;">Graph Explorer</h3>
        <div id="inspector-subtitle" style="font-size: 13px; font-weight: 600; color: var(--accent-gold); margin-bottom: 14px;">Select any node or connection to inspect</div>
        <p id="inspector-desc" style="font-size: 13.5px; color: var(--text-muted); line-height: 1.6; margin-bottom: 16px;">
            Click on a manuscript or emperor node on the left to see connected people, events, and historical links.
        </p>

        <div id="inspector-links-box" style="border-top: 1px solid var(--border-light); padding-top: 14px; display: none;">
            <h4 style="font-size: 14px; margin-bottom: 8px;">Connected Historical Links:</h4>
            <div id="inspector-links-list" style="display: flex; flex-direction: column; gap: 8px;"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var rawData = <?= json_encode($graphData, JSON_UNESCAPED_UNICODE) ?>;
    var container = document.getElementById('network-container');

    var nodes = new vis.DataSet(rawData.nodes);
    var edges = new vis.DataSet(rawData.edges);

    var data = {
        nodes: nodes,
        edges: edges
    };

    var options = {
        height: '650px',
        width: '100%',
        autoResize: true,
        nodes: {
            font: {
                size: 13,
                face: 'Inter, sans-serif',
                color: '#0f172a'
            },
            borderWidth: 2,
            shadow: true
        },
        edges: {
            font: {
                size: 11,
                align: 'middle'
            },
            smooth: {
                type: 'continuous'
            },
            shadow: false
        },
        physics: {
            enabled: true,
            barnesHut: {
                gravitationalConstant: -3800,
                centralGravity: 0.35,
                springLength: 140,
                springConstant: 0.04
            },
            stabilization: {
                iterations: 150
            }
        },
        interaction: {
            hover: true,
            tooltipDelay: 150,
            navigationButtons: true,
            keyboard: true
        }
    };

    var network = new vis.Network(container, data, options);

    network.on("selectNode", function(params) {
        if (params.nodes.length > 0) {
            var nodeId = params.nodes[0];
            var node = nodes.get(nodeId);
            if (node) {
                document.getElementById('inspector-title').innerText = node.label;
                document.getElementById('inspector-subtitle').innerText = "Category: " + node.group.toUpperCase();
                document.getElementById('inspector-desc').innerHTML = node.title || 'Central entity in the archive.';
            }
        }
    });

    network.on("selectEdge", function(params) {
        if (params.edges.length > 0) {
            var edgeId = params.edges[0];
            var edge = edges.get(edgeId);
            if (edge) {
                document.getElementById('inspector-title').innerText = "Historical Connection";
                document.getElementById('inspector-subtitle').innerText = "Match Score: " + (edge.value || 50) + "%";
                document.getElementById('inspector-desc').innerHTML = edge.title || 'Cross-corpus correlation.';
            }
        }
    });

    document.getElementById('btn-fit-network').addEventListener('click', function() {
        network.fit({ animation: { duration: 600 } });
    });

    var physicsOn = true;
    document.getElementById('btn-toggle-physics').addEventListener('click', function() {
        physicsOn = !physicsOn;
        network.setOptions({ physics: { enabled: physicsOn } });
        this.innerText = physicsOn ? "Stabilize / Freeze" : "Enable Physics";
    });
});
</script>

<?php require __DIR__ . '/layouts/footer.php'; ?>
