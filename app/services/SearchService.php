<?php
// app/services/SearchService.php - Multi-facet archive search with MySQL FULLTEXT and Soundex fallback

require_once __DIR__ . '/../../config/db.php';

class SearchService {
    public static function search(array $params = []): array {
        $pdo = DB::getConnection();
        $q = trim($params['q'] ?? '');
        $langId = !empty($params['lang']) ? (int)$params['lang'] : null;
        $placeId = !empty($params['place']) ? (int)$params['place'] : null;
        $eraStart = isset($params['era_from']) && $params['era_from'] !== '' ? (int)$params['era_from'] : null;
        $eraEnd = isset($params['era_to']) && $params['era_to'] !== '' ? (int)$params['era_to'] : null;
        $script = trim($params['script'] ?? '');
        $limit = min((int)($params['limit'] ?? 20), 100);
        $offset = max((int)($params['offset'] ?? 0), 0);

        $conditions = ["m.status = 'published'"];
        $bindings = [];

        // Fulltext or LIKE/Soundex search
        $ftJoinPages = false;
        $ftJoinTranslations = false;

        if ($q !== '') {
            // First check if FULLTEXT matches
            $ftCondition = "(MATCH(m.title, m.author, m.summary) AGAINST(? IN BOOLEAN MODE)
                OR m.title LIKE ? 
                OR m.author LIKE ?
                OR SOUNDEX(m.title) = SOUNDEX(?)
                OR SOUNDEX(m.author) = SOUNDEX(?)
                OR EXISTS (
                    SELECT 1 FROM manuscript_pages mp 
                    WHERE mp.manuscript_id = m.id 
                    AND (MATCH(mp.ocr_text, mp.transcription) AGAINST(? IN BOOLEAN MODE) 
                         OR mp.ocr_text LIKE ? 
                         OR mp.transcription LIKE ?)
                )
                OR EXISTS (
                    SELECT 1 FROM translations tr
                    JOIN manuscript_pages mp2 ON tr.page_id = mp2.id
                    WHERE mp2.manuscript_id = m.id 
                    AND (MATCH(tr.text) AGAINST(? IN BOOLEAN MODE) OR tr.text LIKE ?)
                )
                OR EXISTS (
                    SELECT 1 FROM entity_mentions em
                    JOIN entities e ON em.entity_id = e.id
                    WHERE em.manuscript_id = m.id
                    AND (
                        SOUNDEX(SUBSTRING_INDEX(e.name, ' ', 1)) = SOUNDEX(?)
                        OR SOUNDEX(SUBSTRING_INDEX(e.normalized_name, ' ', 1)) = SOUNDEX(?)
                        OR e.name LIKE ?
                        OR e.normalized_name LIKE ?
                    )
                ))";

            $conditions[] = $ftCondition;
            $wildcard = "%$q%";
            $bindings[] = $q;
            $bindings[] = $wildcard;
            $bindings[] = $wildcard;
            $bindings[] = $q;
            $bindings[] = $q;
            $bindings[] = $q;
            $bindings[] = $wildcard;
            $bindings[] = $wildcard;
            $bindings[] = $q;
            $bindings[] = $wildcard;
            $bindings[] = $q;
            $bindings[] = $q;
            $bindings[] = $wildcard;
            $bindings[] = $wildcard;
        }

        if ($langId !== null) {
            $conditions[] = "m.language_id = ?";
            $bindings[] = $langId;
        }

        if ($placeId !== null) {
            $conditions[] = "m.origin_place_id = ?";
            $bindings[] = $placeId;
        }

        if ($eraStart !== null) {
            $conditions[] = "m.era_end >= ?";
            $bindings[] = $eraStart;
        }

        if ($eraEnd !== null) {
            $conditions[] = "m.era_start <= ?";
            $bindings[] = $eraEnd;
        }

        if ($script !== '') {
            $conditions[] = "m.script LIKE ?";
            $bindings[] = "%$script%";
        }

        $whereSql = "WHERE " . implode(' AND ', $conditions);

        // Count total matching records
        $countSql = "SELECT COUNT(DISTINCT m.id) FROM manuscripts m $whereSql";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($bindings);
        $total = (int)$countStmt->fetchColumn();

        // Fetch results with joins for language and origin place
        $sql = "SELECT m.*, 
                       l.name AS language_name, l.script AS default_script, l.direction,
                       p.ancient_name AS place_ancient, p.modern_name AS place_modern, p.lat, p.lng,
                       (SELECT COUNT(*) FROM manuscript_pages WHERE manuscript_id = m.id) AS page_count,
                       (SELECT COUNT(*) FROM links WHERE (manuscript_a = m.id OR manuscript_b = m.id) AND status = 'confirmed') AS confirmed_links_count
                FROM manuscripts m
                LEFT JOIN languages l ON m.language_id = l.id
                LEFT JOIN places p ON m.origin_place_id = p.id
                $whereSql
                ORDER BY m.era_start ASC, m.id ASC
                LIMIT $limit OFFSET $offset";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($bindings);
        $results = $stmt->fetchAll();

        return [
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'results' => $results,
            'query' => $q
        ];
    }

    /**
     * Get facet counts for filters (languages, eras, places)
     */
    public static function getFacets(): array {
        $pdo = DB::getConnection();

        $languages = $pdo->query("SELECT l.id, l.name, COUNT(m.id) as count 
                                  FROM languages l 
                                  LEFT JOIN manuscripts m ON m.language_id = l.id AND m.status = 'published'
                                  GROUP BY l.id, l.name
                                  ORDER BY count DESC")->fetchAll();

        $places = $pdo->query("SELECT p.id, p.ancient_name, p.modern_name, COUNT(m.id) as count
                               FROM places p
                               JOIN manuscripts m ON m.origin_place_id = p.id AND m.status = 'published'
                               GROUP BY p.id, p.ancient_name, p.modern_name
                               ORDER BY count DESC")->fetchAll();

        $eras = [
            ['label' => 'Ancient / Maurya & Classical (Before 500 CE)', 'from' => -500, 'to' => 500],
            ['label' => 'Early Medieval (500 CE – 1200 CE)', 'from' => 500, 'to' => 1200],
            ['label' => 'Late Medieval / Sultanate (1200 CE – 1526 CE)', 'from' => 1200, 'to' => 1526],
            ['label' => 'Early Modern / Mughal & Maritime (1526 CE – 1750 CE)', 'from' => 1526, 'to' => 1750],
        ];

        return [
            'languages' => $languages,
            'places' => $places,
            'eras' => $eras
        ];
    }
}
