<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;charset=utf8mb4", "root", "root", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true
    ]);
    
    echo "Creating database & tables...\n";
    $schemaSql = file_get_contents(__DIR__ . '/../database/schema.sql');
    $pdo->exec($schemaSql);
    echo "Schema executed successfully!\n";
    
    echo "Seeding data...\n";
    $seedSql = file_get_contents(__DIR__ . '/../database/seed.sql');
    $pdo->exec($seedSql);
    echo "Seed executed successfully!\n";
    
    // Quick verification
    $tables = $pdo->query("SHOW TABLES FROM `manuscript_archive`")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in manuscript_archive: " . implode(", ", $tables) . "\n";
    
    $manuscriptsCount = $pdo->query("SELECT COUNT(*) FROM `manuscript_archive`.`manuscripts`")->fetchColumn();
    echo "Manuscripts count: $manuscriptsCount\n";
    $placesCount = $pdo->query("SELECT COUNT(*) FROM `manuscript_archive`.`places`")->fetchColumn();
    echo "Places count: $placesCount\n";
    $eventsCount = $pdo->query("SELECT COUNT(*) FROM `manuscript_archive`.`events`")->fetchColumn();
    echo "Events count: $eventsCount\n";
    $linksCount = $pdo->query("SELECT COUNT(*) FROM `manuscript_archive`.`links`")->fetchColumn();
    echo "Links count: $linksCount\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

