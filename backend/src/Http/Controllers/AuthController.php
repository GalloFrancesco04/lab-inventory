<?php

require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../../Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../Http/Request.php';
require_once __DIR__ . '/../../Application/Commands/Auth/AuthUserCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Auth/LoginHandler.php';
require_once __DIR__ . '/../../Application/Handlers/Auth/LoginUserCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Auth/AuthUserHandler.php';

require_once __DIR__ . '/../../Application/Commands/Auth/LogoutUserCommand.php';

require_once __DIR__ . '/../../Application/Handlers/Auth/LogoutUserHandler.php';


class AuthController {
    private $mediator;

    public function __construct() {
        $this->mediator = new Mediator();
        // register commands and their handlers
        $this->mediator->register(AuthUserCommand::class, AuthUserHandler::class);
        $this->mediator->register(LoginUserCommand::class, LoginUserHandler::class);
        $this->mediator->register(LogoutUserCommand::class, LogoutUserHandler::class);
    }

    /**
     * POST /api/login
     */
    public function login(Request $request) {
        $data = $request->getBody();

        try {
            // send login command via mediator
            $command = new LoginUserCommand($data['email'], $data['password']); 
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

    try {
            // Anche se non ha parametri, passiamo attraverso il Mediator
            // per gestire eventuali logiche server-side (es. blacklisting token, log db)
            $command = new LogoutUserCommand();
            $this->mediator->send($command);

            Response::success(["message" => "Logout effettuato con successo"]);
        } catch (Exception $e) {
            // Anche se fallisce internamente, all'utente mostriamo successo o errore generico
            Response::error($e->getMessage(), "LOGOUT_ERROR", 500);
        }
    }



    // GET /api/auth/status
     
    public function checkStatus(Request $request) {
        try {
            $command = new AuthUserCommand();
            $result = $this->mediator->send($command);
            Response::success($result);
        } catch (Exception $e) {
            Response::error($e->getMessage(), "AUTH_ERROR", 401);
        }
    }
        
       
}