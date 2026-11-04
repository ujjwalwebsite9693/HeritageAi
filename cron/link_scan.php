<?php
// cron/link_scan.php - Scheduled batch scan to discover historical connections across all manuscripts

if (php_sapi_name() !== 'cli' && (!isset($_GET['secret']) || $_GET['secret'] !== 'mia_cron_trigger')) {
    die("Access Denied: CLI or authorized token only.\n");
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/services/LinkDiscoveryService.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting Full Historical Link Discovery Scan...\n";

$links = LinkDiscoveryService::discoverLinks();

echo "Historical Link Discovery complete. Evaluated/Discovered " . count($links) . " connection candidates.\n";
foreach ($links as $l) {
    echo " -> [Score: {$l['score']}%] Doc #{$l['manuscript_a']} <-> Doc #{$l['manuscript_b']} (Status: {$l['status']})\n";
}

