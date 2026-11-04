<?php
// app/helpers/Calendar.php - Historical Calendar normalization and formatting

class Calendar {
    /**
     * Convert any calendar year to normalized CE integer (negative for BCE)
     */
    public static function normalizeToCe(int $year, string $calendarType): int {
        $calendarType = strtolower(trim($calendarType));
        switch ($calendarType) {
            case 'vikram samvat':
            case 'vikram':
            case 'vs':
                // Vikram Samvat is ahead of CE by 57 years
                return $year - 57;

            case 'shaka':
            case 'saka':
                // Shaka era starts 78 CE
                return $year + 78;

            case 'hijri':
            case 'ah':
                // Hijri lunar conversion approximation
                return (int) round(($year * 0.970224) + 621.57);

            case 'gregorian':
            case 'ce':
            case 'ad':
            default:
                return $year;
        }
    }

    /**
     * Format a normalized CE year nicely for display (e.g. "-257" -> "257 BCE", "1598" -> "1598 CE")
     */
    public static function formatYear(int $year): string {
        if ($year < 0) {
            return abs($year) . " BCE";
        } elseif ($year === 0) {
            return "1 BCE";
        } else {
            return $year . " CE";
        }
    }

    /**
     * Given a normalized year, return equivalences in other calendars
     */
    public static function getEquivalences(int $ceYear): array {
        $vs = $ceYear + 57;
        $shaka = $ceYear - 78;
        $hijri = null;
        if ($ceYear >= 622) {
            $hijri = (int) round(($ceYear - 621.57) / 0.970224);
        }

        return [
            'ce' => self::formatYear($ceYear),
            'sort_year' => $ceYear,
            'vikram_samvat' => $vs > 0 ? "VS " . $vs : null,
            'shaka' => $shaka > 0 ? "Śaka " . $shaka : null,
            'hijri' => $hijri ? $hijri . " AH" : null,
        ];
    }
}

