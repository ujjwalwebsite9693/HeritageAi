<?php
// app/controllers/GraphController.php - Historical Connection Graph & Link Review Queue

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/Link.php';
require_once __DIR__ . '/../services/LinkDiscoveryService.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Csrf.php';
require_once __DIR__ . '/../helpers/Response.php';

class GraphController {
    public function index(): void {
        $rootId = !empty($_GET['root']) ? (int)$_GET['root'] : null;
        $graphData = LinkDiscoveryService::getGraphData($rootId);

        $pdo = DB::getConnection();
        $manuscripts = $pdo->query("SELECT id, title FROM manuscripts WHERE status = 'published'")->fetchAll();

        require __DIR__ . '/../views/graph.php';
    }

    public function reviewQueue(): void {
        Auth::requireRole('researcher', 'admin');

        $statusFilter = $_GET['status'] ?? null;
        $links = Link::all($statusFilter);

        require __DIR__ . '/../views/link_review.php';
    }

    public function updateLinkStatus(): void {
        Auth::requireRole('researcher', 'admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::validate()) {
            Response::error("Invalid request or CSRF token", 403);
        }

        $linkId = (int)($_POST['link_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['confirmed', 'rejected', 'suggested'], true)) {
            Response::error("Invalid status value");
        }

        Link::updateStatus($linkId, $status, Auth::id());

        $_SESSION['flash_success'] = "Historical link status updated to " . ucfirst($status) . ".";
        Response::redirect('/links/review');
    }

    public function runScan(): void {
        Auth::requireRole('researcher', 'admin');

        $discovered = LinkDiscoveryService::discoverLinks();
        $_SESSION['flash_success'] = "Discovered or refreshed " . count($discovered) . " candidate historical connections across the corpus.";
        Response::redirect('/links/review');
    }
}

