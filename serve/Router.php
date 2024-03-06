<?php 
class Router {
    private $routes = [];

    public function addRoute($pattern, $callback) {
        $this->routes[] = ['pattern' => $pattern, 'callback' => $callback];
    }

    public function route() {
        $uri = $_SERVER['REQUEST_URI'];

        $uri = strtok($uri, '?');

        foreach ($this->routes as $route) {
            $pattern = preg_replace('/<([^>]+)>/', '([^/]+)', $route['pattern']);

            $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                call_user_func_array($route['callback'], $matches);
                return;
            }
        }

        include $_SERVER['DOCUMENT_ROOT']."/.config/_404.php";
    }

    
}
?>