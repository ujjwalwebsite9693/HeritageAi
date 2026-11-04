<?php
// cron/worker.php - CLI background queue runner for OCR, AI Translation, and Link Discovery

if (php_sapi_name() !== 'cli' && (!isset($_GET['secret']) || $_GET['secret'] !== 'mia_cron_trigger')) {
    die("Access Denied: CLI or authorized token only.\n");
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/services/OcrService.php';
require_once __DIR__ . '/../app/services/TranslationService.php';
require_once __DIR__ . '/../app/services/LinkDiscoveryService.php';

echo "[" . date('Y-m-d H:i:s') . "] Manuscript Intelligence Archive Queue Worker starting...\n";

$pdo = DB::getConnection();
$jobs = $pdo->query("SELECT * FROM jobs WHERE status = 'pending' ORDER BY id ASC LIMIT 10")->fetchAll();

if (empty($jobs)) {
    echo "No pending jobs in queue.\n";
    exit(0);
}

echo "Found " . count($jobs) . " pending jobs. Processing...\n";

foreach ($jobs as $job) {
    echo "Processing Job #{$job['id']} (Type: {$job['type']})... ";
    $payload = json_decode($job['payload'], true) ?: [];

    $pdo->prepare("UPDATE jobs SET status = 'processing', attempts = attempts + 1, updated_at = NOW() WHERE id = ?")->execute([$job['id']]);

    try {
        if ($job['type'] === 'ocr_process' && !empty($payload['page_id'])) {
            $res = OcrService::processPage((int)$payload['page_id']);
            echo "OCR Complete. (Confidence: {$res['confidence']}%)\n";
        } elseif ($job['type'] === 'link_discovery') {
            $discovered = LinkDiscoveryService::discoverLinks($payload['target_manuscript_id'] ?? null);
            echo "Link Discovery Complete. (" . count($discovered) . " connections evaluated)\n";
        } elseif ($job['type'] === 'ai_translate' && !empty($payload['page_id'])) {
            $trans = TranslationService::translatePage((int)$payload['page_id'], $payload['target_lang'] ?? 'en');
            echo "Translation Complete.\n";
        }

        $pdo->prepare("UPDATE jobs SET status = 'completed', updated_at = NOW() WHERE id = ?")->execute([$job['id']]);
    } catch (Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
        $pdo->prepare("UPDATE jobs SET status = 'failed', error_message = ?, updated_at = NOW() WHERE id = ?")
            ->execute([$e->getMessage(), $job['id']]);
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Queue run finished.\n";

