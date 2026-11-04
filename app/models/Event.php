<?php
// app/models/Event.php - Events data model

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helpers/Calendar.php';

class Event {
    public static function all(array $filters = []): array {
        $pdo = DB::getConnection();
        $conditions = ["1=1"];
        $params = [];

        if (!empty($filters['from'])) {
            $conditions[] = "e.sort_year >= ?";
            $params[] = (int)$filters['from'];
        }
        if (!empty($filters['to'])) {
            $conditions[] = "e.sort_year <= ?";
            $params[] = (int)$filters['to'];
        }
        if (!empty($filters['place'])) {
            $conditions[] = "e.place_id = ?";
            $params[] = (int)$filters['place'];
        }
        if (!empty($filters['category'])) {
            $conditions[] = "e.category = ?";
            $params[] = $filters['category'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = "e.status = ?";
            $params[] = $filters['status'];
        } else {
            $conditions[] = "e.status = 'approved'";
        }

        $where = implode(' AND ', $conditions);
        $sql = "SELECT e.*, p.ancient_name as place_ancient, p.modern_name as place_modern, p.lat, p.lng
                FROM events e
                LEFT JOIN places p ON e.place_id = p.id
                WHERE $where
                ORDER BY e.sort_year ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $events = $stmt->fetchAll();

        // Attach linked source manuscripts
        foreach ($events as &$ev) {
            $srcStmt = $pdo->prepare("SELECT es.*, m.title as manuscript_title 
                                      FROM event_sources es 
                                      JOIN manuscripts m ON es.manuscript_id = m.id 
                                      WHERE es.event_id = ?");
            $srcStmt->execute([$ev['id']]);
            $ev['sources'] = $srcStmt->fetchAll();
            $ev['formatted_year'] = Calendar::formatYear((int)$ev['sort_year']);
        }

        return $events;
    }

    public static function create(array $data): int {
        $pdo = DB::getConnection();
        $sortYear = Calendar::normalizeToCe((int)$data['year_value'], $data['calendar_type'] ?? 'Gregorian');

        $stmt = $pdo->prepare("INSERT INTO events (title, description, date_text, sort_year, end_year, calendar_type, place_id, category, confidence, status)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['date_text'],
            $sortYear,
            $data['end_year'] ?? null,
            $data['calendar_type'] ?? 'Gregorian',
            $data['place_id'] ?? null,
            $data['category'] ?? 'politics',
            $data['confidence'] ?? 0.90,
            $data['status'] ?? 'approved'
        ]);
        $eventId = (int)$pdo->lastInsertId();

        if (!empty($data['manuscript_id'])) {
            $src = $pdo->prepare("INSERT INTO event_sources (event_id, manuscript_id, page_id, quote_ref) VALUES (?, ?, ?, ?)");
            $src->execute([$eventId, $data['manuscript_id'], $data['page_id'] ?? null, $data['quote_ref'] ?? null]);
        }

        return $eventId;
    }

    public static function updateStatus(int $id, string $status): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("UPDATE events SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}

