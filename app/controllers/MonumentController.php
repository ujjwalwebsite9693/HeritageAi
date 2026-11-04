<?php
// app/controllers/MonumentController.php - Monuments & Architecture Explorer and Management

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/Monument.php';
require_once __DIR__ . '/../models/Person.php';
require_once __DIR__ . '/../models/Place.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Csrf.php';
require_once __DIR__ . '/../helpers/Response.php';

class MonumentController {
    public function index(): void {
        $monuments = Monument::all();
        require __DIR__ . '/../views/monuments.php';
    }

    public function detail(): void {
        $id = (int)($_GET['id'] ?? 0);
        $monument = Monument::find($id);

        if (!$monument) {
            http_response_code(404);
            require __DIR__ . '/../views/404.php';
            return;
        }

        require __DIR__ . '/../views/monument_detail.php';
    }

    public function create(): void {
        Auth::requireRole('researcher', 'admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate()) {
                Response::error("CSRF token validation failed", 403);
            }

            if (empty($_POST['name']) || empty($_POST['built_by'])) {
                $_SESSION['flash_error'] = "Monument name and builder are required.";
                Response::redirect('/monuments/create');
            }

            $id = Monument::create($_POST);
            $_SESSION['flash_success'] = "Monument '" . htmlspecialchars($_POST['name']) . "' successfully added to archive!";
            Response::redirect('/monument?id=' . $id);
        }

        $persons = Person::all();
        $places = Place::all();
        require __DIR__ . '/../views/monument_form.php';
    }

    public function edit(): void {
        Auth::requireRole('researcher', 'admin');

        $id = (int)($_GET['id'] ?? 0);
        $monument = Monument::find($id);
        if (!$monument) {
            Response::error("Monument not found", 404);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate()) {
                Response::error("CSRF token validation failed", 403);
            }

            if (empty($_POST['name']) || empty($_POST['built_by'])) {
                $_SESSION['flash_error'] = "Monument name and builder are required.";
                Response::redirect('/monuments/edit?id=' . $id);
            }

            Monument::update($id, $_POST);
            $_SESSION['flash_success'] = "Monument '" . htmlspecialchars($_POST['name']) . "' updated successfully!";
            Response::redirect('/monument?id=' . $id);
        }

        $persons = Person::all();
        $places = Place::all();
        require __DIR__ . '/../views/monument_form.php';
    }

    public function delete(): void {
        Auth::requireRole('researcher', 'admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::validate()) {
            Response::error("Invalid request", 403);
        }

        $id = (int)($_POST['id'] ?? 0);
        Monument::delete($id);
        $_SESSION['flash_success'] = "Monument removed successfully.";
        Response::redirect('/monuments');
    }
}
