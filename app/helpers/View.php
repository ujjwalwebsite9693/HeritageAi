<?php
// app/helpers/View.php - Clean presentation and readability formatting helpers

class ViewHelper {
    public static function cleanDocTitle(string $title): string {
        if (str_contains($title, 'Rock Edict') || str_contains($title, 'Girnar') || str_contains($title, 'Priyadasi')) {
            return "Ashoka's Rock Inscription (सम्राट अशोक के शिलालेख)";
        }
        if (str_contains($title, 'Arthaśāstra') || str_contains($title, 'Arthashastra') || str_contains($title, 'Kauṭilīya')) {
            return "Chanakya's Arthashastra (कौटिल्य का अर्थशास्त्र - राजनीति व शासन)";
        }
        if (str_contains($title, 'Āʼīn-i-Akbarī') || str_contains($title, 'Ain-i-Akbari') || str_contains($title, 'Dastūr')) {
            return "Ain-i-Akbari (आईन-ए-अकबरी - मुग़ल दरबार और कानून)";
        }
        if (str_contains($title, 'Harṣacarita') || str_contains($title, 'Harshacharita') || str_contains($title, 'Bāṇabhaṭṭa')) {
            return "Harshacharita (हर्षचरित - राजा हर्षवर्धन की जीवनी)";
        }
        if (str_contains($title, 'Surat') || str_contains($title, 'Dagregister')) {
            return "Surat Port Trade Records (सूरत बन्दरगाह व्यापार अभिलेख)";
        }
        if (str_contains($title, 'Kalpasūtra') || str_contains($title, 'Kalpasutra')) {
            return "Kalpasutra (कल्पसूत्र - प्राचीन सचित्र ग्रन्थ)";
        }
        return $title;
    }

    public static function cleanPersonName(?string $name): string {
        if (!$name) return 'Historical Figure';
        if (str_contains($name, 'Akbar')) return 'Emperor Akbar (सम्राट अकबर)';
        if (str_contains($name, 'Humayun')) return 'Emperor Humayun (सम्राट हुमायूँ)';
        if (str_contains($name, 'Babur')) return 'Emperor Babur (सम्राट बाबर)';
        if (str_contains($name, 'Shah Jahan')) return 'Emperor Shah Jahan (सम्राट शाहजहाँ)';
        if (str_contains($name, 'Birbal')) return 'Raja Birbal (राजा बीरबल)';
        if (str_contains($name, 'Ashoka')) return 'Emperor Ashoka (सम्राट अशोक महान)';
        if (str_contains($name, 'Kauṭilya') || str_contains($name, 'Chāṇakya') || str_contains($name, 'Chanakya')) return 'Chanakya (चाणक्य / कौटिल्य)';
        if (str_contains($name, 'Chandragupta')) return 'Chandragupta Maurya (चंद्रगुप्त मौर्य)';
        if (str_contains($name, 'Todar Mal')) return 'Raja Todar Mal (राजा टोडरमल)';
        if (str_contains($name, 'Abul-Fazl') || str_contains($name, 'Abul Fazl')) return 'Abul Fazl (अबुल फज़ल)';
        if (str_contains($name, 'Harsha')) return 'King Harshavardhana (राजा हर्षवर्धन)';
        if (str_contains($name, 'Xuanzang')) return 'Hiuen Tsang (ह्वेन त्सांग)';
        if (str_contains($name, 'Bāṇabhaṭṭa') || str_contains($name, 'Banabhatta')) return 'Banabhatta (बाणभट्ट)';
        return $name;
    }

    public static function cleanRole(?string $role): string {
        if (!$role) return 'Scholar / Ruler';
        if (str_contains($role, 'Third Mughal Emperor')) return '3rd Mughal Emperor (मुग़ल सम्राट)';
        if (str_contains($role, 'Second Mughal Emperor')) return '2nd Mughal Emperor (मुग़ल सम्राट)';
        if (str_contains($role, 'Fifth Mughal Emperor')) return '5th Mughal Emperor (मुग़ल सम्राट)';
        if (str_contains($role, 'Founder of the Mughal')) return 'Founder of Mughal Empire (संस्थापक)';
        if (str_contains($role, 'Emperor of the Maurya')) return 'Maurya Emperor (मौर्य सम्राट)';
        if (str_contains($role, 'Founder of the Maurya')) return 'Founder of Maurya Empire (मौर्य संस्थापक)';
        if (str_contains($role, 'Prime Minister') || str_contains($role, 'Political Philosopher')) return 'Prime Minister & Guru (महामंत्री व गुरु)';
        if (str_contains($role, 'Grand Vizier, Poet')) return 'Grand Vizier & Courtier (नवरत्न व कवि)';
        if (str_contains($role, 'Finance Minister')) return 'Imperial Finance Minister (वित्त मंत्री)';
        if (str_contains($role, 'Historiographer')) return 'Imperial Historiographer (इतिहासकार)';
        if (str_contains($role, 'Buddhist Pilgrim')) return 'Traveler & Scholar (यात्री व विद्वान)';
        if (str_contains($role, 'Court Poet')) return 'Celebrated Sanskrit Poet (राजकवि)';
        return $role;
    }

    public static function cleanExplanation(string $exp, int $docA, int $docB): string {
        if (($docA == 1 && $docB == 2) || ($docA == 2 && $docB == 1)) {
            return "चाणक्य ने शासन और राजनीति के जो नियम अर्थशास्त्र में लिखे, सम्राट अशोक ने उन सिद्धांतों को पूरे भारत में शिलालेखों और जन-कल्याण के माध्यम से लागू किया।";
        }
        if (($docA == 3 && $docB == 5) || ($docA == 5 && $docB == 3)) {
            return "सम्राट अकबर ने आईन-ए-अकबरी में व्यापार और कर (customs) के जो नियम बनाए, आगे चलकर सूरत बंदरगाह पर आने वाले जहाजों और व्यापारियों के लिए वही नियम मान्य हुए।";
        }
        if (($docA == 1 && $docB == 4) || ($docA == 4 && $docB == 1)) {
            return "राजा हर्षवर्धन ने सम्राट अशोक की तरह ही प्रजा के कल्याण, दान और अहिंसा की नीतियों को अपनाया और विशाल सभाओं का आयोजन किया।";
        }
        return $exp;
    }
}

// Global procedural shortcuts for convenience in views
if (!function_exists('cleanDocTitle')) {
    function cleanDocTitle(string $title): string {
        return ViewHelper::cleanDocTitle($title);
    }
}

if (!function_exists('cleanPersonName')) {
    function cleanPersonName(?string $name): string {
        return ViewHelper::cleanPersonName($name);
    }
}

if (!function_exists('cleanRole')) {
    function cleanRole(?string $role): string {
        return ViewHelper::cleanRole($role);
    }
}

if (!function_exists('cleanExplanation')) {
    function cleanExplanation(string $exp, int $docA, int $docB): string {
        return ViewHelper::cleanExplanation($exp, $docA, $docB);
    }
}
