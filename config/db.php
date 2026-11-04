<?php
// config/db.php - Database connection singleton (PDO with prepared statements)

class DB {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/app.php';
            $dbCfg = $config['db'];

            $dsn = "mysql:host={$dbCfg['host']};port={$dbCfg['port']};dbname={$dbCfg['name']};charset={$dbCfg['charset']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$instance = new PDO($dsn, $dbCfg['user'], $dbCfg['pass'], $options);
            } catch (PDOException $e) {
                if ($config['debug']) {
                    die("Database Connection Error: " . $e->getMessage());
                } else {
                    die("A database error occurred. Please contact system administrator.");
                }
            }
        }

        return self::$instance;
    }
}

