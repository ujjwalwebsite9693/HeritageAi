<?php
// app/controllers/TranscriptionController.php - Split-screen Transcription Workspace

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/Manuscript.php';
require_once __DIR__ . '/../models/Annotation.php';
require_once __DIR__ . '/../services/OcrService.php';
require_once __DIR__ . '/../services/TranslationService.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Csrf.php';
require_once __DIR__ . '/../helpers/Response.php';

class TranscriptionController {
    public function workspace(): void {
        Auth::requireRole('researcher', 'admin');

        $pageId = (int)($_GET['page_id'] ?? 1);
        $pdo = DB::getConnection();

        $stmt = $pdo->prepare("SELECT p.*, m.id as manuscript_id, m.title as manuscript_title, m.script, l.name as language_name, l.direction
                               FROM manuscript_pages p
                               JOIN manuscripts m ON p.manuscript_id = m.id
                               JOIN languages l ON m.language_id = l.id
                               WHERE p.id = ?");
        $stmt->execute([$pageId]);
        $page = $stmt->fetch();

        if (!$page) {
            http_response_code(404);
            require __DIR__ . '/../views/404.php';
            return;
        }

        // Fetch translations for this page
        $tStmt = $pdo->prepare("SELECT t.*, u.name as reviewer_name 
                                FROM translations t 
                                LEFT JOIN users u ON t.reviewer_id = u.id 
                                WHERE t.page_id = ? 
                                ORDER BY t.version DESC");
        $tStmt->execute([$pageId]);
        $translations = $tStmt->fetchAll();

        // Fetch annotations for this page
        $annotations = Annotation::getForPage($pageId, Auth::id());

        // Fetch siblings for page-by-page navigation
        $allPagesStmt = $pdo->prepare("SELECT id, page_no FROM manuscript_pages WHERE manuscript_id = ? ORDER BY page_no ASC");
        $allPagesStmt->execute([$page['manuscript_id']]);
        $allPages = $allPagesStmt->fetchAll();

        require __DIR__ . '/../views/transcription_workspace.php';
    }

    public function saveTranscription(): void {
        Auth::requireRole('researcher', 'admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::validate()) {
            Response::error("Invalid CSRF token", 403);
        }

        $pageId = (int)($_POST['page_id'] ?? 0);
        $transcription = trim($_POST['transcription'] ?? '');
        $transliteration = trim($_POST['transliteration'] ?? '');
        $status = $_POST['status'] ?? 'corrected'; // 'corrected' or 'verified'

        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("UPDATE manuscript_pages 
                               SET transcription = ?, transliteration = ?, status = ?, updated_at = NOW() 
                               WHERE id = ?");
        $stmt->execute([$transcription, $transliteration, $status, $pageId]);

        // Audit log
        $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, target_type, target_id, details, created_at)
                                VALUES (?, 'update_transcription', 'manuscript_pages', ?, ?, NOW())");
        $audit->execute([Auth::id(), $pageId, "Updated transcription, status='$status'"]);

        $_SESSION['flash_success'] = "Transcription saved successfully with status '" . htmlspecialchars($status) . "'!";
        Response::redirect('/transcribe?page_id=' . $pageId);
    }

    public function triggerOcr(): void {
        Auth::requireRole('researcher', 'admin');

        $pageId = (int)($_POST['page_id'] ?? 0);
        try {
            $result = OcrService::processPage($pageId);
            Response::success($result, "OCR processed successfully.");
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function triggerTranslate(): void {
        Auth::requireRole('researcher', 'admin');

        $pageId = (int)($_POST['page_id'] ?? 0);
        $targetLang = $_POST['target_lang'] ?? 'en';

        try {
            $res = TranslationService::translatePage($pageId, $targetLang, Auth::id());
            Response::success($res, "Translation generated.");
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function reviewTranslations(): void {
        Auth::requireRole('researcher', 'admin');

        $pdo = DB::getConnection();
        $translations = $pdo->query("SELECT t.*, p.page_no, m.id as manuscript_id, m.title as manuscript_title,
                                            l.name as source_language, u.name as reviewer_name
                                     FROM translations t
                                     JOIN manuscript_pages p ON t.page_id = p.id
                                     JOIN manuscripts m ON p.manuscript_id = m.id
                                     JOIN languages l ON m.language_id = l.id
                                     LEFT JOIN users u ON t.reviewer_id = u.id
                                     ORDER BY t.status = 'draft' DESC, t.created_at DESC")->fetchAll();

        require __DIR__ . '/../views/translations_review.php';
    }

    public function verifyTranslation(): void {
        Auth::requireRole('researcher', 'admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::validate()) {
            Response::error("Invalid request or CSRF token", 403);
        }

        $transId = (int)($_POST['translation_id'] ?? 0);
        $text = trim($_POST['text'] ?? '');
        $status = $_POST['status'] ?? 'verified';

        TranslationService::updateTranslation($transId, $text, $status, Auth::id());

        $_SESSION['flash_success'] = "Translation verified and updated to version status '" . htmlspecialchars($status) . "'.";
        Response::redirect('/translations/review');
    }
}

