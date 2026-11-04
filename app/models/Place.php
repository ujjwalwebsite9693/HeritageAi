<?php
// app/models/Place.php - Historical Places and Geocoding

require_once __DIR__ . '/../../config/db.php';

class Place {
    public static function all(): array {
        $pdo = DB::getConnection();
        return $pdo->query("SELECT p.*, 
                                   (SELECT COUNT(*) FROM manuscripts WHERE origin_place_id = p.id) as manuscript_count,
                                   (SELECT COUNT(*) FROM events WHERE place_id = p.id) as event_count
                            FROM places p
                            ORDER BY p.ancient_name ASC")->fetchAll();
    }

    public static function find(int $id): ?array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM places WHERE id = ?");
        $stmt->execute([$id]);
        $place = $stmt->fetch();
        if (!$place) return null;

        // Associated manuscripts
        $mStmt = $pdo->prepare("SELECT id, title, era_start, author, thumbnail FROM manuscripts WHERE origin_place_id = ?");
        $mStmt->execute([$id]);
        $place['manuscripts'] = $mStmt->fetchAll();

        // Associated events
        $eStmt = $pdo->prepare("SELECT * FROM events WHERE place_id = ? ORDER BY sort_year ASC");
        $eStmt->execute([$id]);
        $place['events'] = $eStmt->fetchAll();

        return $place;
    }
}

