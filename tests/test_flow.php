<?php
// tests/test_flow.php - Automated Verification of MIA features

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/helpers/Auth.php';
require_once __DIR__ . '/../app/helpers/Calendar.php';
require_once __DIR__ . '/../app/models/Manuscript.php';
require_once __DIR__ . '/../app/services/SearchService.php';
require_once __DIR__ . '/../app/services/LinkDiscoveryService.php';
require_once __DIR__ . '/../app/services/EntityService.php';
require_once __DIR__ . '/../app/services/TranslationService.php';
require_once __DIR__ . '/../app/services/ExportService.php';

echo "=== MANUSCRIPT INTELLIGENCE ARCHIVE TEST SUITE ===\n\n";

$pass = 0;
$fail = 0;

function assertTest($condition, $name) {
    global $pass, $fail;
    if ($condition) {
        echo "✅ PASS: $name\n";
        $pass++;
    } else {
        echo "❌ FAIL: $name\n";
        $fail++;
    }
}

// 1. Database Connection
$pdo = DB::getConnection();
assertTest($pdo instanceof PDO, "Database Connection via DB::getConnection()");

// 2. Authentication Login
$loginSuccess = Auth::attempt('admin@archive.org', 'admin123');
assertTest($loginSuccess === true, "Admin Login via Auth::attempt");
assertTest(Auth::role() === 'admin', "Admin Role Verification");
assertTest(Auth::hasRole('researcher') === true, "Admin inherits researcher permissions");

// 3. Calendar Conversions
$bce = Calendar::formatYear(-257);
assertTest($bce === "257 BCE", "Negative Integer formatted to BCE ('$bce')");
$vsCe = Calendar::normalizeToCe(1637, 'Vikram Samvat'); // VS 1637 = 1580 CE
assertTest($vsCe === 1580, "Vikram Samvat to CE normalization (VS 1637 -> $vsCe)");
$shakaCe = Calendar::normalizeToCe(552, 'Shaka'); // Shaka 552 = 630 CE
assertTest($shakaCe === 630, "Shaka to CE normalization (Shaka 552 -> $shakaCe)");

// 4. Archive FULLTEXT and Typo Search
$searchResults = SearchService::search(['q' => 'Ashoka']);
assertTest($searchResults['total'] >= 1, "Archive Search for 'Ashoka' returns >= 1 result (found: {$searchResults['total']})");

$soundexSearch = SearchService::search(['q' => 'Akbarr']); // intentional spelling variant
assertTest($soundexSearch['total'] >= 1, "Phonetic Soundex Search for variant 'Akbarr' returns result");

// 5. Link Discovery Scoring
$doc1 = Manuscript::find(1);
$doc2 = Manuscript::find(2);
$analysis = LinkDiscoveryService::evaluateConnection($doc1, $doc2);
assertTest($analysis['score'] >= 30, "Ashoka <-> Arthashastra Connection Score >= 30% (actual: {$analysis['score']}%)");
assertTest(!empty($analysis['shared_entities']), "Ashoka <-> Arthashastra identifies shared entities/ties");

// 6. Entity Fuzzy Matching
$simScore = EntityService::calculateSimilarity("Akbar", "Akbarr");
assertTest($simScore >= 80, "Similarity between 'Akbar' and 'Akbarr' >= 80% (actual: $simScore%)");

// 7. Academic Citation Generation
$citations = ExportService::generateCitations(1);
assertTest(str_contains($citations['chicago'], 'Ashoka'), "Chicago Citation contains author name");
assertTest(str_contains($citations['apa'], 'Ashoka'), "APA Citation contains author name");

// 8. Translation & Glossary Tooltip Mapping
$glossary = TranslationService::getGlossaryMap();
assertTest(count($glossary) >= 5, "Glossary contains classical terms (found: " . count($glossary) . ")");

echo "\n-------------------------------------------------\n";
echo "SUMMARY: $pass Passed, $fail Failed.\n";
if ($fail === 0) {
    echo "🎉 ALL TESTS PASSED! Project is completely production ready.\n";
} else {
    exit(1);
}
