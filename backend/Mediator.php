
<?php

// Mediator pattern implementation for command handling
class Mediator {

    public function register ($commandName, $handler) {
        // Store the handler for the given command
        $this->handlers[$commandName] = $handler;

    }

  
}