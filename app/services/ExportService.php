<?php
// app/services/ExportService.php - Academic citation and multi-format data export

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helpers/Calendar.php';

class ExportService {
    /**
     * Generate standard academic citations for a manuscript
     */
    public static function generateCitations(int $manuscriptId): array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT m.*, l.name as language_name, p.ancient_name as place_name, p.country 
                               FROM manuscripts m 
                               LEFT JOIN languages l ON m.language_id = l.id 
                               LEFT JOIN places p ON m.origin_place_id = p.id 
                               WHERE m.id = ?");
        $stmt->execute([$manuscriptId]);
        $m = $stmt->fetch();

        if (!$m) {
            throw new Exception("Manuscript not found");
        }

        $author = !empty($m['author']) ? $m['author'] : 'Anonymous';
        $title = $m['title'];
        $repo = $m['source_repository'];
        $callNo = $m['call_number'] ? ", Call No: " . $m['call_number'] : "";
        $era = Calendar::formatYear((int)$m['era_start']);
        $today = date('F j, Y');
        $url = "http://localhost:8000/manuscript?id=" . $m['id'];

        return [
            'chicago' => "{$author}. *{$title}*. {$era}. {$repo}{$callNo}. Digital surrogate accessed {$today}, {$url}.",
            'mla' => "{$author}. *{$title}*. {$era}, {$repo}{$callNo}. *Manuscript Intelligence Archive*, {$url}. Accessed {$today}.",
            'apa' => "{$author}. ({$era}). *{$title}* [Manuscript codex]. {$repo}{$callNo}. {$url}"
        ];
    }

    /**
     * Export historical events as CSV string
     */
    public static function exportEventsCsv(?int $placeId = null, ?string $category = null): string {
        $pdo = DB::getConnection();
        $query = "SELECT e.id, e.title, e.description, e.date_text, e.sort_year, e.category, e.confidence,
                         p.ancient_name as place_ancient, p.modern_name as place_modern
                  FROM events e
                  LEFT JOIN places p ON e.place_id = p.id
                  WHERE e.status = 'approved'";
        $params = [];
        if ($placeId) {
            $query .= " AND e.place_id = ?";
            $params[] = $placeId;
        }
        if ($category) {
            $query .= " AND e.category = ?";
            $params[] = $category;
        }
        $query .= " ORDER BY e.sort_year ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $events = $stmt->fetchAll();

        $f = fopen('php://memory', 'r+');
        fputcsv($f, ['ID', 'Title', 'Date Text', 'Normalized Sort Year', 'Category', 'Place (Ancient)', 'Place (Modern)', 'Confidence', 'Description']);
        foreach ($events as $row) {
            fputcsv($f, [
                $row['id'],
                $row['title'],
                $row['date_text'],
                $row['sort_year'],
                $row['category'],
                $row['place_ancient'] ?? 'N/A',
                $row['place_modern'] ?? 'N/A',
                $row['confidence'],
                $row['description']
            ]);
        }
        rewind($f);
        return stream_get_contents($f);
    }

    /**
     * Export places & routes as GeoJSON
     */
    public static function exportPlacesGeoJson(): array {
        $pdo = DB::getConnection();
        $places = $pdo->query("SELECT p.*, 
                               (SELECT COUNT(*) FROM manuscripts WHERE origin_place_id = p.id) as manuscript_count,
                               (SELECT COUNT(*) FROM events WHERE place_id = p.id) as event_count
                               FROM places p")->fetchAll();

        $features = [];
        foreach ($places as $p) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$p['lng'], (float)$p['lat']]
                ],
                'properties' => [
                    'id' => (int)$p['id'],
                    'ancient_name' => $p['ancient_name'],
                    'modern_name' => $p['modern_name'],
                    'region' => $p['region'],
                    'country' => $p['country'],
                    'manuscripts' => (int)$p['manuscript_count'],
                    'events' => (int)$p['event_count'],
                    'notes' => $p['notes']
                ]
            ];
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features
        ];
    }
}

