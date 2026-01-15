
<?php
// Router class to map HTTP requests to handlers and extract path parameters so that they can be passed to the handlers
class Router {
    private $routes=[];

    public function add($method,$path,$handler){

        // Convert path with parameters to regex
        $pathRegex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<\1>[^/]+)', $path);
        
        // Add start and end delimiters
        $pathRegex = '#^' . $pathRegex . '$#';

        $this->routes[] = [
            'method' => $method,
            'pathRegex' => $pathRegex,
            'handler' => $handler
        ];
    }
}