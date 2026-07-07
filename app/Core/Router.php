<?php
/**
 * app/Core/Router.php
 * -------------------
 * Router แบบเบา: จับคู่ HTTP method + path กับ Controller@action
 * รองรับพารามิเตอร์ในเส้นทาง เช่น /news/{slug}
 */

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{method:string, pattern:string, handler:string}> */
    private array $routes = [];

    public function get(string $pattern, string $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, string $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, string $handler): void
    {
        $this->routes[] = compact('method', 'pattern', 'handler');
    }

    /**
     * รับ path (เช่น /admin/news/edit/5) แล้วหา route ที่ตรง
     */
    public function dispatch(string $method, string $uri): void
    {
        $path = '/' . trim(parse_url($uri, PHP_URL_PATH) ?: '/', '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            // แปลง {param} เป็น regex group
            $regex = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $route['pattern']);
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $path, $matches)) {
                array_shift($matches); // ตัด full match
                [$controller, $action] = explode('@', $route['handler']);
                $class = 'App\\Controllers\\' . $controller;

                if (!class_exists($class)) {
                    $this->notFound();
                    return;
                }
                (new $class())->$action(...$matches);
                return;
            }
        }
        $this->notFound();
    }

    private function notFound(): void
    {
        http_response_code(404);
        require dirname(__DIR__) . '/Views/errors/404.php';
    }
}
