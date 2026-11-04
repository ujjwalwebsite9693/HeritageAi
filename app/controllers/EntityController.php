<?php
// app/controllers/EntityController.php - Fuzzy Entity Resolution & Merging

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../services/EntityService.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Csrf.php';
require_once __DIR__ . '/../helpers/Response.php';

class EntityController {
    public function mergeScreen(): void {
        Auth::requireRole('researcher', 'admin');

        $duplicates = EntityService::findDuplicates();
        $pdo = DB::getConnection();
        $allEntities = $pdo->query("SELECT * FROM entities WHERE merged_into IS NULL ORDER BY name ASC")->fetchAll();

        require __DIR__ . '/../views/entity_merge.php';
    }

    public function merge(): void {
        Auth::requireRole('researcher', 'admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::validate()) {
            Response::error("Invalid CSRF token", 403);
        }

        $keepId = (int)($_POST['keep_id'] ?? 0);
        $mergeId = (int)($_POST['merge_id'] ?? 0);

        if ($keepId === $mergeId || !$keepId || !$mergeId) {
            Response::error("Invalid entity IDs selected for merge.");
        }

        try {
            EntityService::mergeEntities($keepId, $mergeId, Auth::id());
            $_SESSION['flash_success'] = "Entity #$mergeId successfully merged into canonical entity #$keepId.";
            Response::redirect('/entities/merge');
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
}

