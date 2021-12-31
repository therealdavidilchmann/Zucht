<?php

    require_once 'Path.php';


    class Routes {
        public $routes;


        public function __construct()
        {
            $this->routes = array();
        }
        
        public function both($route, $activator)
        {
            $this->get($route, $activator);
            $this->post($route, $activator);
        }

        public function get($route, $activator)
        {
            $newRoute = new Route($route, 'GET', $activator);
            array_push($this->routes, $newRoute);
        }

        public function post($route, $activator)
        {
            $newRoute = new Route($route, 'POST', $activator);
            array_push($this->routes, $newRoute);
        }

        public function listen($application)
        {
            $path = Path::getPath();
            if ($path == "") {
                $path = "/";
            }

            for ($i=0; $i < count($this->routes); $i++) {
                if ($this->routes[$i]->route == $path) {
                    if ($_SERVER['REQUEST_METHOD'] == $this->routes[$i]->method) {
                        if (is_string($this->routes[$i]->activator)) {
                            $activator = explode('@', $this->routes[$i]->activator);
                            if (file_exists("controllers/$activator[0].php")) {
                                require_once "controllers/$activator[0].php";
                                $class = new $activator[0]();
                                $method = $activator[1];
                                $class->$method($application);
                            } else {
                                Response::error(500, "Controller $activator[0] doesn't exist.");
                            }
                        } else {
                            $method = $this->routes[$i]->activator;
                            $method($application);
                        }
                        return;
                    }
                }
            }
            Response::error(404, 'Page not found');
        }
    }

    class Route {
        public $route;
        public $method;
        public $activator;

        public function __construct($route, $method, $activator)
        {
            $this->route = $route;
            $this->method = $method;
            $this->activator = $activator;
        }
    }

    

?>
