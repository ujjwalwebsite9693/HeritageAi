<?php
// database/add_monuments.php - Add Monuments and Expanded Historical Figures

require_once __DIR__ . '/../config/db.php';

$pdo = DB::getConnection();

echo "Creating monuments table...\n";
$pdo->exec("
CREATE TABLE IF NOT EXISTS `monuments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(180) NOT NULL,
  `alternate_name` VARCHAR(180) NULL,
  `built_by` VARCHAR(180) NOT NULL,
  `person_id` INT UNSIGNED NULL,
  `built_year` INT NULL,
  `built_year_text` VARCHAR(100) NOT NULL,
  `place_id` INT UNSIGNED NULL,
  `location_text` VARCHAR(150) NOT NULL,
  `era_label` VARCHAR(100) NOT NULL,
  `architectural_style` VARCHAR(120) NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `summary` TEXT NOT NULL,
  `history` TEXT NOT NULL,
  `visiting_info` VARCHAR(255) NULL,
  `highlights` JSON NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "Seeding monuments...\n";
$pdo->exec("TRUNCATE TABLE `monuments`;");

$stmt = $pdo->prepare("
INSERT INTO `monuments` 
(`id`, `name`, `alternate_name`, `built_by`, `person_id`, `built_year`, `built_year_text`, `place_id`, `location_text`, `era_label`, `architectural_style`, `image_url`, `summary`, `history`, `visiting_info`, `highlights`, `created_at`) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$monuments = [
    [
        1,
        'Taj Mahal',
        'Rauza-i-Munawwara',
        'Emperor Shah Jahan',
        10,
        1648,
        '1632 – 1653 CE',
        6,
        'Agra, Uttar Pradesh',
        'Mughal Golden Age',
        'Mughal Classical White Marble',
        '/assets/img/monuments/taj_mahal.jpg',
        'The jewel of Indo-Islamic architecture, built on the Yamuna riverbank as an eternal monument of love and imperial perfection.',
        'Commissioned in 1631 by Emperor Shah Jahan in memory of his beloved empress Mumtaz Mahal. Over 20,000 artisans, calligraphers from Shiraz, stonecutters from Multan, and mosaicists crafted the translucent white Makrana marble inlaid with lapis lazuli, jade, and carnelian.',
        'Open sunrise to sunset (closed Fridays). Night viewing available on full moon nights.',
        json_encode(['UNESCO World Heritage Site', 'Pietra Dura Inlay Art', 'Symmetrical Charbagh Garden', 'Central Octagonal Dome']),
    ],
    [
        2,
        'Red Fort (Lal Qila)',
        'Qila-i-Mubarak',
        'Emperor Shah Jahan',
        10,
        1648,
        '1638 – 1648 CE',
        4,
        'Old Delhi (Shahjahanabad)',
        'Mughal Empire',
        'Indo-Islamic Red Sandstone',
        '/assets/img/monuments/red_fort.jpg',
        'The magnificent crimson citadel that served as the imperial residence and political nerve center of the Mughal Empire for two centuries.',
        'Designed by master architect Ustad Ahmad Lahori when Shah Jahan relocated the imperial capital from Agra to Delhi. It housed the famed Peacock Throne in the Diwan-i-Khas, beneath the inscription: "If there be a paradise on earth, it is here, it is here, it is here."',
        'Open Tuesday to Sunday, 9:30 AM – 4:30 PM. Light and sound show in evenings.',
        json_encode(['Seat of the Peacock Throne', 'Lahori Gate & Delhi Gate', 'Diwan-i-Aam & Diwan-i-Khas', 'Rang Mahal Pavilions']),
    ],
    [
        3,
        'Humayun’s Tomb',
        'Maqbara-e-Humayun',
        'Empress Bega Begum & Emperor Akbar',
        11,
        1572,
        '1565 – 1572 CE',
        4,
        'Nizamuddin East, New Delhi',
        'Early Mughal Architecture',
        'Persian-Timurid & Indo-Islamic',
        '/assets/img/monuments/humayuns_tomb.jpg',
        'The monumental garden tomb of the second Mughal Emperor Humayun, celebrated as the architectural prototype for the Taj Mahal.',
        'Commissioned by Humayun’s chief consort Empress Bega Begum and built under the oversight of Persian architect Mirak Mirza Ghiyas. It introduced the monumental double dome and symmetrical Charbagh paradise garden layout to the Indian subcontinent.',
        'Open daily from sunrise to sunset. Located near Hazrat Nizamuddin Dargah.',
        json_encode(['First Garden-Tomb in India', 'Inspiration for Taj Mahal', 'Over 150 Royal Graves', 'UNESCO World Heritage Site']),
    ],
    [
        4,
        'Fatehpur Sikri & Buland Darwaza',
        'The City of Victory',
        'Emperor Akbar the Great',
        6,
        1575,
        '1571 – 1575 CE',
        6,
        'Fatehpur Sikri, near Agra',
        'Akbari Architecture',
        'Red Sandstone Blend of Hindu & Islamic Motifs',
        '/assets/img/monuments/fatehpur_sikri.jpg',
        'Akbar’s utopian royal capital and the towering Buland Darwaza (Gate of Magnificence), erected to celebrate the Mughal conquest of Gujarat.',
        'Founded by Akbar near the shrine of Sufi saint Sheikh Salim Chishti. It served as the capital where Akbar convened scholars of all faiths at the Ibadat Khana, blending Rajput, Persian, and Central Asian decorative traditions.',
        'Open daily from 6:00 AM – 6:00 PM. Located 37 km from Agra city.',
        json_encode(['54-Meter Buland Darwaza', 'White Marble Tomb of Salim Chishti', 'Panch Mahal 5-Tier Pavilion', 'Ibadat Khana Debate Hall']),
    ],
    [
        5,
        'Nalanda Mahavihara',
        'Ancient Nalanda University',
        'Kumaragupta I & King Harsha',
        4,
        427,
        'c. 427 – 1197 CE',
        8,
        'Nalanda / Rajgir, Bihar',
        'Classical Gupta & Harsha Era',
        'Ancient Buddhist Brick Architecture',
        '/assets/img/monuments/nalanda.jpg',
        'The ancient world’s premier residential university, where 10,000 monks and scholars from China, Korea, Tibet, and Central Asia studied philosophy, medicine, and astronomy.',
        'Patronized across 700 years by the Gupta emperors, King Harsha of Kannauj, and the Pala dynasty. It housed the legendary nine-story library "Dharmaganja" where the pilgrim Xuanzang spent five years studying and copying manuscripts.',
        'Open daily 9:00 AM – 5:00 PM. Museum on site holds excavated bronzes and seals.',
        json_encode(['10,000 Students & 2,000 Teachers', 'Great Stupa of Sariputra', 'Dharmaganja Multi-Tier Library', 'UNESCO World Heritage Site']),
    ],
    [
        6,
        'Ashoka Rock Edict of Mount Girnar',
        'Girinagara Epigraphic Boulder',
        'Emperor Ashoka Maurya',
        1,
        -257,
        'c. 257 BCE',
        5,
        'Junagadh, Gujarat',
        'Maurya Imperial Dynasty',
        'Ancient Volcanic Granite Inscription',
        '/assets/img/monuments/girnar_edict.jpg',
        'The colossal granite boulder bearing 14 Major Rock Edicts of Ashoka, recording his remorse over the Kalinga War and embrace of moral Dhamma.',
        'Inscribed in Western Prakrit Brahmi script around 257 BCE. Later, Mahakshatrapa Rudradaman (150 CE) and Gupta Emperor Skandagupta (455 CE) added inscriptions commemorating the repair of the ancient Sudarshana Lake dam.',
        'Open daily 8:00 AM – 6:00 PM at the foot of Mount Girnar.',
        json_encode(['14 Major Edicts of Ashoka', 'Ancient Sudarshana Dam Records', 'Three Historic Dynasties on One Stone', 'Earliest Sanskrit Royal Epigraph']),
    ],
    [
        7,
        'Qutub Minar & Iron Pillar',
        'Minar-i-Qutb',
        'Qutb-ud-din Aibak & Iltutmish',
        NULL,
        1199,
        '1199 – 1220 CE',
        4,
        'Mehrauli, New Delhi',
        'Delhi Sultanate',
        'Afghan-Indo-Islamic Fluted Tower',
        '/assets/img/monuments/qutub_minar.jpg',
        'The 72.5-meter towering minaret of red sandstone and marble, alongside the rust-resistant 1,600-year-old Gupta Iron Pillar.',
        'Initiated in 1199 CE as a victory minaret. The complex contains the enigmatic 4th-century Gupta Iron Pillar with a Sanskrit inscription commemorating King Chandra (Chandragupta II), showcasing ancient Indian metallurgy.',
        'Open sunrise to 10:00 PM. Beautifully illuminated after sunset.',
        json_encode(['72.5m Tallest Brick Minaret', 'Rustless Gupta Iron Pillar', 'Intricate Quranic Calligraphy', 'Alai Darwaza Horseshoe Gateway']),
    ]
];

foreach ($monuments as $m) {
    $stmt->execute($m);
}
echo "Seeded " . count($monuments) . " famous historical monuments.\n";

echo "Updating historical persons (Humayun, Babur, Shah Jahan, Birbal)...\n";
$figures = [
    [
        10,
        'Shah Jahan',
        'Khurram, Shah Jahan the Magnificent, शाहजहाँ',
        1592,
        1666,
        'Fifth Mughal Emperor',
        'Renowned for presiding over the golden age of Mughal architecture. Built the Taj Mahal in Agra, the Red Fort and Jama Masjid in Delhi, and founded Shahjahanabad.',
        '/assets/img/persons/shahjahan.png'
    ],
    [
        11,
        'Humayun',
        'Nasir-ud-din Muhammad, हुमायूँ',
        1508,
        1556,
        'Second Mughal Emperor',
        'Father of Emperor Akbar. Re-established the Mughal Empire after years in Persian exile. His love for astronomy and library folios led to the creation of the royal miniature painting atelier.',
        '/assets/img/persons/humayun.png'
    ],
    [
        12,
        'Babur',
        'Zahir-ud-din Muhammad Babur, बाबर',
        1483,
        1530,
        'Founder of the Mughal Empire',
        'Descendant of Timur and Genghis Khan who defeated Ibrahim Lodi at the Battle of Panipat (1526). Renowned poet, naturalist, and author of the autobiographical Baburnama.',
        '/assets/img/persons/babur.png'
    ],
    [
        13,
        'Raja Birbal',
        'Mahesh Das, राजा बीरबल',
        1528,
        1586,
        'Grand Vizier, Poet & Courtier to Akbar',
        'One of the Navaratnas (Nine Gems) in Akbar’s royal court. Celebrated throughout Indian history for his witty wisdom, diplomatic missions, and close friendship with Emperor Akbar.',
        '/assets/img/persons/birbal.png'
    ],
];

foreach ($figures as $f) {
    $check = $pdo->prepare("SELECT id FROM persons WHERE id = ?");
    $check->execute([$f[0]]);
    if ($check->fetch()) {
        $upd = $pdo->prepare("UPDATE persons SET name = ?, alt_names = ?, birth_year = ?, death_year = ?, role = ?, bio = ?, portrait_url = ? WHERE id = ?");
        $upd->execute([$f[1], $f[2], $f[3], $f[4], $f[5], $f[6], $f[7], $f[0]]);
    } else {
        $ins = $pdo->prepare("INSERT INTO persons (id, name, alt_names, birth_year, death_year, role, bio, portrait_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $ins->execute([$f[0], $f[1], $f[2], $f[3], $f[4], $f[5], $f[6], $f[7]]);
    }
}

echo "Database successfully enriched with monuments and prominent historical figures!\n";
