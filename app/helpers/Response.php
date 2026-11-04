<?php
// app/helpers/Response.php - HTTP and JSON response formatting

class Response {
    public static function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    public static function error(string $message, int $status = 400, array $extra = []): void {
        self::json(array_merge([
            'success' => false,
            'error' => $message,
            'status' => $status
        ], $extra), $status);
    }

    public static function success(array $data = [], string $message = 'Success'): void {
        self::json(array_merge([
            'success' => true,
            'message' => $message
        ], $data));
    }

    public static function redirect(string $url): void {
        header("Location: $url");
        exit;
    }
}

