<?php
// app/services/LinkDiscoveryService.php - Hidden Link Discovery Engine

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/EntityService.php';

class LinkDiscoveryService {
    /**
     * Scan candidate relationships for a target manuscript (or all published manuscripts)
     */
    public static function discoverLinks(?int $targetManuscriptId = null): array {
        $pdo = DB::getConnection();

        $query = "SELECT m.*, l.name as language_name, p.ancient_name as place_name, p.region 
                  FROM manuscripts m 
                  LEFT JOIN languages l ON m.language_id = l.id 
                  LEFT JOIN places p ON m.origin_place_id = p.id 
                  WHERE m.status = 'published'";
        
        $manuscripts = $pdo->query($query)->fetchAll();
        $discovered = [];

        $targets = $targetManuscriptId 
            ? array_filter($manuscripts, fn($m) => (int)$m['id'] === $targetManuscriptId)
            : $manuscripts;

        foreach ($targets as $docA) {
            foreach ($manuscripts as $docB) {
                if ($docA['id'] >= $docB['id']) {
                    continue; // Avoid self and duplicate pairings
                }

                // Check if an existing reviewed/seeded link is present
                $check = $pdo->prepare("SELECT id, status, score, reason_json FROM links WHERE (manuscript_a = ? AND manuscript_b = ?) OR (manuscript_a = ? AND manuscript_b = ?)");
                $check->execute([$docA['id'], $docB['id'], $docB['id'], $docA['id']]);
                $existing = $check->fetch();

                $analysis = self::evaluateConnection($docA, $docB);

                // If confirmed previously, retain high score
                if ($existing && $existing['status'] === 'confirmed') {
                    $analysis['score'] = max((int)$existing['score'], $analysis['score']);
                }

                if ($analysis['score'] >= 30 || $existing) {
                    $reasonJson = json_encode($analysis, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                    if ($existing) {
                        if ($existing['status'] === 'suggested') {
                            $upd = $pdo->prepare("UPDATE links SET score = ?, reason_json = ? WHERE id = ?");
                            $upd->execute([$analysis['score'], $reasonJson, $existing['id']]);
                        }
                        $discovered[] = [
                            'id' => $existing['id'],
                            'manuscript_a' => $docA['id'],
                            'manuscript_b' => $docB['id'],
                            'score' => $existing['status'] === 'confirmed' ? $existing['score'] : $analysis['score'],
                            'analysis' => $analysis,
                            'status' => $existing['status']
                        ];
                    } else {
                        $ins = $pdo->prepare("INSERT INTO links (manuscript_a, manuscript_b, score, reason_json, status, created_at) VALUES (?, ?, ?, ?, 'suggested', NOW())");
                        $ins->execute([$docA['id'], $docB['id'], $analysis['score'], $reasonJson]);
                        $newId = (int)$pdo->lastInsertId();

                        $discovered[] = [
                            'id' => $newId,
                            'manuscript_a' => $docA['id'],
                            'manuscript_b' => $docB['id'],
                            'score' => $analysis['score'],
                            'analysis' => $analysis,
                            'status' => 'suggested'
                        ];
                    }
                }
            }
        }

        return $discovered;
    }

    /**
     * Compute multi-factor connection score between two manuscripts
     */
    public static function evaluateConnection(array $docA, array $docB): array {
        $pdo = DB::getConnection();

        // 1. Fetch entities mentioned in Doc A and Doc B
        $entAStmt = $pdo->prepare("SELECT DISTINCT e.id, e.name, e.type, e.normalized_name, e.ref_id 
                                  FROM entity_mentions em 
                                  JOIN entities e ON em.entity_id = e.id 
                                  WHERE em.manuscript_id = ? AND e.merged_into IS NULL");
        $entAStmt->execute([$docA['id']]);
        $entsA = $entAStmt->fetchAll();

        $entBStmt = $pdo->prepare("SELECT DISTINCT e.id, e.name, e.type, e.normalized_name, e.ref_id 
                                  FROM entity_mentions em 
                                  JOIN entities e ON em.entity_id = e.id 
                                  WHERE em.manuscript_id = ? AND e.merged_into IS NULL");
        $entBStmt->execute([$docB['id']]);
        $entsB = $entBStmt->fetchAll();

        $sharedEntities = [];
        $relatedPersonLinks = [];

        // Direct shared entity matching
        foreach ($entsA as $ea) {
            foreach ($entsB as $eb) {
                if ($ea['normalized_name'] === $eb['normalized_name'] || EntityService::calculateSimilarity($ea['name'], $eb['name']) >= 75) {
                    $sharedEntities[] = $ea['name'] . ($ea['type'] ? " (" . ucfirst($ea['type']) . ")" : "");
                }

                // Check interpersonal genealogical / statecraft relationship
                if ($ea['type'] === 'person' && $eb['type'] === 'person' && !empty($ea['ref_id']) && !empty($eb['ref_id'])) {
                    $relStmt = $pdo->prepare("SELECT relation_type FROM person_relations 
                                              WHERE (person_a = ? AND person_b = ?) OR (person_a = ? AND person_b = ?)");
                    $relStmt->execute([$ea['ref_id'], $eb['ref_id'], $eb['ref_id'], $ea['ref_id']]);
                    $relation = $relStmt->fetchColumn();
                    if ($relation) {
                        $relatedPersonLinks[] = "{$ea['name']} ↔ {$eb['name']} (" . str_replace('-', ' ', $relation) . ")";
                    }
                }
            }
        }
        $sharedEntities = array_unique($sharedEntities);
        $relatedPersonLinks = array_unique($relatedPersonLinks);

        // 2. Temporal Proximity Scoring (Time proximity)
        $eraDiff = abs($docA['era_start'] - $docB['era_start']);
        if ($eraDiff <= 60) {
            $timeScore = 95;
        } elseif ($eraDiff <= 180) {
            $timeScore = 80;
        } elseif ($eraDiff <= 400) {
            $timeScore = 60;
        } elseif ($eraDiff <= 800) {
            $timeScore = 40;
        } else {
            $timeScore = 20;
        }

        // 3. Geographic Proximity & Realm Overlap
        $geoBoost = 0;
        $geoOverlap = "Different geographical realms";
        if ($docA['origin_place_id'] && $docB['origin_place_id']) {
            if ($docA['origin_place_id'] === $docB['origin_place_id']) {
                $geoBoost = 35;
                $geoOverlap = "Identical origin center (" . ($docA['place_name'] ?? 'Shared capital') . ")";
            } elseif (!empty($docA['region']) && !empty($docB['region']) && stripos($docA['region'], $docB['region']) !== false) {
                $geoBoost = 25;
                $geoOverlap = "Shared geopolitical region (" . $docA['region'] . ")";
            }
        }

        // 4. Keyword / Text Similarity (TF-IDF overlap on summary)
        $textA = ($docA['title'] ?? '') . ' ' . ($docA['summary'] ?? '');
        $textB = ($docB['title'] ?? '') . ' ' . ($docB['summary'] ?? '');
        $kwOverlap = self::calculateKeywordOverlap($textA, $textB);

        // 5. Total composite score calculation (0 - 100)
        $entityScore = count($sharedEntities) * 25 + count($relatedPersonLinks) * 30;
        $baseScore = ($entityScore * 0.45) + ($timeScore * 0.25) + ($geoBoost * 0.20) + ($kwOverlap * 100 * 0.10);
        $totalScore = (int) max(10, min(99, round($baseScore)));

        // Formulate scholarly explanation
        $summary = "Cross-corpus link between '" . $docA['title'] . "' and '" . $docB['title'] . "'";
        $explanation = "Historical correlation established through " . (count($sharedEntities) + count($relatedPersonLinks)) . " shared named entities and genealogical ties, " .
                       strtolower($geoOverlap) . ", and a temporal interval of " . $eraDiff . " years.";

        if (!empty($sharedEntities)) {
            $explanation .= " Prominent shared references: " . implode(", ", array_slice($sharedEntities, 0, 4)) . ".";
        }
        if (!empty($relatedPersonLinks)) {
            $explanation .= " Dynastic & statecraft ties: " . implode("; ", $relatedPersonLinks) . ".";
        }

        $allShared = array_merge($sharedEntities, array_map(fn($r) => "Link: $r", $relatedPersonLinks));

        return [
            'summary' => $summary,
            'shared_entities' => array_values($allShared),
            'time_proximity_score' => $timeScore,
            'geographic_overlap' => $geoOverlap,
            'era_delta_years' => $eraDiff,
            'score' => $totalScore,
            'explanation' => $explanation,
            'metrics' => [
                'keyword_overlap' => round($kwOverlap, 2),
                'entity_cooccurrence' => count($sharedEntities),
                'relational_links' => count($relatedPersonLinks),
                'era_delta_years' => $eraDiff
            ]
        ];
    }

    /**
     * Compute token Jaccard similarity between texts
     */
    private static function calculateKeywordOverlap(string $textA, string $textB): float {
        $stopWords = ['the', 'and', 'of', 'in', 'to', 'a', 'is', 'that', 'for', 'it', 'as', 'was', 'with', 'by', 'on', 'at', 'from', 'this', 'an', 'ka', 'ki', 'ke', 'aur', 'ko', 'se', 'me', 'hai'];
        $clean = function($text) use ($stopWords) {
            $words = preg_split('/\W+/u', mb_strtolower($text, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY);
            return array_diff($words, $stopWords);
        };

        $wordsA = array_unique($clean($textA));
        $wordsB = array_unique($clean($textB));

        if (empty($wordsA) || empty($wordsB)) return 0.0;

        $intersection = array_intersect($wordsA, $wordsB);
        $union = array_unique(array_merge($wordsA, $wordsB));

        return count($intersection) / count($union);
    }

    /**
     * Fetch network graph payload for vis-network or D3.js
     */
    public static function getGraphData(?int $rootId = null, int $depth = 2): array {
        $pdo = DB::getConnection();

        $manuscripts = $pdo->query("SELECT id, title, era_start, author, language_id FROM manuscripts WHERE status = 'published'")->fetchAll();
        $links = $pdo->query("SELECT l.*, ma.title as title_a, mb.title as title_b 
                              FROM links l 
                              JOIN manuscripts ma ON l.manuscript_a = ma.id 
                              JOIN manuscripts mb ON l.manuscript_b = mb.id 
                              WHERE l.status != 'rejected'")->fetchAll();

        $nodes = [];
        $edges = [];

        // Manuscript nodes
        foreach ($manuscripts as $m) {
            $cleanTitle = class_exists('ViewHelper') ? ViewHelper::cleanDocTitle($m['title']) : $m['title'];
            $nodes[] = [
                'id' => 'm_' . $m['id'],
                'label' => $cleanTitle,
                'group' => 'manuscript',
                'title' => "Manuscript: {$cleanTitle}<br>Author: {$m['author']}<br>Era: " . Calendar::formatYear((int)$m['era_start']),
                'shape' => 'dot',
                'size' => 24,
                'color' => '#8b2635'
            ];
        }

        // Links between manuscripts
        foreach ($links as $l) {
            $edges[] = [
                'from' => 'm_' . $l['manuscript_a'],
                'to' => 'm_' . $l['manuscript_b'],
                'value' => (int)$l['score'],
                'title' => "Connection Score: {$l['score']}%<br>Status: " . ucfirst($l['status']),
                'color' => $l['status'] === 'confirmed' ? '#2e7d32' : '#f57c00',
                'dashes' => $l['status'] === 'suggested'
            ];
        }

        // Add prominent persons
        $persons = $pdo->query("SELECT p.id, p.name, p.role FROM persons p LIMIT 15")->fetchAll();
        foreach ($persons as $p) {
            $pNodeId = 'p_' . $p['id'];
            $cleanName = class_exists('ViewHelper') ? ViewHelper::cleanPersonName($p['name']) : $p['name'];
            $cleanRole = class_exists('ViewHelper') ? ViewHelper::cleanRole($p['role']) : $p['role'];
            $nodes[] = [
                'id' => $pNodeId,
                'label' => $cleanName,
                'group' => 'person',
                'title' => "Historical Figure: {$cleanName}<br>Role: {$cleanRole}",
                'shape' => 'diamond',
                'size' => 18,
                'color' => '#1a365d'
            ];
        }

        // Add entity mention links from person to manuscript
        $mentions = $pdo->query("SELECT em.manuscript_id, e.ref_id, e.name 
                                 FROM entity_mentions em 
                                 JOIN entities e ON em.entity_id = e.id 
                                 WHERE e.type = 'person' AND e.ref_id IS NOT NULL")->fetchAll();
        foreach ($mentions as $men) {
            $edges[] = [
                'from' => 'p_' . $men['ref_id'],
                'to' => 'm_' . $men['manuscript_id'],
                'value' => 40,
                'title' => "Mentioned in manuscript",
                'color' => '#a0aec0',
                'arrows' => 'to'
            ];
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges
        ];
    }
}

