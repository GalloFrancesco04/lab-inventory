
<?php

// Mediator pattern implementation for command handling
class Mediator {

    public function register ($commandName, $handler) {
        // Store the handler for the given command
        $this->handlers[$commandName] = $handler;

    }

    // Send a command to its registered handler
    public function send($commandName, $data=[]) {
        if(!isset($this->handlers[$commandName])) {

            error_log("Mediator Error: No handler for command '$commandName'");
            throw new Exception("No handler registered for command: " . $commandName);
            
        }

        $handler = $this->handlers[$commandName];

        
        return call_user_func($handler, $data);
    }
}