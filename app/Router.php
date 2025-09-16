<?php
namespace App;

final class Router {
    private array $routes = ['GET'=>[], 'POST'=>[]];

    public function get(string $path, callable|array $h): void  { $this->routes['GET'][]  = [$this->compile($path), $h]; }
    public function post(string $path, callable|array $h): void { $this->routes['POST'][] = [$this->compile($path), $h]; }

    private function compile(string $path): array {
        $names = [];
        $regex = preg_replace_callback('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', function($m) use (&$names){
            $names[] = $m[1]; return '([^/]+)';
        }, $path);
        return ['#^'.$regex.'$#', $names];
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        foreach ($this->routes[$method] ?? [] as [$compiled, $handler]) {
            [$regex, $names] = $compiled;
            if (preg_match($regex, $path, $m)) {
                array_shift($m);
                $params = [];
                foreach ($names as $i=>$n) $params[$n] = $m[$i] ?? null;
                if (is_array($handler)) { [$cls,$fn] = $handler; (new $cls)->{$fn}($params); }
                else { $handler($params); }
                return;
            }
        }
        http_response_code(404); echo "404 Not Found";
    }
}
