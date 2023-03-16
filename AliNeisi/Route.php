<?php

namespace Alineisi;

use ReflectionMethod;

function routeName($name)
{
    global $names;
    if (isset($names[$name]))
        return '/' . $names[$name];
    else
        return "Route name `$name` is not defined";
}

class Route
{
    protected static $path_to_notfound_page = '';

    protected static $routes = [];

    public static function notFoundPath($path)
    {
        self::$path_to_notfound_page = $path;
    }

    public static function view($url, $view)
    {
        self::$routes[self::getRoute($url, 'get')] = ['type' => 'view', 'callback' => $view];
        return new Methods($url, 'get');
    }

    public static function get($url, $callback)
    {
        self::$routes[self::getRoute($url, 'get')] = ['type' => 'get', 'callback' => $callback];
        return new Methods($url, 'get');
    }

    public static function post($url, $callback)
    {
        self::$routes[self::getRoute($url, 'post')] = ['type' => 'post', 'callback' => $callback];
        return new Methods($url, 'post');
    }

    protected static function getRoute($url, $method)
    {
        $method = strtolower($method);
        $query_string = $_SERVER['QUERY_STRING'] ?? '';
        if ($query_string == '')
            return self::getUrl($url) . ".$method";
        return self::getUrl($url) . "?$query_string.$method";
    }

    protected static function getUrl($url)
    {
        if (strpos($url, '/') === 0) {
            return substr($url, 1, strlen($url));
        }
        return $url;
    }

    protected static function getRouteIndex($url, $method)
    {
        $method = strtolower($method);
        return self::getUrl($url) . ".$method";
    }

    public static function dispatch()
    {
        $path = self::getUrl($_SERVER['REQUEST_URI']);
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        $result = self::$routes[self::getRouteIndex($path, $method)] ?? '';
        $callback = $result['callback'] ?? '';
        $type = $result['type'] ?? '';
        switch ($type) {
            case 'post':
            case 'get':
                if (is_array($callback)) {
                    $class = $callback[0];
                    $function = $callback[1];
                    $reflectionMethod = new ReflectionMethod($class, $function);
                    if ($reflectionMethod->isStatic())
                        $result = $callback();
                    else
                        $result = (new $class)->$function();
                } else
                    $result = $callback();
                if (is_string($result)) (new self)->jsonResponse($result);
                break;
            case
            'view':
                self::getView($callback);
                break;
            default:
                self::abort();
        }
    }

    protected function jsonResponse($data) {
        header('Content-type: application/json');
        echo json_encode($data);
    }

    protected static function abort()
    {
        http_response_code(404);
        $path = self::$path_to_notfound_page;
        if ($path == '') (new self)->jsonResponse('Not Found.');
        else self::getView($path);
        die;
    }

    protected static function getView($path)
    {
        if (strpos($path, '.') !== false) {
            $explode = explode('.', $path);
            $extension = $explode[count($explode) - 1];
            if ($extension == '') {
                $path = trim($path, '.');
                require_once "$path.php";
            } else require_once $path;
        } else
            require_once "$path.php";
    }

}

class Methods
{
    protected $url = '';
    protected $method = '';

    public function __construct($url, $method)
    {
        $this->url = $url;
        $this->method = $method;
    }

    public function name($name)
    {
        global $names;
        $names[$name] = $this->getUrl($this->url);
    }

    protected function getUrl($url)
    {
        if (strpos($url, '/') === 0) {
            return substr($url, 1, strlen($url));
        }
        return $url;
    }
}
