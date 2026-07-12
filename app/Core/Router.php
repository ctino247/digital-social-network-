<?php

namespace App\Core;

class Router
{
    protected array $routes = [];
    public Request $request;
    public Response $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get(string $path, $callback, array $middlewares = []): void
    {
        $this->routes['GET'][$this->convertToRegex($path)] = [
            'callback'    => $callback,
            'middlewares' => $middlewares,
            'original'    => $path
        ];
    }

    public function post(string $path, $callback, array $middlewares = []): void
    {
        $this->routes['POST'][$this->convertToRegex($path)] = [
            'callback'    => $callback,
            'middlewares' => $middlewares,
            'original'    => $path
        ];
    }

    protected function convertToRegex(string $path): string
    {
        // Convert routes like /profile/{username} or /product/{slug} to regex
        // replacing {param} with (?P<param>[a-zA-Z0-9_\-\.\@]+)
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_\-\.\@]+)', $path);
        return '#^' . $regex . '$#';
    }

    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        $routesForMethod = $this->routes[$method] ?? [];

        foreach ($routesForMethod as $regex => $routeInfo) {
            if (preg_match($regex, $path, $matches)) {
                // Filter out non-string keys from matches
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run Middlewares
                foreach ($routeInfo['middlewares'] as $middlewareClass) {
                    /** @var Middleware $middleware */
                    $middleware = new $middlewareClass();
                    $middleware->execute($this->request, $this->response);
                }

                $callback = $routeInfo['callback'];

                // Handle string callbacks (e.g. "HomeController@index")
                if (is_string($callback)) {
                    $parts = explode('@', $callback);
                    if (count($parts) === 2) {
                        $controllerClass = "\\App\\Controllers\\" . $parts[0];
                        $action = $parts[1];

                        if (class_exists($controllerClass)) {
                            $controller = new $controllerClass($this->request, $this->response);
                            if (method_exists($controller, $action)) {
                                return call_user_func_array([$controller, $action], $params);
                            }
                        }
                    }
                }

                // Handle closures/callable
                if (is_callable($callback)) {
                    return call_user_func_array($callback, $params);
                }

                $this->response->setStatusCode(500);
                echo "Invalid Route Callback.";
                return;
            }
        }

        // 404 Not Found
        $this->response->setStatusCode(404);
        $this->render404();
    }

    protected function render404(): void
    {
        // Try rendering view if exists, otherwise fallback to standard HTML
        $custom404 = ROOT_PATH . '/app/Views/errors/404.php';
        if (file_exists($custom404)) {
            include_once $custom404;
        } else {
            echo "<!DOCTYPE html><html class='dark'><head><title>Page Not Found</title><script src='https://cdn.tailwindcss.com'></script></head>";
            echo "<body class='bg-[#131315] text-[#e4e2e4] flex flex-col justify-center items-center h-screen font-sans'>";
            echo "<h1 class='text-9xl font-bold text-[#c0c1ff]'>404</h1>";
            echo "<p class='text-xl mt-4 text-[#908fa0]'>The page you are looking for does not exist on Mimshack.</p>";
            echo "<a href='/' class='mt-6 px-6 py-3 bg-[#c0c1ff] text-[#1000a9] font-semibold rounded-full hover:opacity-90 transition'>Go Home</a>";
            echo "</body></html>";
        }
    }
}
