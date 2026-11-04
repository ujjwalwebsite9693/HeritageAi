<?php
// app/controllers/AdminController.php - Administrative and System Management

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Csrf.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../services/OcrService.php';
require_once __DIR__ . '/../services/LinkDiscoveryService.php';

class AdminController {
    public function dashboard(): void {
        Auth::requireRole('researcher', 'admin');
        $pdo = DB::getConnection();

        $stats = [
            'manuscripts' => (int)$pdo->query("SELECT COUNT(*) FROM manuscripts")->fetchColumn(),
            'monuments' => (int)$pdo->query("SELECT COUNT(*) FROM monuments")->fetchColumn(),
            'persons' => (int)$pdo->query("SELECT COUNT(*) FROM persons")->fetchColumn(),
            'events' => (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn(),
            'pending_links' => (int)$pdo->query("SELECT COUNT(*) FROM links WHERE status = 'suggested'")->fetchColumn(),
            'pending_jobs' => (int)$pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'pending'")->fetchColumn(),
            'users' => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        ];

        require __DIR__ . '/../views/admin_dashboard.php';
    }

    public function users(): void {
        Auth::requireRole('admin');
        $pdo = DB::getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Csrf::validate()) {
            $userId = (int)($_POST['user_id'] ?? 0);
            $role = $_POST['role'] ?? 'user';

            if (in_array($role, ['visitor', 'user', 'researcher', 'admin'], true)) {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$role, $userId]);
                $_SESSION['flash_success'] = "User #$userId role updated to '$role'.";
            }
            Response::redirect('/admin/users');
            return;
        }

        $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
        require __DIR__ . '/../views/admin_users.php';
    }

    public function queue(): void {
        Auth::requireRole('admin');
        $pdo = DB::getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'run_worker') {
            if (!Csrf::validate()) {
                Response::error("Invalid CSRF token", 403);
            }

            $pendingJobs = $pdo->query("SELECT * FROM jobs WHERE status = 'pending' ORDER BY id ASC LIMIT 10")->fetchAll();
            $processed = 0;

            foreach ($pendingJobs as $job) {
                $payload = json_decode($job['payload'], true) ?: [];
                $pdo->prepare("UPDATE jobs SET status = 'processing', attempts = attempts + 1 WHERE id = ?")->execute([$job['id']]);

                try {
                    if ($job['type'] === 'ocr_process' && !empty($payload['page_id'])) {
                        OcrService::processPage((int)$payload['page_id']);
                    } elseif ($job['type'] === 'link_discovery') {
                        LinkDiscoveryService::discoverLinks($payload['target_manuscript_id'] ?? null);
                    }

                    $pdo->prepare("UPDATE jobs SET status = 'completed', updated_at = NOW() WHERE id = ?")->execute([$job['id']]);
                    $processed++;
                } catch (Exception $e) {
                    $pdo->prepare("UPDATE jobs SET status = 'failed', error_message = ?, updated_at = NOW() WHERE id = ?")
                        ->execute([$e->getMessage(), $job['id']]);
                }
            }

            $_SESSION['flash_success'] = "Queue worker executed: $processed background jobs processed.";
            Response::redirect('/admin/queue');
            return;
        }

        $jobs = $pdo->query("SELECT * FROM jobs ORDER BY id DESC LIMIT 50")->fetchAll();
        $stats = [
            'pending' => (int)$pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'pending'")->fetchColumn(),
            'completed' => (int)$pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'completed'")->fetchColumn(),
            'failed' => (int)$pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'failed'")->fetchColumn(),
        ];

        require __DIR__ . '/../views/admin_queue.php';
    }

    public function settings(): void {
        Auth::requireRole('admin');
        $pdo = DB::getConnection();

        $info = [
            'php_version' => PHP_VERSION,
            'mysql_version' => $pdo->query("SELECT VERSION()")->fetchColumn(),
            'extensions' => [
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'curl' => extension_loaded('curl'),
                'gd' => extension_loaded('gd'),
                'mbstring' => extension_loaded('mbstring'),
                'openssl' => extension_loaded('openssl'),
            ],
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
        ];

        require __DIR__ . '/../views/admin_settings.php';
    }
}
