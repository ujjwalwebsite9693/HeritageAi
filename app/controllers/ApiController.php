<?php
// app/controllers/ApiController.php - Complete REST API handler

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/Manuscript.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Place.php';
require_once __DIR__ . '/../models/Person.php';
require_once __DIR__ . '/../models/Link.php';
require_once __DIR__ . '/../models/Annotation.php';
require_once __DIR__ . '/../services/SearchService.php';
require_once __DIR__ . '/../services/TranslationService.php';
require_once __DIR__ . '/../services/EntityService.php';
require_once __DIR__ . '/../services/LinkDiscoveryService.php';
require_once __DIR__ . '/../services/ExportService.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Response.php';

class ApiController {
    private ?array $apiUser = null;

    public function __construct() {
        // Authenticate via X-API-KEY header or query param if provided
        $this->apiUser = Auth::authenticateApi();
    }

    public function handle(string $endpoint, string $method): void {
        $parts = explode('/', trim($endpoint, '/'));
        // $parts[0] is 'api'
        $resource = $parts[1] ?? '';
        $id = isset($parts[2]) && is_numeric($parts[2]) ? (int)$parts[2] : null;
        $subResource = $parts[3] ?? ($parts[2] ?? '');

        // Rate limiting header simulation
        header('X-RateLimit-Limit: 120');
        header('X-RateLimit-Remaining: 119');

        switch ($resource) {
            case 'search':
                $q = $_GET['q'] ?? '';
                $results = SearchService::search(['q' => $q, 'limit' => 20]);
                Response::success($results);
                break;

            case 'manuscripts':
                if ($id !== null) {
                    if (isset($parts[3]) && $parts[3] === 'pages') {
                        // GET /api/manuscripts/{id}/pages
                        $pdo = DB::getConnection();
                        $stmt = $pdo->prepare("SELECT * FROM manuscript_pages WHERE manuscript_id = ? ORDER BY page_no ASC");
                        $stmt->execute([$id]);
                        Response::success(['pages' => $stmt->fetchAll()]);
                    } else {
                        // GET /api/manuscripts/{id}
                        $m = Manuscript::find($id);
                        if (!$m) Response::error("Manuscript not found", 404);
                        Response::success(['manuscript' => $m]);
                    }
                } elseif ($method === 'POST') {
                    // POST /api/manuscripts
                    $this->requireRole('researcher', 'admin');
                    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
                    $mId = Manuscript::create($input);
                    Response::success(['manuscript_id' => $mId], "Manuscript created successfully", 201);
                } else {
                    // GET /api/manuscripts?q=&lang=&era=&place=
                    $results = SearchService::search($_GET);
                    Response::success($results);
                }
                break;

            case 'pages':
                if ($id !== null && isset($parts[3])) {
                    if ($parts[3] === 'transcription' && in_array($method, ['PUT', 'POST'])) {
                        // PUT /api/pages/{id}/transcription
                        $this->requireRole('researcher', 'admin');
                        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
                        $pdo = DB::getConnection();
                        $stmt = $pdo->prepare("UPDATE manuscript_pages SET transcription = ?, status = 'corrected', updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$input['transcription'] ?? '', $id]);
                        Response::success([], "Transcription updated");
                    } elseif ($parts[3] === 'translate' && $method === 'POST') {
                        // POST /api/pages/{id}/translate
                        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
                        $lang = $input['target_lang'] ?? 'en';
                        $res = TranslationService::translatePage($id, $lang, $this->apiUser['id'] ?? null);
                        Response::success($res);
                    } elseif ($parts[3] === 'annotations' && $method === 'POST') {
                        // POST /api/pages/{id}/annotations
                        $this->requireAuth();
                        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
                        $annId = Annotation::create([
                            'page_id' => $id,
                            'user_id' => $this->apiUser['id'],
                            'type' => $input['type'] ?? 'reference',
                            'coordinates' => $input['coordinates'] ?? null,
                            'note' => $input['note'] ?? '',
                            'visibility' => $input['visibility'] ?? 'public',
                            'parent_id' => $input['parent_id'] ?? null,
                        ]);
                        Response::success(['annotation_id' => $annId], "Annotation recorded");
                    }
                }
                Response::error("Unknown page endpoint", 404);
                break;

            case 'translations':
                if ($id !== null && isset($parts[3]) && $parts[3] === 'verify' && in_array($method, ['PUT', 'POST'])) {
                    // PUT /api/translations/{id}/verify
                    $this->requireRole('researcher', 'admin');
                    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
                    $upd = TranslationService::updateTranslation($id, $input['text'] ?? '', 'verified', $this->apiUser['id']);
                    Response::success(['translation' => $upd], "Translation verified");
                }
                Response::error("Unknown translation endpoint", 404);
                break;

            case 'events':
                if ($method === 'POST') {
                    // POST /api/events
                    $this->requireRole('researcher', 'admin');
                    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
                    $evId = Event::create($input);
                    Response::success(['event_id' => $evId], "Event created", 201);
                } else {
                    // GET /api/events?from=&to=&place=&category=
                    $events = Event::all($_GET);
                    Response::success(['events' => $events]);
                }
                break;

            case 'places':
                if ($id !== null) {
                    $place = Place::find($id);
                    if (!$place) Response::error("Place not found", 404);
                    Response::success(['place' => $place]);
                } else {
                    $places = Place::all();
                    Response::success(['places' => $places]);
                }
                break;

            case 'persons':
                if ($id !== null) {
                    if (isset($parts[3]) && $parts[3] === 'relations') {
                        // GET /api/persons/{id}/relations
                        $person = Person::find($id);
                        Response::success(['relations' => $person['relations'] ?? []]);
                    } else {
                        // GET /api/persons/{id}
                        $person = Person::find($id);
                        if (!$person) Response::error("Person not found", 404);
                        Response::success(['person' => $person]);
                    }
                } else {
                    $persons = Person::all();
                    Response::success(['persons' => $persons]);
                }
                break;

            case 'links':
                if ($id !== null && isset($parts[3]) && $parts[3] === 'review' && in_array($method, ['PUT', 'POST'])) {
                    // PUT /api/links/{id}/review
                    $this->requireRole('researcher', 'admin');
                    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
                    $status = $input['status'] ?? 'confirmed';
                    Link::updateStatus($id, $status, $this->apiUser['id'] ?? 1);
                    Response::success([], "Link status updated to $status");
                } else {
                    // GET /api/links?manuscript={id}&status=
                    $mId = !empty($_GET['manuscript']) ? (int)$_GET['manuscript'] : null;
                    if ($mId) {
                        $links = Manuscript::getConnectedLinks($mId);
                    } else {
                        $links = Link::all($_GET['status'] ?? null);
                    }
                    Response::success(['links' => $links]);
                }
                break;

            case 'entities':
                if (isset($parts[2]) && $parts[2] === 'duplicates') {
                    // GET /api/entities/duplicates
                    $dups = EntityService::findDuplicates();
                    Response::success(['duplicates' => $dups]);
                } elseif (isset($parts[2]) && $parts[2] === 'merge' && $method === 'POST') {
                    // POST /api/entities/merge
                    $this->requireRole('researcher', 'admin');
                    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
                    EntityService::mergeEntities((int)$input['keep_id'], (int)$input['merge_id'], $this->apiUser['id'] ?? 1);
                    Response::success([], "Entities merged");
                } else {
                    Response::error("Unknown entities endpoint", 404);
                }
                break;

            case 'graph':
                // GET /api/graph?root={id}&depth=2
                $rootId = !empty($_GET['root']) ? (int)$_GET['root'] : null;
                $depth = (int)($_GET['depth'] ?? 2);
                $graph = LinkDiscoveryService::getGraphData($rootId, $depth);
                Response::success($graph);
                break;

            case 'export':
                // GET /api/export/{type}/{id}
                $type = $parts[2] ?? '';
                $exportId = !empty($parts[3]) ? (int)$parts[3] : 0;
                
                if ($type === 'events-csv') {
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="mia_events_' . date('Ymd') . '.csv"');
                    echo ExportService::exportEventsCsv();
                    exit;
                } elseif ($type === 'places-geojson') {
                    header('Content-Type: application/geo+json');
                    echo json_encode(ExportService::exportPlacesGeoJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    exit;
                } elseif ($type === 'citations' && $exportId) {
                    $citations = ExportService::generateCitations($exportId);
                    Response::success(['citations' => $citations]);
                } else {
                    Response::error("Unsupported export type: $type", 400);
                }
                break;

            default:
                Response::error("Endpoint not found", 404);
        }
    }

    private function requireAuth(): void {
        if (!$this->apiUser) {
            Response::error("Unauthorized: Valid API Key or session required.", 401);
        }
    }

    private function requireRole(string ...$roles): void {
        $this->requireAuth();
        if ($this->apiUser['role'] === 'admin') return;
        if (!in_array($this->apiUser['role'], $roles, true)) {
            Response::error("Forbidden: Insufficient role permissions.", 403);
        }
    }
}

