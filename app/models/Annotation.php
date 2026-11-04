<?php
// app/models/Annotation.php - Scholarly annotations and threaded comments

require_once __DIR__ . '/../../config/db.php';

class Annotation {
    public static function create(array $data): int {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("INSERT INTO annotations (page_id, user_id, type, coordinates, note, visibility, parent_id, created_at)
                               VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $data['page_id'],
            $data['user_id'],
            $data['type'] ?? 'reference',
            $data['coordinates'] ?? null,
            $data['note'],
            $data['visibility'] ?? 'public',
            $data['parent_id'] ?? null
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function getForPage(int $pageId, ?int $currentUserId = null): array {
        $pdo = DB::getConnection();
        $sql = "SELECT a.*, u.name as author_name, u.role as author_role
                FROM annotations a
                JOIN users u ON a.user_id = u.id
                WHERE a.page_id = ? AND (a.visibility = 'public' OR a.user_id = ?)
                ORDER BY a.created_at ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$pageId, $currentUserId]);
        return $stmt->fetchAll();
    }
}

