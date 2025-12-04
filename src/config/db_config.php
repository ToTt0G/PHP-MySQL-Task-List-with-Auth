<?php
namespace App\Config;
use PDO;
use PDOException;

function get_db_connection() {
    static $conn = null;

    if ($conn === null) {
        if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
            http_response_code(403);
            die('Forbidden');
        }

        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT') ?: 3306;
        $db = getenv('MYSQL_DATABASE');
        $user = getenv('MYSQL_USER');
        $pass = getenv('MYSQL_PASSWORD');
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $conn = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }

        try {
            $sqlUsers = "CREATE TABLE IF NOT EXISTS users (
                id CHAR(36) PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $conn->exec($sqlUsers);

            $sqlTasks = "CREATE TABLE IF NOT EXISTS tasks (
                id VARCHAR(255) PRIMARY KEY UNIQUE,
                value TEXT NOT NULL,
                user_id CHAR(36) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            $conn->exec($sqlTasks);

            $sqlSessions = "CREATE TABLE IF NOT EXISTS sessions (
                id CHAR(36) PRIMARY KEY UNIQUE,
                user_id CHAR(36) NOT NULL,
                session_token VARCHAR(255) NOT NULL UNIQUE,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            $conn->exec($sqlSessions);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Table creation failed: ' . $e->getMessage()]);
            exit;
        }
    }

    return $conn;
}
?>