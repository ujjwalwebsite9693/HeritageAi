<?php
// app/controllers/ArchiveController.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/Manuscript.php';
require_once __DIR__ . '/../models/Place.php';
require_once __DIR__ . '/../services/SearchService.php';
require_once __DIR__ . '/../services/TranslationService.php';
require_once __DIR__ . '/../services/ExportService.php';
require_once __DIR__ . '/../services/LinkDiscoveryService.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Csrf.php';
require_once __DIR__ . '/../helpers/Response.php';

class ArchiveController {
    public function search(): void {
        $params = [
            'q' => $_GET['q'] ?? '',
            'lang' => $_GET['lang'] ?? '',
            'place' => $_GET['place'] ?? '',
            'era_from' => $_GET['era_from'] ?? '',
            'era_to' => $_GET['era_to'] ?? '',
            'script' => $_GET['script'] ?? '',
            'limit' => 12,
            'offset' => isset($_GET['page']) ? ((int)$_GET['page'] - 1) * 12 : 0
        ];

        $results = SearchService::search($params);
        $facets = SearchService::getFacets();

        require __DIR__ . '/../views/archive_search.php';
    }

    public function detail(): void {
        $id = (int)($_GET['id'] ?? 0);
        $manuscript = Manuscript::find($id);

        if (!$manuscript) {
            http_response_code(404);
            require __DIR__ . '/../views/404.php';
            return;
        }

        $connectedLinks = Manuscript::getConnectedLinks($id);
        $glossaryMap = TranslationService::getGlossaryMap();
        $citations = ExportService::generateCitations($id);

        require __DIR__ . '/../views/manuscript_detail.php';
    }

    public function upload(): void {
        Auth::requireRole('researcher', 'admin');

        $pdo = DB::getConnection();
        $languages = $pdo->query("SELECT * FROM languages ORDER BY name ASC")->fetchAll();
        $places = Place::all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate()) {
                Response::error("Invalid security token (CSRF).", 403);
            }

            $title = trim($_POST['title'] ?? '');
            $author = trim($_POST['author'] ?? 'Anonymous');
            $languageId = (int)($_POST['language_id'] ?? 1);
            $script = trim($_POST['script'] ?? 'Devanagari');
            $eraStart = (int)($_POST['era_start'] ?? 0);
            $eraEnd = !empty($_POST['era_end']) ? (int)$_POST['era_end'] : null;
            $placeId = !empty($_POST['origin_place_id']) ? (int)$_POST['origin_place_id'] : null;
            $repository = trim($_POST['source_repository'] ?? '');
            $callNumber = trim($_POST['call_number'] ?? '');
            $license = trim($_POST['license'] ?? 'Public Domain');
            $summary = trim($_POST['summary'] ?? '');

            // Handle file upload
            $thumbnailPath = '/assets/img/manuscript-ashoka.jpg'; // fallback
            if (!empty($_FILES['scan_file']['tmp_name'])) {
                $uploadDir = dirname(__DIR__, 2) . '/public/assets/img/uploads';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0777, true);
                }

                $filename = 'scan_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.]/', '', basename($_FILES['scan_file']['name']));
                $destination = $uploadDir . '/' . $filename;
                if (move_uploaded_file($_FILES['scan_file']['tmp_name'], $destination)) {
                    $thumbnailPath = '/assets/img/uploads/' . $filename;
                }
            }

            $manuscriptId = Manuscript::create([
                'title' => $title,
                'author' => $author,
                'language_id' => $languageId,
                'script' => $script,
                'era_start' => $eraStart,
                'era_end' => $eraEnd,
                'origin_place_id' => $placeId,
                'source_repository' => $repository,
                'call_number' => $callNumber,
                'license' => $license,
                'access_level' => 'public',
                'status' => 'published',
                'uploaded_by' => Auth::id(),
                'thumbnail' => $thumbnailPath,
                'summary' => $summary
            ]);

            // Create Page 1 with initial scan
            $pageId = Manuscript::addPage($manuscriptId, 1, $thumbnailPath, trim($_POST['transcription_initial'] ?? ''));

            // Queue background OCR and Link Discovery
            $jobStmt = $pdo->prepare("INSERT INTO jobs (type, payload, status, created_at) VALUES ('ocr_process', ?, 'pending', NOW())");
            $jobStmt->execute([json_encode(['page_id' => $pageId, 'manuscript_id' => $manuscriptId])]);

            $linkJob = $pdo->prepare("INSERT INTO jobs (type, payload, status, created_at) VALUES ('link_discovery', ?, 'pending', NOW())");
            $linkJob->execute([json_encode(['target_manuscript_id' => $manuscriptId])]);

            // Run immediate candidate link detection so results appear right away
            LinkDiscoveryService::discoverLinks($manuscriptId);

            $_SESSION['flash_success'] = "Manuscript successfully uploaded! Automated OCR and Historical Link Discovery queued.";
            Response::redirect('/manuscript?id=' . $manuscriptId);
            return;
        }

        require __DIR__ . '/../views/upload.php';
    }
}

