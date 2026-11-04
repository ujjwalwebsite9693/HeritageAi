<?php
// app/models/Manuscript.php - Data model for manuscripts & pages

require_once __DIR__ . '/../../config/db.php';

class Manuscript {
    public static function find(int $id): ?array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT m.*, l.name as language_name, l.script as default_script, l.direction,
                                      p.ancient_name as place_ancient, p.modern_name as place_modern, p.lat, p.lng,
                                      u.name as uploader_name
                               FROM manuscripts m
                               LEFT JOIN languages l ON m.language_id = l.id
                               LEFT JOIN places p ON m.origin_place_id = p.id
                               LEFT JOIN users u ON m.uploaded_by = u.id
                               WHERE m.id = ?");
        $stmt->execute([$id]);
        $manuscript = $stmt->fetch();
        if (!$manuscript) return null;

        // Fetch pages
        $pageStmt = $pdo->prepare("SELECT * FROM manuscript_pages WHERE manuscript_id = ? ORDER BY page_no ASC");
        $pageStmt->execute([$id]);
        $manuscript['pages'] = $pageStmt->fetchAll();

        // Fetch page translations
        foreach ($manuscript['pages'] as &$page) {
            $tStmt = $pdo->prepare("SELECT t.*, u.name as reviewer_name 
                                    FROM translations t 
                                    LEFT JOIN users u ON t.reviewer_id = u.id 
                                    WHERE t.page_id = ? 
                                    ORDER BY t.version DESC");
            $tStmt->execute([$page['id']]);
            $page['translations'] = $tStmt->fetchAll();

            // Fetch page annotations
            $aStmt = $pdo->prepare("SELECT a.*, u.name as author_name, u.role as author_role 
                                    FROM annotations a 
                                    JOIN users u ON a.user_id = u.id 
                                    WHERE a.page_id = ? 
                                    ORDER BY a.created_at ASC");
            $aStmt->execute([$page['id']]);
            $page['annotations'] = $aStmt->fetchAll();
        }

        return $manuscript;
    }

    public static function create(array $data): int {
        $pdo = DB::getConnection();
        $sql = "INSERT INTO manuscripts (title, author, language_id, script, era_start, era_end, origin_place_id, 
                                        source_repository, call_number, license, access_level, status, uploaded_by, 
                                        thumbnail, summary, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['title'],
            $data['author'] ?? 'Anonymous',
            $data['language_id'],
            $data['script'],
            $data['era_start'],
            $data['era_end'] ?? null,
            $data['origin_place_id'] ?? null,
            $data['source_repository'],
            $data['call_number'] ?? null,
            $data['license'] ?? 'Public Domain',
            $data['access_level'] ?? 'public',
            $data['status'] ?? 'published',
            $data['uploaded_by'] ?? null,
            $data['thumbnail'] ?? null,
            $data['summary'] ?? null
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function addPage(int $manuscriptId, int $pageNo, string $imagePath, ?string $transcription = null): int {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("INSERT INTO manuscript_pages (manuscript_id, page_no, image_path, ocr_text, transcription, status, created_at)
                               VALUES (?, ?, ?, ?, ?, 'ocr_draft', NOW())");
        $stmt->execute([$manuscriptId, $pageNo, $imagePath, $transcription, $transcription]);
        return (int)$pdo->lastInsertId();
    }

    public static function getConnectedLinks(int $manuscriptId): array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT l.*, 
                                      ma.title as doc_a_title, mb.title as doc_b_title,
                                      u.name as reviewer_name
                               FROM links l
                               JOIN manuscripts ma ON l.manuscript_a = ma.id
                               JOIN manuscripts mb ON l.manuscript_b = mb.id
                               LEFT JOIN users u ON l.reviewed_by = u.id
                               WHERE (l.manuscript_a = ? OR l.manuscript_b = ?) AND l.status != 'rejected'
                               ORDER BY l.score DESC");
        $stmt->execute([$manuscriptId, $manuscriptId]);
        return $stmt->fetchAll();
    }
}

