<?php
// database/generate_monument_assets.php - Generate crisp, attractive images for monuments and historical figures

$imgDir = __DIR__ . '/../public/assets/img';
$monumentsDir = $imgDir . '/monuments';
$personsDir = $imgDir . '/persons';

foreach ([$monumentsDir, $personsDir] as $d) {
    if (!is_dir($d)) {
        mkdir($d, 0777, true);
    }
}

function drawMonumentGraphic($filepath, $title, $location, $era, $bgColor, $primaryColor, $accentColor, $iconSymbol) {
    $width = 800;
    $height = 500;
    $im = imagecreatetruecolor($width, $height);

    // Sky / Background gradient
    for ($y = 0; $y < $height; $y++) {
        $ratio = $y / $height;
        $r = (int)($bgColor[0] * (1 - $ratio * 0.4) + 240 * ($ratio * 0.4));
        $g = (int)($bgColor[1] * (1 - $ratio * 0.4) + 245 * ($ratio * 0.4));
        $b = (int)($bgColor[2] * (1 - $ratio * 0.4) + 250 * ($ratio * 0.4));
        $lineColor = imagecolorallocate($im, max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
        imageline($im, 0, $y, $width, $y, $lineColor);
    }

    // Architectural Ground Horizon
    $groundColor = imagecolorallocate($im, 70, 75, 85);
    $groundAccent = imagecolorallocate($im, 100, 110, 125);
    imagefilledrectangle($im, 0, $height - 110, $width, $height, $groundColor);
    imagefilledrectangle($im, 0, $height - 120, $width, $height - 110, $groundAccent);

    // Monument Silhouette / Architecture Structure
    $pCol = imagecolorallocate($im, $primaryColor[0], $primaryColor[1], $primaryColor[2]);
    $pDark = imagecolorallocate($im, max(0, $primaryColor[0] - 40), max(0, $primaryColor[1] - 40), max(0, $primaryColor[2] - 40));
    $gold = imagecolorallocate($im, $accentColor[0], $accentColor[1], $accentColor[2]);
    $white = imagecolorallocate($im, 255, 255, 255);

    // Central Dome / Monument Body
    imagefilledrectangle($im, 260, 160, 540, $height - 120, $pCol);
    imagefilledarc($im, 400, 160, 220, 190, 180, 360, $pCol, IMG_ARC_PIE);
    // Dome Finial
    imagefilledrectangle($im, 396, 50, 404, 80, $gold);
    imagefilledellipse($im, 400, 50, 18, 18, $gold);

    // Flanking Minarets / Towers
    imagefilledrectangle($im, 140, 110, 175, $height - 120, $pDark);
    imagefilledarc($im, 157, 110, 40, 50, 180, 360, $gold, IMG_ARC_PIE);

    imagefilledrectangle($im, 625, 110, 660, $height - 120, $pDark);
    imagefilledarc($im, 642, 110, 40, 50, 180, 360, $gold, IMG_ARC_PIE);

    // Grand Portal / Arch (Iwan)
    $archDark = imagecolorallocate($im, 25, 28, 35);
    imagefilledrectangle($im, 340, 240, 460, $height - 120, $archDark);
    imagefilledarc($im, 400, 240, 120, 130, 180, 360, $archDark, IMG_ARC_PIE);

    // Decorative Borders
    imagerectangle($im, 20, 20, $width - 20, $height - 20, $gold);
    imagerectangle($im, 24, 24, $width - 24, $height - 24, $white);

    // Text Overlay Banner on Bottom Left
    $bannerBg = imagecolorallocatealpha($im, 15, 23, 42, 35);
    imagefilledrectangle($im, 35, $height - 100, $width - 35, $height - 35, $bannerBg);

    $textGold = imagecolorallocate($im, 254, 240, 138);
    $textWhite = imagecolorallocate($im, 255, 255, 255);
    $textMuted = imagecolorallocate($im, 203, 213, 225);

    imagestring($im, 5, 55, $height - 90, strtoupper($title), $textWhite);
    imagestring($im, 4, 55, $height - 65, "Location: " . $location . " | Era: " . $era, $textGold);
    imagestring($im, 3, $width - 220, $height - 75, "ARCHIVE MONUMENT", $textMuted);

    imagejpeg($im, $filepath, 94);
    imagedestroy($im);
}

function drawPortraitGraphic($filepath, $name, $title, $dynasty, $hue) {
    $width = 280;
    $height = 280;
    $im = imagecreatetruecolor($width, $height);

    // Background gradient circle
    $bg = imagecolorallocate($im, 248, 250, 252);
    imagefilledrectangle($im, 0, 0, $width, $height, $bg);

    $borderCol = imagecolorallocate($im, $hue[0], $hue[1], $hue[2]);
    $accentCol = imagecolorallocate($im, 217, 119, 6); // amber/gold
    $dark = imagecolorallocate($im, 15, 23, 42);
    $white = imagecolorallocate($im, 255, 255, 255);

    // Double Halo / Royal Medallion
    imagefilledellipse($im, 140, 140, 250, 250, $borderCol);
    imagefilledellipse($im, 140, 140, 236, 236, $white);
    imageellipse($im, 140, 140, 224, 224, $accentCol);

    // Turban / Crown Motif
    $crownCol = imagecolorallocate($im, $hue[0], $hue[1], $hue[2]);
    imagefilledarc($im, 140, 95, 90, 80, 180, 360, $crownCol, IMG_ARC_PIE);
    // Plume / Kalgi
    imagefilledrectangle($im, 137, 35, 143, 65, $accentCol);
    imagefilledellipse($im, 140, 35, 14, 14, $accentCol);

    // Face Silhouette
    $faceCol = imagecolorallocate($im, 235, 205, 175);
    imagefilledellipse($im, 140, 125, 65, 75, $faceCol);

    // Imperial Robe
    imagefilledarc($im, 140, 215, 135, 120, 180, 360, $crownCol, IMG_ARC_PIE);

    // Name Banner
    $bannerCol = imagecolorallocate($im, 15, 23, 42);
    imagefilledrectangle($im, 25, 210, $width - 25, 255, $bannerCol);
    imagerectangle($im, 25, 210, $width - 25, 255, $accentCol);

    $nameCol = imagecolorallocate($im, 255, 255, 255);
    $subCol = imagecolorallocate($im, 252, 211, 77);

    // Centered text
    $xName = max(30, (int)(140 - strlen($name) * 4.5));
    imagestring($im, 4, $xName, 218, $name, $nameCol);

    $xSub = max(30, (int)(140 - strlen($dynasty) * 3.5));
    imagestring($im, 2, $xSub, 236, $dynasty, $subCol);

    imagepng($im, $filepath);
    imagedestroy($im);
}

echo "Generating rich images for famous historical monuments...\n";

// 1. Taj Mahal (White marble, Yamuna blue sky, gold finials)
drawMonumentGraphic($monumentsDir . '/taj_mahal.jpg', "Taj Mahal", "Agra, Uttar Pradesh", "1632-1653 CE", [180, 215, 245], [240, 245, 250], [215, 175, 55], "Taj");

// 2. Red Fort (Deep red sandstone, Delhi sky)
drawMonumentGraphic($monumentsDir . '/red_fort.jpg', "Red Fort (Lal Qila)", "Old Delhi", "1638-1648 CE", [240, 210, 190], [175, 45, 40], [225, 185, 60], "Fort");

// 3. Humayun's Tomb (Persian red sandstone, white marble dome)
drawMonumentGraphic($monumentsDir . '/humayuns_tomb.jpg', "Humayun's Tomb", "Delhi", "1565-1572 CE", [215, 225, 240], [195, 75, 60], [235, 195, 75], "Tomb");

// 4. Fatehpur Sikri & Buland Darwaza
drawMonumentGraphic($monumentsDir . '/fatehpur_sikri.jpg', "Fatehpur Sikri & Buland Darwaza", "Agra", "1571-1575 CE", [245, 215, 175], [185, 65, 45], [230, 180, 40], "Gate");

// 5. Nalanda Mahavihara (Ancient monastic brick)
drawMonumentGraphic($monumentsDir . '/nalanda.jpg', "Nalanda Ancient Mahavihara", "Nalanda, Bihar", "c. 427-1197 CE", [220, 230, 240], [180, 95, 50], [210, 150, 45], "Uni");

// 6. Girnar Rock Edict (Ancient granite boulder)
drawMonumentGraphic($monumentsDir . '/girnar_edict.jpg', "Ashoka Rock Inscription of Girnar", "Junagadh, Gujarat", "c. 257 BCE", [210, 215, 210], [90, 100, 105], [200, 160, 50], "Rock");

// 7. Qutub Minar (Fluted red sandstone & marble tower)
drawMonumentGraphic($monumentsDir . '/qutub_minar.jpg', "Qutub Minar & Iron Pillar", "Delhi", "1199-1220 CE", [230, 220, 205], [190, 70, 50], [220, 170, 60], "Minar");

echo "Generating portraits for prominent historical emperors and figures...\n";

drawPortraitGraphic($personsDir . '/akbar.png', "Emperor Akbar", "Mughal Sovereign", "Mughal Empire (1542-1605)", [153, 27, 27]);
drawPortraitGraphic($personsDir . '/humayun.png', "Emperor Humayun", "Second Mughal Ruler", "Mughal Empire (1508-1556)", [180, 83, 9]);
drawPortraitGraphic($personsDir . '/babur.png', "Emperor Babur", "Dynasty Founder", "Mughal Empire (1483-1530)", [30, 58, 138]);
drawPortraitGraphic($personsDir . '/shahjahan.png', "Shah Jahan", "Master of Architecture", "Mughal Empire (1592-1666)", [190, 24, 93]);
drawPortraitGraphic($personsDir . '/birbal.png', "Raja Birbal", "Grand Courtier & Wit", "Akbar Navaratna (1528-1586)", [13, 148, 136]);
drawPortraitGraphic($personsDir . '/ashoka.png', "Ashoka the Great", "Emperor of Dhamma", "Maurya Empire (304-232 BCE)", [124, 45, 18]);
drawPortraitGraphic($personsDir . '/chandragupta.png', "Chandragupta Maurya", "Subcontinent Unifier", "Maurya Dynasty (340-297 BCE)", [67, 56, 202]);
drawPortraitGraphic($personsDir . '/chanakya.png', "Acharya Chanakya", "Author of Arthashastra", "Maurya Era (375-283 BCE)", [146, 64, 14]);
drawPortraitGraphic($personsDir . '/harsha.png', "Harsha Vardhana", "Emperor of Kannauj", "Pushyabhuti (590-647 CE)", [3, 105, 161]);
drawPortraitGraphic($personsDir . '/todarmal.png', "Raja Todar Mal", "Finance Minister", "Revenue Reforms (1515-1589)", [107, 33, 168]);

echo "All monument images and emperor portraits generated successfully!\n";
