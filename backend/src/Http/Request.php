<?php

class Request {
    private array $body;
    private array $query;
    private string $method;
    private string $path;

    public function __construct() {
        // 1. Legge il metodo (GET, POST, PUT, etc.)
        $this->method = $_SERVER['REQUEST_METHOD'];

        // 2. Legge il percorso (es. /api/clients)
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->path = $uri;

        // 3. Legge i parametri GET (es. ?page=1&search=sony)
        $this->query = $_GET;

        // 4. Legge il Body JSON (per POST/PUT)
        $input = file_get_contents('php://input');
        $this->body = json_decode($input, true) ?? [];
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function getPath(): string {
        return $this->path;
    }

    // return  array|string|null
    public function getBody(?string $key = null) {
        if ($key) {
            return $this->body[$key] ?? null;
        }
        return $this->body;
    }

    // return array|string|null
    public function getQuery(?string $key = null) {
        if ($key) {
            return $this->query[$key] ?? null;
        }
        return $this->query;
    }
}