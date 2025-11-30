<?php
// src/Core/Router.php

declare(strict_types=1);

namespace App\Core;

/**
 * Simple Router untuk REST API
 *
 * Contoh:
 *  $router->get('/courses', [$courseController, 'index']);
 *  $router->get('/courses/:id', [$courseController, 'show']);
 */
class Router
{
    /**
     * @var array<int,array{method:string,path:string,handler:callable}>
     */
    private array $routes = [];

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
            'method'  => $method,
            'path'    => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // basePath: /api (diset di index.php lewat .htaccess atau manual)
        $basePath = '/api';

        if (str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
            if ($uri === '') {
                $uri = '/';
            }
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertPathToRegex($route['path']);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // buang full match

                // Panggil handler dengan parameter dari path
                call_user_func_array($route['handler'], array_values($matches));
                return;
            }
        }

        // 404 kalau tidak ada route yang cocok
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success'     => false,
            'status_code' => 404,
            'message'     => 'Endpoint not found',
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Konversi path seperti "/courses/:id" menjadi regex
     * Example: "/courses/:id" -> "#^/courses/([^/]+)$#"
     */
    private function convertPathToRegex(string $path): string
    {
        // ubah :param jadi grup regex
        $pattern = preg_replace('/\/:([a-zA-Z0-9_]+)/', '/([^/]+)', $path);

        return '#^' . $pattern . '$#';
    }
}
