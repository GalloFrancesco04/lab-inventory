<?php

class SessionManager
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            $this->start();
        }
    }

    //start session with secure settings
    private function start(): void
    {
        $options = [
            'cookie_lifetime' => 28800, // session life 8 hours
            'cookie_secure' => false,   
            'cookie_httponly' => true,  //Javascript can't read the cookie (mitigate XSS)
            'cookie_samesite' => 'Strict', // cookie is not sent at other sites (mitigate CSRF)
            'use_strict_mode' => true   // Prevents the server from accepting uninitialized session IDs
        ];

        session_start($options);
    }

    //set user data in session after password verification
    public function setUser(int $id, string $name, int $role): void
    {
        //change session ID after login to prevent session fixation attacks
        session_regenerate_id(true);

        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = $role;
        $_SESSION['login_time'] = time();
    }

    
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    
    public function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public function getUserName(): ?string
    {
        return $_SESSION['user_name'] ?? null;
    }

    
    public function getUserRole(): ?int
    {
        return $_SESSION['user_role'] ?? null;
    }


    public function logout(): void
    {
        //1. clean session data
        $_SESSION = [];

        // 2. Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // 3. Destroy the session
        session_destroy();
    }
}
?>