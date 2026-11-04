<?php
// config/app.php - Application Configuration

// Simple .env parser if not already loaded into $_ENV
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, '"\'');
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

return [
    'name' => $_ENV['APP_NAME'] ?? 'Manuscript Intelligence Archive',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/'),
    'secret' => $_ENV['APP_SECRET'] ?? 'default_mia_secret_salt_123',

    'db' => [
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => (int)($_ENV['DB_PORT'] ?? 3306),
        'name' => $_ENV['DB_NAME'] ?? 'manuscript_archive',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? 'root',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    ],

    'storage' => [
        'path' => dirname(__DIR__) . '/storage',
        'originals' => dirname(__DIR__) . '/storage/originals',
        'derivatives' => dirname(__DIR__) . '/storage/derivatives',
        'max_upload_size' => (int)($_ENV['UPLOAD_MAX_BYTES'] ?? 26214400),
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/tiff', 'application/pdf'],
    ],

    'ai' => [
        'gemini_api_key' => $_ENV['GEMINI_API_KEY'] ?? '',
        'tesseract_path' => $_ENV['TESSERACT_PATH'] ?? '',
    ],

    'roles' => [
        'visitor' => ['read'],
        'user' => ['read', 'annotate', 'api_key'],
        'researcher' => ['read', 'annotate', 'api_key', 'transcribe', 'verify', 'link_review', 'entity_merge'],
        'admin' => ['read', 'annotate', 'api_key', 'transcribe', 'verify', 'link_review', 'entity_merge', 'manage_users', 'upload', 'system_config'],
    ]
];

