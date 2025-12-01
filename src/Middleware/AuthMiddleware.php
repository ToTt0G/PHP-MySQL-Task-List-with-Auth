<?php
namespace App\Middleware;

use App\Models\Sessions;
use App\Models\Users;

class AuthMiddleware {
    private static $sessionsModel;
    private static $usersModel;

    public static function init($db_connection) {
        self::$sessionsModel = new Sessions($db_connection);
        self::$usersModel = new Users($db_connection);
    }

    public static function handle() {
        if (isset($_SESSION['user_id'])) {
            // The user is already logged in with an active PHP session.
            // No need to refresh the 'remember_me' session here.

            // Check if the user is an admin
            $user = self::$usersModel->getUserById($_SESSION['user_id']);
            if ($user['role'] !== 'admin') {
                if (strpos($_SERVER['REQUEST_URI'], '/api/admin') === 0 || strpos($_SERVER['REQUEST_URI'], '/admin') === 0 || preg_match('#^/api/users/\d+#', $_SERVER['REQUEST_URI'])) {
                    header('Content-Type: application/json');
                    http_response_code(403); // Forbidden
                    echo json_encode(['success' => false, 'message' => 'Forbidden']);
                } else {
                    //contine with normal request
                    return;
                }
                exit();
            }
        } else {
            if (isset($_COOKIE['remember_me'])) {
                $session = self::$sessionsModel->getSession($_COOKIE['remember_me']);
                if ($session) {
                    // Refresh the session token in the database and the cookie
                    self::$sessionsModel->refreshSession($_COOKIE['remember_me']);
                    setcookie('remember_me', $_COOKIE['remember_me'], time() + (86400 * 30), "/", "", false, true); // 30 days

                    $user = self::$usersModel->getUserById($session['user_id']);
                    if ($user) {
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['name'] = $user['name'];
                        // The user is now logged in, you can proceed.
                        header('Location: /tasks');
                        exit();
                    }
                }
            }

            // For API requests, return a JSON error
            if (strpos($_SERVER['REQUEST_URI'], '/api') === 0) {
                header('Content-Type: application/json');
                http_response_code(401); // Unauthorized
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            } else {
                // For web requests, redirect to the login page
                header('Location: /login');
            }
            exit();
        }
        
    }
}