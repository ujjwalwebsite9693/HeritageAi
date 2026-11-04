<?php
// app/services/EntityService.php - Entity extraction, fuzzy matching, and merge resolution

require_once __DIR__ . '/../../config/db.php';

class EntityService {
    /**
     * Find potential duplicate entities using Soundex, Levenshtein, and alias matching
     */
    public static function findDuplicates(): array {
        $pdo = DB::getConnection();
        $entities = $pdo->query("SELECT * FROM entities WHERE merged_into IS NULL ORDER BY name ASC")->fetchAll();

        $duplicates = [];
        $count = count($entities);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $e1 = $entities[$i];
                $e2 = $entities[$j];

                // Only compare entities of same type
                if ($e1['type'] !== $e2['type']) {
                    continue;
                }

                $score = self::calculateSimilarity($e1['name'], $e2['name']);
                if ($score >= 65) {
                    $duplicates[] = [
                        'entity_a' => $e1,
                        'entity_b' => $e2,
                        'similarity_score' => $score,
                        'type' => $e1['type'],
                        'reason' => self::getMatchReason($e1['name'], $e2['name'], $score)
                    ];
                }
            }
        }

        usort($duplicates, fn($a, $b) => $b['similarity_score'] <=> $a['similarity_score']);
        return $duplicates;
    }

    /**
     * Calculate similarity between two entity strings
     */
    public static function calculateSimilarity(string $s1, string $s2): int {
        $n1 = self::normalizeString($s1);
        $n2 = self::normalizeString($s2);

        if ($n1 === $n2) return 100;

        // Soundex match
        $soundexMatch = (soundex($n1) === soundex($n2));

        // Levenshtein distance
        $maxLen = max(strlen($n1), strlen($n2));
        if ($maxLen === 0) return 100;
        $lev = levenshtein($n1, $n2);
        $levScore = (int) round((1 - ($lev / $maxLen)) * 100);

        // Substring / token overlap
        $tokens1 = explode(' ', $n1);
        $tokens2 = explode(' ', $n2);
        $intersect = array_intersect($tokens1, $tokens2);
        $tokenScore = (count($intersect) / max(count($tokens1), count($tokens2))) * 100;

        $finalScore = (int) max($levScore, $soundexMatch ? 75 : 0, $tokenScore);
        return min(100, $finalScore);
    }

    private static function normalizeString(string $str): string {
        $str = mb_strtolower(trim($str), 'UTF-8');
        // Strip common diacritics
        $search  = ['ā', 'ī', 'ū', 'ṛ', 'ṝ', 'ḷ', 'ṅ', 'ñ', 'ṭ', 'ḍ', 'ṇ', 'ś', 'ṣ', 'ḥ', 'ṃ', 'ṟ', 'z̤', 'ʻ', 'ʼ', '-'];
        $replace = ['a', 'i', 'u', 'r', 'r', 'l', 'n', 'n', 't', 'd', 'n', 's', 's', 'h', 'm', 'r', 'z',  '',  '', ' '];
        $str = str_replace($search, $replace, $str);
        return preg_replace('/\s+/', ' ', $str);
    }

    private static function getMatchReason(string $s1, string $s2, int $score): string {
        $n1 = self::normalizeString($s1);
        $n2 = self::normalizeString($s2);
        if (soundex($n1) === soundex($n2)) {
            return "Phonetic Soundex equivalence with " . $score . "% lexical alignment";
        }
        return "Levenshtein edit similarity (" . $score . "%) & token overlap";
    }

    /**
     * Merge entity B into entity A
     */
    public static function mergeEntities(int $keepId, int $mergeId, int $userId): bool {
        $pdo = DB::getConnection();
        $pdo->beginTransaction();

        try {
            // Check existence
            $stmt = $pdo->prepare("SELECT * FROM entities WHERE id IN (?, ?)");
            $stmt->execute([$keepId, $mergeId]);
            $ents = $stmt->fetchAll();
            if (count($ents) < 2) {
                throw new Exception("One or both entities not found.");
            }

            // Re-point all entity mentions from mergeId to keepId
            $repoint = $pdo->prepare("UPDATE entity_mentions SET entity_id = ? WHERE entity_id = ?");
            $repoint->execute([$keepId, $mergeId]);

            // Mark entity as merged_into
            $mark = $pdo->prepare("UPDATE entities SET merged_into = ? WHERE id = ?");
            $mark->execute([$keepId, $mergeId]);

            // Record in audit log
            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, target_type, target_id, details, created_at) 
                                    VALUES (?, 'entity_merge', 'entities', ?, ?, NOW())");
            $audit->execute([$userId, $keepId, "Merged entity #$mergeId into #$keepId"]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}

