<?php
// app/models/Link.php - Cross-manuscript discovered links and review workflow

require_once __DIR__ . '/../../config/db.php';

class Link {
    public static function all(?string $status = null): array {
        $pdo = DB::getConnection();
        $sql = "SELECT l.*, 
                       ma.title as doc_a_title, ma.author as doc_a_author, ma.thumbnail as doc_a_thumb,
                       mb.title as doc_b_title, mb.author as doc_b_author, mb.thumbnail as doc_b_thumb,
                       u.name as reviewer_name
                FROM links l
                JOIN manuscripts ma ON l.manuscript_a = ma.id
                JOIN manuscripts mb ON l.manuscript_b = mb.id
                LEFT JOIN users u ON l.reviewed_by = u.id";

        if ($status) {
            $sql .= " WHERE l.status = ?";
            $stmt = $pdo->prepare($sql . " ORDER BY l.score DESC");
            $stmt->execute([$status]);
        } else {
            $stmt = $pdo->prepare($sql . " ORDER BY l.status = 'suggested' DESC, l.score DESC");
            $stmt->execute();
        }

        $links = $stmt->fetchAll();
        foreach ($links as &$link) {
            $link['reason'] = json_decode($link['reason_json'], true) ?: [];
        }
        return $links;
    }

    public static function updateStatus(int $id, string $status, int $userId): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("UPDATE links SET status = ?, reviewed_by = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$status, $userId, $id]);

        $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, target_type, target_id, details, created_at)
                                VALUES (?, 'review_link', 'links', ?, ?, NOW())");
        $audit->execute([$userId, $id, "Updated link status to '$status'"]);

        return $result;
    }
}

