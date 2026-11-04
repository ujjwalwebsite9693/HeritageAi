<?php
// tests/test_crud.php - Test CRUD operations for Monuments and Persons
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/models/Monument.php';
require_once __DIR__ . '/../app/models/Person.php';
require_once __DIR__ . '/../app/helpers/View.php';

echo "=== TESTING MONUMENT & PERSON CRUD ===\n";

// 1. Test Monument Creation
$monData = [
    'name' => 'Agra Fort (आगरा का किला)',
    'alternate_name' => 'Lal Qila of Agra',
    'built_by' => 'Emperor Akbar the Great',
    'person_id' => 6,
    'built_year' => 1565,
    'built_year_text' => '1565 – 1573 CE',
    'location_text' => 'Agra, Uttar Pradesh',
    'era_label' => 'Mughal Architectural Era',
    'architectural_style' => 'Mughal Red Sandstone Fortification',
    'image_url' => '/assets/img/monuments/fatehpur_sikri.jpg',
    'summary' => 'Historical Mughal fort in the city of Agra built with red sandstone.',
    'history' => 'Commissioned by Emperor Akbar in 1565 CE on the banks of the Yamuna River.',
    'visiting_info' => 'Open sunrise to sunset daily.',
    'highlights' => 'Diwan-i-Aam, Diwan-i-Khas, Jahangiri Mahal, Sheesh Mahal'
];

$monId = Monument::create($monData);
assert($monId > 0, "Monument ID must be > 0");
echo "✅ PASS: Monument created with ID {$monId}\n";

$foundMon = Monument::find($monId);
assert($foundMon['name'] === 'Agra Fort (आगरा का किला)', "Monument name matches");
assert(count($foundMon['highlights_array']) === 4, "Highlights parsed correctly");
echo "✅ PASS: Monument fetched and parsed correctly\n";

$updateSuccess = Monument::update($monId, array_merge($monData, ['built_by' => 'Jalal al-Din Muhammad Akbar']));
assert($updateSuccess === true, "Monument updated");
echo "✅ PASS: Monument updated\n";

$deleteSuccess = Monument::delete($monId);
assert($deleteSuccess === true, "Monument deleted");
echo "✅ PASS: Monument deleted\n";

// 2. Test Person Creation
$personData = [
    'name' => 'Sher Shah Suri (शेरशाह सूरी)',
    'alt_names' => 'Farid Khan, Lion King',
    'birth_year' => 1486,
    'death_year' => 1545,
    'role' => 'Founder of the Suri Empire (सूरी साम्राज्य के संस्थापक)',
    'bio' => 'Visionary ruler who built the Grand Trunk Road and introduced the Rupiya currency.',
    'portrait_url' => '/assets/img/persons/babur.png'
];

$personId = Person::create($personData);
assert($personId > 0, "Person ID must be > 0");
echo "✅ PASS: Person created with ID {$personId}\n";

$foundPerson = Person::find($personId);
assert($foundPerson['name'] === 'Sher Shah Suri (शेरशाह सूरी)', "Person name matches");
echo "✅ PASS: Person fetched correctly\n";

$updatePersonSuccess = Person::update($personId, array_merge($personData, ['role' => 'Emperor of Northern India']));
assert($updatePersonSuccess === true, "Person updated");
echo "✅ PASS: Person updated\n";

$deletePersonSuccess = Person::delete($personId);
assert($deletePersonSuccess === true, "Person deleted");
echo "✅ PASS: Person deleted\n";

// 3. Test Chanakya Relationships
$chanakya = Person::find(2);
assert($chanakya !== null, "Chanakya found");
assert(isset($chanakya['relations']), "Chanakya has relations key");
assert(count($chanakya['relations']) > 0, "Chanakya has relations");
echo "✅ PASS: Chanakya relations loaded cleanly with count: " . count($chanakya['relations']) . "\n";
foreach ($chanakya['relations'] as $rel) {
    assert(!empty($rel['related_person_name']), "Related person name is not empty");
    assert(!empty($rel['relationship_type']), "Relationship type is not empty");
    echo "   -> Relationship: {$rel['relationship_type']} with {$rel['related_person_name']}\n";
}

echo "\n🎉 ALL CRUD AND RELATIONSHIP TESTS PASSED!\n";
