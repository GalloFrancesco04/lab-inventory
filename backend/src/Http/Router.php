<?php
// Router class to map HTTP requests to handlers and extract path parameters so that they can be passed to the handlers
class Router {
    private $routes=[];

    public function add($method,$path,$handler){

        // Convert path with parameters to regex
       $pathRegex = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $path);
        
        // Add start and end delimiters
        $pathRegex = '#^' . $pathRegex . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $pathRegex,
            'handler' => $handler
        ];
    }

    // Dispatch the request to the appropriate handler
    public function dispatch($requestMethod, $RequestUri, Request $request){

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
                    
                    if (class_exists($controllerName)){
                        // Instantiate controller
                        $controller=new $controllerName();
                        // Prepare parameters: first the Request object, then the extracted path parameters
                        $params = array_merge([$request], $matches);
                        // Call the controller action with extracted parameters
                        call_user_func_array([$controller, $actionName], $params);

                    return;
                    }

                    

                    
                }
            }
        }
         // No matching route found
        require_once __DIR__ . '/Response.php';
        Response::error("Endpoint not found", "ROUTE_NOT_FOUND", 404);
}
    }
   