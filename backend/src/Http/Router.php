<?php
// Router class to map HTTP requests to handlers and extract path parameters so that they can be passed to the handlers
class Router {
    private $routes=[];

    public function add($method,$path,$handler){

        // Convert path with parameters to regex
       $pathRegex = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $path);
        
        // Add start and end delimiters
        $pathRegex = '#^' . $pathRegex . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $pathRegex,
            'handler' => $handler
        ];
    }

    // Dispatch the request to the appropriate handler
    public function dispatch($requestMethod, $RequestUri){

        $parsedUrl= parse_url($RequestUri);
        $path=$parsedUrl['path'];
        // Iterate through registered routes to find a match
        foreach($this->routes as $route) {
            // Match method and path 
            if($route['method'] === $requestMethod && preg_match($route['path'],$path, $matches)){

                array_shift($matches); // Remove full match

                // If handler is a callable function 
                if(is_array($route['handler'])){

                        // Handler is a controller class and method
                    $controllerName= $route['handler'][0];
                    $actionName= $route['handler'][1];

                    $controller=new $controllerName();

                    // Call the controller action with extracted parameters
                    call_user_func_array([$controller, $actionName], $matches);

                    return;
                }
            }
        }
         // No matching route found
    require_once __DIR__ . '/utils/JsonResponse.php';
        JsonResponse::error("Endpoint not found", "ROUTE_NOT_FOUND", 404);
}
    }
   