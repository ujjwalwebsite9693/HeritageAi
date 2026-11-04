<?php
// app/controllers/PersonController.php - Historical Persons and Genealogical Networks

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/Person.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Csrf.php';
require_once __DIR__ . '/../helpers/Response.php';

class PersonController {
    public function index(): void {
        $persons = Person::all();
        require __DIR__ . '/../views/persons.php';
    }

    public function detail(): void {
        $id = (int)($_GET['id'] ?? 0);
        $person = Person::find($id);

        if (!$person) {
            http_response_code(404);
            require __DIR__ . '/../views/404.php';
            return;
        }

        $genealogyData = Person::getGenealogyTree($id);
        require __DIR__ . '/../views/person_detail.php';
    }

    public function create(): void {
        Auth::requireRole('researcher', 'admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate()) {
                Response::error("CSRF token validation failed", 403);
            }

            if (empty($_POST['name']) || empty($_POST['role'])) {
                $_SESSION['flash_error'] = "Emperor / Figure name and role are required.";
                Response::redirect('/persons/create');
            }

            $id = Person::create($_POST);
            $_SESSION['flash_success'] = "Historical figure '" . htmlspecialchars($_POST['name']) . "' added successfully!";
            Response::redirect('/person?id=' . $id);
        }

        require __DIR__ . '/../views/person_form.php';
    }

    public function edit(): void {
        Auth::requireRole('researcher', 'admin');

        $id = (int)($_GET['id'] ?? 0);
        $person = Person::find($id);
        if (!$person) {
            Response::error("Person not found", 404);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate()) {
                Response::error("CSRF token validation failed", 403);
            }

            if (empty($_POST['name']) || empty($_POST['role'])) {
                $_SESSION['flash_error'] = "Emperor / Figure name and role are required.";
                Response::redirect('/persons/edit?id=' . $id);
            }

            Person::update($id, $_POST);
            $_SESSION['flash_success'] = "Historical figure '" . htmlspecialchars($_POST['name']) . "' updated successfully!";
            Response::redirect('/person?id=' . $id);
        }

        require __DIR__ . '/../views/person_form.php';
    }

    public function delete(): void {
        Auth::requireRole('researcher', 'admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::validate()) {
            Response::error("Invalid request", 403);
        }

        $id = (int)($_POST['id'] ?? 0);
        Person::delete($id);
        $_SESSION['flash_success'] = "Historical figure removed successfully.";
        Response::redirect('/persons');
    }
}
