<?php
// app/controllers/TimelineController.php - Interactive Historical Timeline

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Place.php';
require_once __DIR__ . '/../helpers/Calendar.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Csrf.php';
require_once __DIR__ . '/../helpers/Response.php';

class TimelineController {
    public function index(): void {
        $category = $_GET['category'] ?? null;
        $placeId = !empty($_GET['place_id']) ? (int)$_GET['place_id'] : null;

        $events = Event::all([
            'category' => $category,
            'place' => $placeId
        ]);

        $places = Place::all();

        // Convert events into JSON items format for vis-timeline
        $timelineItems = [];
        foreach ($events as $e) {
            $year = (int)$e['sort_year'];
            $formattedDate = Calendar::formatYear($year);
            $equivalences = Calendar::getEquivalences($year);

            // Vis-timeline uses YYYY-MM-DD or standard year format
            // For BCE years in vis-timeline, format as "-000261-01-01"
            $visStart = sprintf("%+07d-01-01", $year);

            $classes = 'timeline-item timeline-' . $e['category'];

            $content = '<div class="tl-bubble">' .
                       '<strong>' . htmlspecialchars($e['title']) . '</strong>' .
                       '<span class="tl-date">' . htmlspecialchars($formattedDate) . '</span>' .
                       '</div>';

            $tooltip = "<strong>" . htmlspecialchars($e['title']) . "</strong><br>" .
                       "<em>Date: " . htmlspecialchars($e['date_text']) . " (" . $formattedDate . ")</em><br>" .
                       (!empty($equivalences['vikram_samvat']) ? "Vikram Samvat: " . $equivalences['vikram_samvat'] . "<br>" : "") .
                       (!empty($equivalences['shaka']) ? "Śaka: " . $equivalences['shaka'] . "<br>" : "") .
                       (!empty($equivalences['hijri']) ? "Hijri: " . $equivalences['hijri'] . "<br>" : "") .
                       "Place: " . htmlspecialchars($e['place_ancient'] ?? 'Unknown') . "<br>" .
                       htmlspecialchars(substr($e['description'] ?? '', 0, 160)) . "...";

            $timelineItems[] = [
                'id' => (int)$e['id'],
                'content' => $content,
                'title' => $tooltip,
                'start' => $visStart,
                'group' => $e['category'],
                'className' => $classes,
                'data' => [
                    'id' => $e['id'],
                    'title' => $e['title'],
                    'description' => $e['description'],
                    'date_text' => $e['date_text'],
                    'category' => $e['category'],
                    'place_ancient' => $e['place_ancient'] ?? '',
                    'place_modern' => $e['place_modern'] ?? '',
                    'sources' => $e['sources'] ?? []
                ]
            ];
        }

        require __DIR__ . '/../views/timeline.php';
    }

    public function manage(): void {
        Auth::requireRole('researcher', 'admin');
        $pdo = DB::getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate()) {
                Response::error("Invalid CSRF token", 403);
            }

            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $dateText = trim($_POST['date_text'] ?? '');
            $yearValue = (int)($_POST['year_value'] ?? 0);
            $calendarType = $_POST['calendar_type'] ?? 'Gregorian';
            $placeId = !empty($_POST['place_id']) ? (int)$_POST['place_id'] : null;
            $category = $_POST['category'] ?? 'politics';
            $manuscriptId = !empty($_POST['manuscript_id']) ? (int)$_POST['manuscript_id'] : null;
            $quoteRef = trim($_POST['quote_ref'] ?? '');

            Event::create([
                'title' => $title,
                'description' => $description,
                'date_text' => $dateText,
                'year_value' => $yearValue,
                'calendar_type' => $calendarType,
                'place_id' => $placeId,
                'category' => $category,
                'confidence' => 0.95,
                'status' => 'approved',
                'manuscript_id' => $manuscriptId,
                'quote_ref' => $quoteRef
            ]);

            $_SESSION['flash_success'] = "Historical event created and cataloged on the timeline!";
            Response::redirect('/events/manage');
            return;
        }

        $events = Event::all(['status' => '%']); // fetch all including pending
        $places = Place::all();
        $manuscripts = $pdo->query("SELECT id, title FROM manuscripts WHERE status = 'published'")->fetchAll();

        require __DIR__ . '/../views/events_manage.php';
    }
}

