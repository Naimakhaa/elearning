<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private string $basePath;

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // 1) Hapus prefix folder project (misal: /ELEARNING_API/public)
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }

        // 2) Hapus basePath (misal: /api)
        if ($this->basePath !== '' && str_starts_with($uri, $this->basePath)) {
            $uri = substr($uri, strlen($this->basePath));
        }

        if ($uri === '') {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertPathToRegex($route['path']);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // buang full match
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        // Endpoint tidak ditemukan
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success'     => false,
            'status_code' => 404,
            'message'     => 'Endpoint not found',
        ], JSON_PRETTY_PRINT);
    }

    private function convertPathToRegex(string $path): string
    {
        // ubah /courses/:id jadi regex /courses/([^/]+)
        $pattern = preg_replace('#/:([a-zA-Z0-9_]+)#', '/([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}
