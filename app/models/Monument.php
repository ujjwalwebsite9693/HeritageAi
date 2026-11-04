<?php
// app/models/Monument.php - Model for Historical Monuments & Heritage Sites

require_once __DIR__ . '/../../config/db.php';

class Monument {
    public static function all(): array {
        $pdo = DB::getConnection();
        $sql = "SELECT m.*, p.name as builder_name, p.portrait_url as builder_portrait,
                       pl.ancient_name as place_ancient, pl.modern_name as place_modern
                FROM monuments m
                LEFT JOIN persons p ON m.person_id = p.id
                LEFT JOIN places pl ON m.place_id = pl.id
                ORDER BY m.built_year DESC";
        $stmt = $pdo->query($sql);
        $monuments = $stmt->fetchAll();

        foreach ($monuments as &$m) {
            $m['highlights_array'] = json_decode($m['highlights'] ?? '[]', true) ?: [];
        }
        return $monuments;
    }

    public static function find(int $id): ?array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT m.*, p.name as builder_name, p.role as builder_role, p.portrait_url as builder_portrait,
                                      pl.ancient_name as place_ancient, pl.modern_name as place_modern, pl.lat, pl.lng
                               FROM monuments m
                               LEFT JOIN persons p ON m.person_id = p.id
                               LEFT JOIN places pl ON m.place_id = pl.id
                               WHERE m.id = ?");
        $stmt->execute([$id]);
        $monument = $stmt->fetch();
        if (!$monument) return null;

        $monument['highlights_array'] = json_decode($monument['highlights'] ?? '[]', true) ?: [];

        // Linked manuscripts referencing this site or its builder
        $mStmt = $pdo->prepare("SELECT DISTINCT m.id, m.title, m.author, m.era_start, m.thumbnail 
                                FROM manuscripts m 
                                WHERE m.origin_place_id = ? OR m.summary LIKE ? 
                                LIMIT 4");
        $wildcard = "%" . $monument['name'] . "%";
        $mStmt->execute([$monument['place_id'], $wildcard]);
        $monument['connected_manuscripts'] = $mStmt->fetchAll();

        return $monument;
    }

    public static function create(array $data): int {
        $pdo = DB::getConnection();
        $highlights = !empty($data['highlights']) ? json_encode(array_filter(array_map('trim', explode(',', $data['highlights'])))) : '[]';

        $stmt = $pdo->prepare("INSERT INTO monuments (name, alternate_name, built_by, person_id, built_year, built_year_text, place_id, location_text, era_label, architectural_style, image_url, summary, history, visiting_info, highlights)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            trim($data['name']),
            trim($data['alternate_name'] ?? ''),
            trim($data['built_by']),
            !empty($data['person_id']) ? (int)$data['person_id'] : null,
            !empty($data['built_year']) ? (int)$data['built_year'] : 1600,
            trim($data['built_year_text'] ?? 'Historical Era'),
            !empty($data['place_id']) ? (int)$data['place_id'] : null,
            trim($data['location_text'] ?? 'India'),
            trim($data['era_label'] ?? 'Mughal / Classical'),
            trim($data['architectural_style'] ?? 'Indo-Islamic'),
            trim($data['image_url'] ?? '/assets/img/monuments/taj_mahal.jpg'),
            trim($data['summary']),
            trim($data['history'] ?? $data['summary']),
            trim($data['visiting_info'] ?? 'Open daily for visitors from sunrise to sunset.'),
            $highlights
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $pdo = DB::getConnection();
        $highlights = !empty($data['highlights']) ? json_encode(array_filter(array_map('trim', explode(',', $data['highlights'])))) : '[]';

        $stmt = $pdo->prepare("UPDATE monuments SET 
                                name = ?, 
                                alternate_name = ?, 
                                built_by = ?, 
                                person_id = ?, 
                                built_year = ?, 
                                built_year_text = ?, 
                                place_id = ?, 
                                location_text = ?, 
                                era_label = ?, 
                                architectural_style = ?, 
                                image_url = ?, 
                                summary = ?, 
                                history = ?, 
                                visiting_info = ?, 
                                highlights = ?
                               WHERE id = ?");
        return $stmt->execute([
            trim($data['name']),
            trim($data['alternate_name'] ?? ''),
            trim($data['built_by']),
            !empty($data['person_id']) ? (int)$data['person_id'] : null,
            !empty($data['built_year']) ? (int)$data['built_year'] : 1600,
            trim($data['built_year_text'] ?? 'Historical Era'),
            !empty($data['place_id']) ? (int)$data['place_id'] : null,
            trim($data['location_text'] ?? 'India'),
            trim($data['era_label'] ?? 'Mughal / Classical'),
            trim($data['architectural_style'] ?? 'Indo-Islamic'),
            trim($data['image_url'] ?? '/assets/img/monuments/taj_mahal.jpg'),
            trim($data['summary']),
            trim($data['history'] ?? $data['summary']),
            trim($data['visiting_info'] ?? 'Open daily for visitors from sunrise to sunset.'),
            $highlights,
            $id
        ]);
    }

    public static function delete(int $id): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("DELETE FROM monuments WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
