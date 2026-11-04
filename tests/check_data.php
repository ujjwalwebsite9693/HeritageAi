<?php
require_once __DIR__ . '/../config/db.php';
$db = DB::getConnection();

echo "=== PERSONS ===\n";
$stmt = $db->query("SELECT id, name, role, birth_year, death_year, portrait_url FROM persons ORDER BY id ASC");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "ID {$row['id']}: {$row['name']} ({$row['role']}) - Portrait: {$row['portrait_url']}\n";
}

echo "\n=== MONUMENTS ===\n";
$stmt = $db->query("SELECT id, name, built_by, built_year_text, location_text, image_url FROM monuments ORDER BY id ASC");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "ID {$row['id']}: {$row['name']} (By: {$row['built_by']}, {$row['built_year_text']}, {$row['location_text']}) - Img: {$row['image_url']}\n";
}

echo "\n=== MANUSCRIPTS ===\n";
$stmt = $db->query("SELECT id, title, author, era_start, era_end, thumbnail FROM manuscripts ORDER BY id ASC");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "ID {$row['id']}: {$row['title']} (By: {$row['author']}) - Thumb: {$row['thumbnail']}\n";
}
