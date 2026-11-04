<?php
// app/models/Person.php - Historical Persons and Genealogical Network

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helpers/Calendar.php';

class Person {
    public static function all(): array {
        $pdo = DB::getConnection();
        return $pdo->query("SELECT p.*,
                                   (SELECT COUNT(*) FROM person_relations WHERE person_a = p.id OR person_b = p.id) as relation_count
                            FROM persons p 
                            ORDER BY p.birth_year ASC, p.name ASC")->fetchAll();
    }

    public static function find(int $id): ?array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM persons WHERE id = ?");
        $stmt->execute([$id]);
        $person = $stmt->fetch();
        if (!$person) return null;

        $person['birth_formatted'] = $person['birth_year'] !== null ? Calendar::formatYear((int)$person['birth_year']) : 'Unknown';
        $person['death_formatted'] = $person['death_year'] !== null ? Calendar::formatYear((int)$person['death_year']) : 'Unknown';

        // Connected Monuments built by this person
        $monStmt = $pdo->prepare("SELECT * FROM monuments WHERE person_id = ? OR built_by LIKE ?");
        $wildcard = "%" . $person['name'] . "%";
        $monStmt->execute([$id, $wildcard]);
        $person['monuments'] = $monStmt->fetchAll();

        // Direct and indirect relationships
        $relStmt = $pdo->prepare("SELECT pr.*, 
                                         pa.name as person_a_name, pa.role as person_a_role,
                                         pb.name as person_b_name, pb.role as person_b_role,
                                         m.title as source_manuscript_title
                                  FROM person_relations pr
                                  JOIN persons pa ON pr.person_a = pa.id
                                  JOIN persons pb ON pr.person_b = pb.id
                                  LEFT JOIN manuscripts m ON pr.source_manuscript_id = m.id
                                  WHERE pr.person_a = ? OR pr.person_b = ?");
        $relStmt->execute([$id, $id]);
        $rawRelations = $relStmt->fetchAll();
        $formattedRelations = [];
        foreach ($rawRelations as $r) {
            $isA = ($r['person_a'] == $id);
            $formattedRelations[] = [
                'id' => (int)$r['id'],
                'related_person_id' => (int)($isA ? $r['person_b'] : $r['person_a']),
                'related_person_name' => $isA ? $r['person_b_name'] : $r['person_a_name'],
                'related_person_role' => $isA ? $r['person_b_role'] : $r['person_a_role'],
                'relationship_type' => str_replace('-', ' ', $r['relation_type'] ?? 'ally'),
                'notes' => $r['notes'] ?? ''
            ];
        }
        $person['relations'] = $formattedRelations;

        // Linked manuscripts mentioning this person
        $mStmt = $pdo->prepare("SELECT DISTINCT m.id, m.title, m.author, m.era_start, m.thumbnail 
                                FROM entity_mentions em 
                                JOIN entities e ON em.entity_id = e.id 
                                JOIN manuscripts m ON em.manuscript_id = m.id 
                                WHERE e.type = 'person' AND e.ref_id = ?");
        $mStmt->execute([$id]);
        $person['manuscripts'] = $mStmt->fetchAll();

        return $person;
    }

    public static function create(array $data): int {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("INSERT INTO persons (name, alt_names, birth_year, death_year, role, bio, portrait_url, created_at)
                               VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            trim($data['name']),
            trim($data['alt_names'] ?? ''),
            !empty($data['birth_year']) ? (int)$data['birth_year'] : null,
            !empty($data['death_year']) ? (int)$data['death_year'] : null,
            trim($data['role']),
            trim($data['bio'] ?? ''),
            trim($data['portrait_url'] ?? '/assets/img/persons/ashoka.png')
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("UPDATE persons SET 
                                name = ?, 
                                alt_names = ?, 
                                birth_year = ?, 
                                death_year = ?, 
                                role = ?, 
                                bio = ?, 
                                portrait_url = ?
                               WHERE id = ?");
        return $stmt->execute([
            trim($data['name']),
            trim($data['alt_names'] ?? ''),
            !empty($data['birth_year']) ? (int)$data['birth_year'] : null,
            !empty($data['death_year']) ? (int)$data['death_year'] : null,
            trim($data['role']),
            trim($data['bio'] ?? ''),
            trim($data['portrait_url'] ?? '/assets/img/persons/ashoka.png'),
            $id
        ]);
    }

    public static function delete(int $id): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("DELETE FROM persons WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Relationship tree / graph representation
     */
    public static function getGenealogyTree(int $rootId): array {
        $pdo = DB::getConnection();
        $relations = $pdo->query("SELECT pr.*, 
                                         pa.name as a_name, pa.role as a_role,
                                         pb.name as b_name, pb.role as b_role
                                  FROM person_relations pr
                                  JOIN persons pa ON pr.person_a = pa.id
                                  JOIN persons pb ON pr.person_b = pb.id")->fetchAll();

        $nodes = [];
        $edges = [];

        foreach ($relations as $r) {
            $nodes[$r['person_a']] = [
                'id' => (int)$r['person_a'],
                'label' => $r['a_name'],
                'role' => $r['a_role'],
                'shape' => 'box',
                'color' => '#1e3a8a'
            ];
            $nodes[$r['person_b']] = [
                'id' => (int)$r['person_b'],
                'label' => $r['b_name'],
                'role' => $r['b_role'],
                'shape' => 'box',
                'color' => '#1e3a8a'
            ];

            $edges[] = [
                'from' => (int)$r['person_a'],
                'to' => (int)$r['person_b'],
                'label' => str_replace('-', ' ', $r['relation_type'] ?? 'ally'),
                'arrows' => 'to',
                'notes' => $r['notes']
            ];
        }

        return [
            'nodes' => array_values($nodes),
            'edges' => $edges
        ];
    }
}
