<?php
// public/api/index.php - Dedicated API endpoint entry point

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
require_once __DIR__ . '/../../app/helpers/Response.php';
require_once __DIR__ . '/../../app/controllers/ApiController.php';

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

$api = new ApiController();
$api->handle($requestUri, $requestMethod);

