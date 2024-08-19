<?php
// Ruta inicial
$rootUrl = $_SERVER['SCRIPT_FILENAME'];
$rootUrl = explode("/", $rootUrl);
unset($rootUrl[count($rootUrl) - 1]);
$rootUrl = implode("/", $rootUrl);
define('MAIN_URL', $rootUrl . '/');

use api\ApiRouter;
use core\main\FrameworkMain;
use core\ApplicationClass;
use core\config\GlobalConfig;
use core\main\controllers\AuthController;
use core\utils\Utils;

try {

    spl_autoload_register(function ($className) {
        $fileName = str_replace("\\", "/", $className) . '.php';
        if (file_exists($fileName)) {
            $fileName = MAIN_URL . $fileName;
        }
        include $fileName;
    });

    // Variables de entorno
    $_ENV = Utils::is_local() ? parse_ini_file('.env.local') : parse_ini_file('.env');

    $application = new ApplicationClass;
    $application = $application->dataBase();

    extract($application);

    $request_uri = $_SERVER['REQUEST_URI'];

    if (strpos($request_uri, 'api/') && $status) {

        $request_uri = substr($request_uri, -1) === '/' ? $request_uri : $request_uri . '/';
        $route = FrameworkMain::getApiRoute($request_uri);
        $apiRouter = new ApiRouter();

        if ($route->route != '' && $apiRouter->existRoute($route->route)) {

            // Middlewares
            $middlewares = $apiRouter->getMiddlewares($route->route);

            foreach ($middlewares as $middleware) {
                $middleware();
            }

            // Funciones de la ruta
            $controller = ucfirst($route->route) . 'Controller';

            if (file_exists('api/controllers/' . $controller . '.php')) {
                include 'api/controllers/' . $controller . '.php';

                $controller = new $controller;

                $route->operation = explode('?', $route->operation);

                if (count($route->operation) > 1) {
                    $route->operation = $route->operation[0];
                } else {
                    $route->operation = implode('?', $route->operation);
                }

                $routerOperations = $controller::routes($route->operation);

                if (isset($routerOperations->auth) && $routerOperations->auth) {
                    if (!AuthController::isValidToken()) {
                        Utils::NotValidToken();
                        die();
                    }

                    if (isset($routerOperations->roles)) {
                        AuthController::validateRol($routerOperations->roles);
                    }
                }


                if (isset($routerOperations->method) && isset($routerOperations->fnt)) {
                    if (FrameworkMain::validateMethod($routerOperations->method)) {
                        $controller->{$routerOperations->fnt}();
                    } else {
                        Utils::RouteNotFound();
                    }
                } else {
                    Utils::RouteNotFound();
                }
            } else {
                Utils::RouteNotFound();
            }
        } else {
            Utils::RouteNotFound();
        }
    } elseif (strpos($request_uri, 'user/') && $status) {
        $request_uri = substr($request_uri, -1) === '/' ? $request_uri : $request_uri . '/';
        $authFnt = AuthController::routes(FrameworkMain::getApiRoute($request_uri, true));

        if (FrameworkMain::validateMethod($authFnt->method)) {
            if (isset($authFnt->auth)) {
                if ($authFnt->auth) {
                    if (AuthController::isValidToken()) {

                        if (isset($authFnt->roles)) {
                            AuthController::validateRol($authFnt->roles);
                        }

                        AuthController::{$authFnt->fnt}();
                    } else {
                        Utils::NotValidToken();
                    }
                } else {
                    AuthController::{$authFnt->fnt}();
                }
            } else {
                AuthController::{$authFnt->fnt}();
            }
        } else {
            Utils::RouteNotFound();
        }
    } else {
        FrameworkMain::genericApiResponse([
            "dbStatus" => $status,
            "msg" => "Esta herramienta está diseñada solo para la reación de APIs :)",
            "info" => GlobalConfig::frameworkInformation(),
        ]);
    }
} catch (\Throwable $th) {
    header("Content-Type: application/json");
    echo json_encode([
        "status" => false,
        "msg" => $th->getMessage()
    ]);
}
