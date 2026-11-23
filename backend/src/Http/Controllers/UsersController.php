<?php

require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../../Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../Http/Request.php';
require_once __DIR__ . '/../../Application/Commands/Users/ViewTechniciansCommand.php';
require_once __DIR__ . '/../../Application/Handlers/Users/ViewTechniciansHandler.php';

class UsersController {
    private $mediator;

    public function __construct() {
        $this->mediator = new Mediator();
        $this->mediator->register(ViewTechniciansCommand::class, ViewTechniciansHandler::class);
    }

    /**
     * GET /api/technicians
     */
    public function getTechnicians(Request $request) {
        try {
            $technicians = $this->mediator->send(new ViewTechniciansCommand());

            Response::success(array_map(fn($tech) => [
                'id' => $tech->getId(),
                'email' => $tech->getEmail(),
                'role' => $tech->getRole(),
            ], $technicians));
        } catch (Exception $e) {
            Response::error($e->getMessage(), "DB_ERROR", 500);
        }
    }
}
