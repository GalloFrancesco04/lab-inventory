<?php

require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../../Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../Http/Request.php';

// Import Commands and Handlers
require_once __DIR__ . '/../../Application/Commands/Auth/LoginCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Auth/LoginHandler.php';

class AuthController {
    private $mediator;

    public function __construct() {
        $this->mediator = new Mediator();
        // register commands and their handlers
        $this->mediator->register(LoginCommand::class, LoginHandler::class);
    }

    /**
     * POST /api/login
     */
    public function login(Request $request) {
        $data = $request->getBody();

        try {
            // send login command via mediator
            $command = new LoginCommand($data); 
            $tokenData = $this->mediator->send($command);

            // back to client
            Response::success($tokenData); 
        } catch (Exception $e) {
            Response::error($e->getMessage(), "LOGIN_FAILED", 401);
        }
    }

    /**
     * POST /api/logout
     */
    //the logout process can be handled client-side by simply deleting the token.
    public function logout(Request $request) {
        
        Response::success(["message" => "Logout effettuato con successo"]);
    }
}