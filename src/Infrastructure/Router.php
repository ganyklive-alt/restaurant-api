<?php

namespace App\Infrastructure;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler)
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler)
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = '#^' . preg_replace('#\{([^/]+)\}#', '([^/]+)', $route) . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                [$class, $method] = $handler;
                $controller = new $class();
                return $controller->$method(...$matches);
            }
        }

        http_response_code(404);
        echo json_encode(["error" => "Route not found"]);
    }
}