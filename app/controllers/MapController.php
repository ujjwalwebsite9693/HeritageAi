<?php
// app/controllers/MapController.php - Historical Cartography and Routes with Leaflet.js

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/Place.php';

class MapController {
    public function index(): void {
        $places = Place::all();

        // Historical Trade and Pilgrimage Routes
        $routes = [
            [
                'name' => 'The Grand Uttarāpatha (Northern Silk & Imperial Trunk Road)',
                'description' => 'Ancient arterial highway connecting Takshashila in the northwest across Indraprastha, Kanyakubja, and Prayaga to Pataliputra in Magadha.',
                'color' => '#b7791f',
                'points' => [
                    ['name' => 'Takṣaśilā', 'lat' => 33.7463, 'lng' => 72.8258],
                    ['name' => 'Indraprastha', 'lat' => 28.6562, 'lng' => 77.2410],
                    ['name' => 'Kānyakubja', 'lat' => 27.0543, 'lng' => 79.9197],
                    ['name' => 'Prayāga', 'lat' => 25.4358, 'lng' => 81.8463],
                    ['name' => 'Pāṭaliputra', 'lat' => 25.6101, 'lng' => 85.1415],
                    ['name' => 'Nālandā', 'lat' => 25.1357, 'lng' => 85.4450],
                ]
            ],
            [
                'name' => 'The Mughal Imperial Agra-Surat Commercial Corridor',
                'description' => 'The premier overland trade highway linking the Mughal court at Agra via Ujjayinī to the international maritime haven of Surat.',
                'color' => '#805ad5',
                'points' => [
                    ['name' => 'Āgra', 'lat' => 27.1767, 'lng' => 78.0081],
                    ['name' => 'Ujjayinī', 'lat' => 23.1765, 'lng' => 75.7885],
                    ['name' => 'Sūrat Bander', 'lat' => 21.1702, 'lng' => 72.8311],
                ]
            ],
            [
                'name' => 'Ashokan Girnar Royal Epigraphic Road',
                'description' => 'Imperial administrative route linking the imperial capital to the western provincial reservoir at Girnar (Saurashtra).',
                'color' => '#c53030',
                'points' => [
                    ['name' => 'Ujjayinī', 'lat' => 23.1765, 'lng' => 75.7885],
                    ['name' => 'Girnāra (Junagadh)', 'lat' => 21.5222, 'lng' => 70.4579],
                ]
            ]
        ];

        require __DIR__ . '/../views/map.php';
    }

    public function detail(): void {
        $id = (int)($_GET['id'] ?? 0);
        $place = Place::find($id);

        if (!$place) {
            http_response_code(404);
            require __DIR__ . '/../views/404.php';
            return;
        }

        require __DIR__ . '/../views/place_detail.php';
    }
}

