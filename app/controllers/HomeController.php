<?php
// app/controllers/HomeController.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helpers/Auth.php';

class HomeController {
    public function index(): void {
        $pdo = DB::getConnection();

        // High-level statistics
        $stats = [
            'manuscripts' => (int)$pdo->query("SELECT COUNT(*) FROM manuscripts WHERE status = 'published'")->fetchColumn(),
            'monuments' => (int)$pdo->query("SELECT COUNT(*) FROM monuments")->fetchColumn(),
            'persons' => (int)$pdo->query("SELECT COUNT(*) FROM persons")->fetchColumn(),
            'places' => (int)$pdo->query("SELECT COUNT(*) FROM places")->fetchColumn(),
            'events' => (int)$pdo->query("SELECT COUNT(*) FROM events WHERE status = 'approved'")->fetchColumn(),
            'links' => (int)$pdo->query("SELECT COUNT(*) FROM links WHERE status = 'confirmed'")->fetchColumn(),
        ];

        // Featured Famous Monuments (Taj Mahal, Red Fort, Humayun's Tomb, Fatehpur Sikri, Nalanda, Qutub Minar)
        $featuredMonuments = $pdo->query("SELECT * FROM monuments ORDER BY id ASC LIMIT 6")->fetchAll();
        foreach ($featuredMonuments as &$mon) {
            $mon['highlights_array'] = json_decode($mon['highlights'] ?? '[]', true) ?: [];
        }

        // Featured Historical Emperors & Leaders (Akbar, Humayun, Shah Jahan, Ashoka, Babur, Birbal)
        $featuredFigures = $pdo->query("SELECT * FROM persons WHERE id IN (6, 11, 10, 1, 12, 13) ORDER BY FIELD(id, 6, 11, 10, 1, 12, 13)")->fetchAll();

        // Featured manuscripts
        $featured = $pdo->query("SELECT m.*, l.name as language_name, p.ancient_name as place_ancient, p.modern_name as place_modern
                                 FROM manuscripts m 
                                 JOIN languages l ON m.language_id = l.id
                                 LEFT JOIN places p ON m.origin_place_id = p.id
                                 WHERE m.status = 'published'
                                 ORDER BY m.era_start ASC LIMIT 3")->fetchAll();

        require __DIR__ . '/../views/home.php';
    }
}
