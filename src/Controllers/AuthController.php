<?php
namespace App\Controllers;

use App\Models\Users;
use App\Models\Sessions;
use App\Helpers\Csrf;

class AuthController
{
    private $usersModel;
    private $sessionsModel;

    public function __construct(Users $usersModel, Sessions $sessionsModel)
    {
        $this->usersModel = $usersModel;
        $this->sessionsModel = $sessionsModel;
    }

    public function handleApiRequest($request_uri, $method)
    {
        if (strpos($request_uri, '/api/auth') === 0) {
            if ($method === "POST") {
                // CSRF Protection
                $headers = getallheaders();
                $token = $headers['X-CSRF-Token'] ?? '';
                
                if (!Csrf::verify($token)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Invalid CSRF token']);
                    return;
                }

                switch ($request_uri) {
                    case '/api/auth/register':
                        $this->register();
                        break;
                    case '/api/auth/login':
                        $this->login();
                        break;
                    case '/api/auth/logout':
                        $this->logout();
                        break;
                    default:
                        echo json_encode(["success" => false, "message" => "Invalid endpoint"]);
                        break;
                }
            } else {
                echo json_encode(["success" => false, "message" => "Invalid method"]);
                exit;
            }

        } elseif (strpos($request_uri, '/api/users') === 0) {
            if ($method === "GET") {
                switch ($request_uri) {
                    case '/api/users/current':
                        $id = substr($request_uri, strpos($request_uri, '/api/users/') + strlen('/api/users/'));
                        $user = $this->usersModel->getUserById($id);
                        if ($user) {
                            echo json_encode($user);
                        } else {
                            echo json_encode(["success" => false, "message" => "User not found"]);
                        }
                        break;
                    default:
                        echo json_encode(["success" => false, "message" => "Invalid endpoint"]);
                        break;
                }
            } else {
                echo json_encode(["success" => false, "message" => "Invalid method"]);
                exit;
            }
        }
    }

    public function register()
    {
        $email = htmlspecialchars($_POST["email"], ENT_QUOTES, 'UTF-8');
        $password = htmlspecialchars($_POST["password"], ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars($_POST["name"], ENT_QUOTES, 'UTF-8');
        $result = $this->usersModel->register($email, $password, $name);
        echo json_encode($result);
        exit;
    }

    public function login()
    {
        $email = htmlspecialchars($_POST["email"], ENT_QUOTES, 'UTF-8');
        $password = htmlspecialchars($_POST["password"], ENT_QUOTES, 'UTF-8');
        $remember_me = isset($_POST['remember_me']);

        $result = $this->usersModel->login($email, $password);
        if ($result['success']) {
            // Regenerate the session ID to prevent session fixation attacks
            session_regenerate_id(true);

            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['name'] = $result['user']['name'];

            if ($remember_me) {
                $session_token = $this->sessionsModel->createSession($result['user']['id']);
                if ($session_token) {
                    // Set cookie for 30 days, HttpOnly=true
                    setcookie('remember_me', $session_token, time() + (86400 * 30), "/", "", false, true); 
                }
            }
            // Remove password from the response
            unset($result['user']['password']);
        }
        echo json_encode($result);
        exit;
    }

    public function logout()
    {
        if (isset($_COOKIE['remember_me'])) {
            $this->sessionsModel->deleteSession($_COOKIE['remember_me']);
            unset($_COOKIE['remember_me']);
            setcookie('remember_me', '', time() - 3600, '/', "", false, true); // expire the cookie
        }
        session_destroy();
        header('Location: /login');
        exit;
    }
}
?>