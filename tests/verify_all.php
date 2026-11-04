<?php
// tests/verify_all.php - Full Comprehensive Error & Bug Verifier
error_reporting(E_ALL);

$routes = [
    '/',
    '/index.php',
    '/monuments.php',
    '/monument.php?id=1',
    '/monument.php?id=2',
    '/monument.php?id=3',
    '/monument.php?id=5',
    '/persons.php',
    '/person.php?id=1',
    '/person.php?id=2',
    '/person.php?id=6',
    '/person.php?id=10',
    '/compare.php',
    '/compare.php?mode=person&p1=6&p2=1',
    '/compare.php?mode=monument&p1=1&p2=2',
    '/assistant.php',
    '/search.php',
    '/search.php?q=akbar',
    '/search.php?q=ashoka',
    '/search.php?q=nonexistent123xyz',
    '/manuscript.php?id=1',
    '/manuscript.php?id=2',
    '/map.php',
    '/timeline.php',
    '/graph.php',
    '/quiz.php',
    '/login.php',
    '/register.php',
    '/assets/css/style.css'
];

echo "=== STARTING FULL SITE ERROR & BUG AUDIT ===" . PHP_EOL;

$allPassed = true;
$results = [];

foreach ($routes as $route) {
    $url = 'http://127.0.0.1:8000' . $route;
    $ctx = stream_context_create(['http' => ['ignore_errors' => true]]);
    $html = @file_get_contents($url, false, $ctx);
    $status = $http_response_header[0] ?? 'UNKNOWN';

    $hasError = false;
    $errorMsg = '';

    if (!str_contains($status, '200') && !str_contains($status, '302')) {
        $hasError = true;
        $errorMsg = 'Bad HTTP status: ' . $status;
    } elseif (preg_match('/(Fatal error|Warning:|Notice:|Deprecated:|Parse error)/i', $html, $matches)) {
        $hasError = true;
        $errorMsg = 'PHP issue detected: ' . $matches[1];
    } elseif (strlen($html) === 0 && !str_contains($status, '302')) {
        $hasError = true;
        $errorMsg = 'Empty response body (0 bytes)';
    }

    if ($hasError) {
        echo "❌ FAIL: {$route} -> {$errorMsg}" . PHP_EOL;
        $allPassed = false;
    } else {
        echo "✅ PASS: {$route} (" . strlen($html) . " bytes, {$status})" . PHP_EOL;
    }
}

// Also check Admin login flow with cookie session
echo PHP_EOL . "--- VERIFYING ADMIN AUTH FLOW ---" . PHP_EOL;
$loginUrl = 'http://127.0.0.1:8000/login.php';
$adminUrl = 'http://127.0.0.1:8000/admin.php';
$postData = http_build_query([
    'email' => 'admin@archive.org',
    'password' => 'admin123'
]);

$cookieFile = __DIR__ . '/test_cookie.txt';
if (file_exists($cookieFile)) @unlink($cookieFile);

// Step 1: Login POST
$ch1 = curl_init();
curl_setopt($ch1, CURLOPT_URL, $loginUrl);
curl_setopt($ch1, CURLOPT_POST, true);
curl_setopt($ch1, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch1, CURLOPT_HEADER, true);
$res1 = curl_exec($ch1);
curl_close($ch1);

$sessId = '';
if (preg_match('/Set-Cookie:\s*PHPSESSID=([^;]+)/i', $res1, $matches)) {
    $sessId = $matches[1];
}

// Step 2: Access Admin Dashboard via GET with the session cookie
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $adminUrl);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
if ($sessId) {
    curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Cookie: PHPSESSID={$sessId}"]);
}
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
$adminHtml = curl_exec($ch2);
$adminHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$adminUrlFinal = curl_getinfo($ch2, CURLINFO_EFFECTIVE_URL);
curl_close($ch2);

echo "Admin Final URL: " . $adminUrlFinal . PHP_EOL;
echo "Admin Content Sample: " . substr(strip_tags($adminHtml), 0, 150) . PHP_EOL;

if ($adminHttpCode === 200 && str_contains($adminHtml, 'Control Center')) {
    echo "✅ PASS: Admin Login & Dashboard access verified! (200 OK, found 'Control Center')" . PHP_EOL;
} else {
    echo "❌ FAIL: Admin Dashboard flow failed (HTTP code: {$adminHttpCode})" . PHP_EOL;
    $allPassed = false;
}

if (file_exists($cookieFile)) {
    @unlink($cookieFile);
}

echo PHP_EOL . "==========================================" . PHP_EOL;
if ($allPassed) {
    echo "🎉 ALL " . count($routes) . " PUBLIC ROUTES & ADMIN FLOW VERIFIED CLEAN WITH ZERO ERRORS!" . PHP_EOL;
} else {
    echo "⚠️ ISSUES FOUND DURING AUDIT!" . PHP_EOL;
}
