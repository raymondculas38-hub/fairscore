<?php

class Router
{
    protected $routes = [];

    public function get($uri, $action)
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post($uri, $action)
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function put($uri, $action)
    {
        $this->addRoute('PUT', $uri, $action);
    }

    public function delete($uri, $action)
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    protected function addRoute($method, $uri, $action)
    {
        // Convert {param} to regex group
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $uri);
        $pattern = '#^' . $pattern . '$#';
        $this->routes[] = compact('method', 'uri', 'action', 'pattern');
    }

    public function route($uri, $method)
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                $params = array_values(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));

                if (is_callable($route['action'])) {
                    return call_user_func_array($route['action'], $params);
                }

                if (is_array($route['action'])) {
                    [$controllerClass, $methodName] = $route['action'];
                    // Remove namespace for instantiation if using auto-loaded classes directly
                    $parts = explode('\\', $controllerClass);
                    $className = end($parts);
                    
                    if (!class_exists($className)) {
                         $className = $controllerClass;
                    }
                    
                    $controller = new $className();
                    return call_user_func_array([$controller, $methodName], $params);
                }
            }
        }

        $this->abort();
    }

    protected function abort($code = 404)
    {
        http_response_code($code);
        echo "404 Not Found";
        exit();
    }
}
