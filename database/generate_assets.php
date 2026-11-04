<?php
// database/generate_assets.php - Generate realistic manuscript folio images and thumbnails

$imgDir = __DIR__ . '/../public/assets/img';
$pagesDir = $imgDir . '/pages';
$personsDir = $imgDir . '/persons';
$uploadsDir = $imgDir . '/uploads';

foreach ([$imgDir, $pagesDir, $personsDir, $uploadsDir] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

function createManuscriptFolio($path, $title, $scriptText, $eraText, $subhead, $bgHue = [245, 237, 218]) {
    $width = 900;
    $height = 1100;
    $im = imagecreatetruecolor($width, $height);

    // Background parchment texture simulation
    $bg = imagecolorallocate($im, $bgHue[0], $bgHue[1], $bgHue[2]);
    imagefilledrectangle($im, 0, 0, $width, $height, $bg);

    // Add noise and aging grain
    for ($i = 0; $i < 3500; $i++) {
        $noiseVal = rand(-25, 25);
        $nc = imagecolorallocate($im, 
            max(0, min(255, $bgHue[0] + $noiseVal)), 
            max(0, min(255, $bgHue[1] + $noiseVal)), 
            max(0, min(255, $bgHue[2] + $noiseVal - 10))
        );
        imagesetpixel($im, rand(0, $width - 1), rand(0, $height - 1), $nc);
    }

    // Antiqued ornamental border
    $borderCol = imagecolorallocate($im, 140, 110, 75);
    $innerBorderCol = imagecolorallocate($im, 185, 155, 120);
    $goldCol = imagecolorallocate($im, 205, 160, 50);

    // Double borders
    imagerectangle($im, 30, 30, $width - 30, $height - 30, $borderCol);
    imagerectangle($im, 36, 36, $width - 36, $height - 36, $innerBorderCol);
    imagerectangle($im, 44, 44, $width - 44, $height - 44, $goldCol);

    // Dark ink colors
    $inkDark = imagecolorallocate($im, 45, 35, 28);
    $inkRed = imagecolorallocate($im, 150, 40, 35);
    $inkGold = imagecolorallocate($im, 175, 120, 20);

    // Title and Header
    imagestring($im, 5, 60, 70, "ARCHIVAL DIGITAL SURROGATE - FOLIO FACSIMILE", $inkRed);
    imagestring($im, 5, 60, 105, strtoupper($title), $inkDark);
    imagestring($im, 4, 60, 135, "ERA / PROVENANCE: " . $eraText . " | " . $subhead, $inkGold);
    imageline($im, 60, 165, $width - 60, 165, $borderCol);

    // Draw manuscript lines
    $y = 220;
    $lines = explode("\n", wordwrap($scriptText, 65, "\n", true));
    foreach ($lines as $idx => $line) {
        if ($y > $height - 140) break;
        // Draw subtle faint rule line under text
        imageline($im, 65, $y + 22, $width - 65, $y + 22, imagecolorallocate($im, 220, 205, 185));
        imagestring($im, 5, 70, $y, $line, $inkDark);
        $y += 42;
    }

    // Archival seal stamp at bottom right
    imageellipse($im, $width - 130, $height - 100, 110, 110, $inkRed);
    imageellipse($im, $width - 130, $height - 100, 98, 98, $inkRed);
    imagestring($im, 3, $width - 170, $height - 110, "ASI / MIA SEAL", $inkRed);
    imagestring($im, 2, $width - 165, $height - 90, "VERIFIED SCAN", $inkRed);

    // Call number watermark at bottom left
    imagestring($im, 3, 60, $height - 70, "DIGITAL CODEX REPOSITORY ARCHIVE - PUBLIC DOMAIN", $borderCol);

    imagejpeg($im, $path, 92);
    imagedestroy($im);
}

function createPersonBadge($path, $name, $role, $era) {
    $width = 240;
    $height = 240;
    $im = imagecreatetruecolor($width, $height);
    $bg = imagecolorallocate($im, 240, 235, 225);
    imagefilledrectangle($im, 0, 0, $width, $height, $bg);

    $border = imagecolorallocate($im, 160, 130, 90);
    $navy = imagecolorallocate($im, 30, 55, 95);
    $dark = imagecolorallocate($im, 40, 35, 30);
    $crimson = imagecolorallocate($im, 140, 35, 45);

    imageellipse($im, 120, 120, 220, 220, $border);
    imageellipse($im, 120, 120, 200, 200, $crimson);

    // Initial letters icon
    $initials = strtoupper(substr($name, 0, 2));
    imagestring($im, 5, 110, 75, $initials, $navy);
    imagestring($im, 4, max(15, (int)(120 - strlen($name) * 4)), 115, $name, $dark);
    imagestring($im, 2, max(15, (int)(120 - strlen($role) * 3)), 145, substr($role, 0, 26), $crimson);
    imagestring($im, 2, max(15, (int)(120 - strlen($era) * 3)), 170, $era, $border);

    imagepng($im, $path);
    imagedestroy($im);
}

echo "Generating manuscript folios and cover thumbnails...\n";

// 1. Ashoka Girnar
$ashokaText1 = "Devanampiyena piyadasina ranna idam dhammalipi lekhapita.\nIdha na kinci jivam arabhitpa pravajuhitavyam na ca samajo kattavyo.\nDhammasavane dhammanusasti ca devanampiyasa satasahasani\nmanusanam hitasukhaya lokasa ca anukampaya prabhavatu.\nGirnara rock edict boulder facsimile, Saurashtra province.";
createManuscriptFolio($pagesDir . '/ashoka_girnar_p1.jpg', "Ashoka Girnar Rock Inscription (Page 1)", $ashokaText1, "c. 257 BCE", "Brahmi / Western Magadhi", [238, 230, 212]);
copy($pagesDir . '/ashoka_girnar_p1.jpg', $imgDir . '/manuscript-ashoka.jpg');

$ashokaText2 = "Atthavasabhisitasa devanampiyasa piyadasino ranno kalinga vijita.\nDiyadhamate panasatasahase ye tato apavudhe satasahasamate tata hate.\nTato pacha adhuna ladhesu kalingesu tivo dhammavayo dhammakamata\ndhammanusasti ca devanampiyasa. Priyadasi remorse and moral conversion.";
createManuscriptFolio($pagesDir . '/ashoka_girnar_p2.jpg', "Ashoka Girnar Rock Inscription (Page 2 - Edict XIII)", $ashokaText2, "c. 257 BCE", "Rock Inscription Edict XIII", [236, 228, 208]);

// 2. Arthashastra
$arthaText1 = "Prithivya labhe palane ca yavantyarthasastrani purvacaryaih\nprasthapitani prayasastani samhṛtyaikamidamarthasastram kṛtam.\nSukhasya mulam dharmah. Dharmasya mulamarthah.\nArthasya mulam rajyam. Rajyasya mulamindriyajayah.\nGrantha palm-leaf codex, Pataliputra statecraft recension.";
createManuscriptFolio($pagesDir . '/arthashastra_p1.jpg', "Kautiliya Arthashastra (Folio 1)", $arthaText1, "c. 300 BCE", "Sanskrit Grantha Recension", [248, 240, 215]);
copy($pagesDir . '/arthashastra_p1.jpg', $imgDir . '/manuscript-arthashastra.jpg');

$arthaText2 = "Raja atmasampannah prakṛtya sampannah samudayabalo dutapranidhih\ncaramsca pataliputratah prayunjita. Arimitrodasinamadhyamanam\nmandalam vicintya santvadānabhedadandaih prakṛtih svavasamanayet.\nStatecraft, foreign policy, and imperial spy legations.";
createManuscriptFolio($pagesDir . '/arthashastra_p2.jpg', "Kautiliya Arthashastra (Folio 2)", $arthaText2, "c. 300 BCE", "Book I Governance", [246, 238, 210]);

// 3. Ain-i-Akbari
$ainText1 = "Bar danayan-i rumuz-i haqiqat va sarrafan-i nuqud-i maani pushidah\nnamanad kih kargah-i afarīnish bar adl ustuvar ast va shahanshahi-i\narsah-i giti panah-i din va dawlat Hazrat Zill-Allah Jalal al-Din\nMuhammad Akbar Padshah Ghazi ast kih qavanin-i dar al-khilafah Agrah.";
createManuscriptFolio($pagesDir . '/ain_akbari_p1.jpg', "Ain-i-Akbari (Imperial Royal Codex - Folio 1)", $ainText1, "1598 CE", "Classical Persian / Nastaliq", [252, 245, 225]);
copy($pagesDir . '/ain_akbari_p1.jpg', $imgDir . '/manuscript-ain-akbari.jpg');

$ainText2 = "Dar bayan-i Ain-i Dahsalah va zabt-i arazī-i mahrusah bih ihtimam-i\nRajah Todar Mal madar al-maham kih jam va kharj-i subah-jat-i Agrah,\nDihli, Gujarat va Lahawr ra bar qaidah-i zarrin-i paymayish-i Gaz-i Ilahi.";
createManuscriptFolio($pagesDir . '/ain_akbari_p2.jpg', "Ain-i-Akbari (Revenue Dahsala Folio 2)", $ainText2, "1598 CE", "Raja Todar Mal Revenue Code", [250, 243, 220]);

// 4. Harshacharita
$harshaText1 = "Jayati sriharshadevo bhuvanaikavirah sakalasamantamukutamanimarici\nmalacumbitacaranakamalah kanyakubjeshvarah. Sa hi paramamahesvaro\nbhutvapi sugatasasane param pritimavahat. Prayagatirthe\nmahamokshaparisadam cakre. Banabhatta court biography.";
createManuscriptFolio($pagesDir . '/harshacharita_p1.jpg', "Harshacharita of Banabhatta (Folio 1)", $harshaText1, "630 CE", "Sharada / Devanagari Birch-Bark", [242, 232, 205]);
copy($pagesDir . '/harshacharita_p1.jpg', $imgDir . '/manuscript-harshacharita.jpg');

// 5. Surat Dagregister
$suratText1 = "Anno 1642 den 14 November in de haven van Soeratta aengecomen\nhet fluitschip Dordrecht uyt Batavia met cargasoen van noten, foelie\nen tin. Alhier vertoont het keizerlijck Farman uyt Agra door bemiddeling\nvan de gouverneur aengaende de doorgang van karavanen.";
createManuscriptFolio($pagesDir . '/surat_dagregister_p1.jpg', "Surat VOC Maritime Ledger (Folio 1)", $suratText1, "1642 CE", "Early Modern Dutch & Persian", [240, 236, 222]);
copy($pagesDir . '/surat_dagregister_p1.jpg', $imgDir . '/manuscript-surat.jpg');

// 6. Kalpasutra
$kalpaText = "Namo Arihantanam namo Siddhanam namo Ayariyanam namo Uvajjhayanam.\nBhadrabahu svami racitam Kalpasutram. Illustrated golden ink Jain\nrecension from Patan libraries with illuminated marginal Tirthankara figures.";
createManuscriptFolio($pagesDir . '/kalpasutra_p1.jpg', "Kalpasutra Jain Illustrated Folios", $kalpaText, "1420 CE", "Jain Devanagari / Prakrit", [255, 248, 220]);
copy($pagesDir . '/kalpasutra_p1.jpg', $imgDir . '/manuscript-kalpasutra.jpg');

echo "Generating historical person portraits/badges...\n";
createPersonBadge($personsDir . '/ashoka.png', "Ashoka Maurya", "Emperor of India", "304 - 232 BCE");
createPersonBadge($personsDir . '/chanakya.png', "Kautilya Chanakya", "Author of Arthashastra", "375 - 283 BCE");
createPersonBadge($personsDir . '/chandragupta.png', "Chandragupta Maurya", "Maurya Empire Founder", "340 - 297 BCE");
createPersonBadge($personsDir . '/harsha.png', "Harsha Vardhana", "Emperor of Kannauj", "590 - 647 CE");
createPersonBadge($personsDir . '/banabhatta.png', "Banabhatta", "Sanskrit Court Poet", "595 - 650 CE");
createPersonBadge($personsDir . '/akbar.png', "Emperor Akbar", "Mughal Sovereign", "1542 - 1605 CE");
createPersonBadge($personsDir . '/todarmal.png', "Raja Todar Mal", "Finance Minister", "1515 - 1589 CE");
createPersonBadge($personsDir . '/abulfazl.png', "Shaikh Abul Fazl", "Imperial Historiographer", "1551 - 1602 CE");
createPersonBadge($personsDir . '/xuanzang.png', "Xuanzang (Hiuen Tsang)", "Buddhist Pilgrim Scholar", "602 - 664 CE");

echo "All graphic assets generated successfully!\n";

