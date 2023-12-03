<?php
$rootUrl = $_SERVER['SCRIPT_FILENAME'];
$rootUrl = explode("/", $rootUrl);
unset($rootUrl[count($rootUrl) - 1]);
$rootUrl = implode("/", $rootUrl);

define('MAIN_URL', $rootUrl . '/');

use api\ApiRouter;
use core\main\FrameworkMain;
use core\ApplicationClass;
use core\config\GlobalConfig;

spl_autoload_register(function ($className) {
    $fileName = str_replace("\\", "/", $className) . '.php';
    if (file_exists($fileName)) {
        $fileName = MAIN_URL . $fileName;
    } elseif (file_exists("api/controllers/" . $fileName)) {
        $fileName = "api/controllers/" . $fileName;
    } elseif (file_exists("api/models/" . $fileName)) {
        $fileName = "api/models/" . $fileName;
    }
    include $fileName;
});

$application = new ApplicationClass;
$application = $application->dataBase();

extract($application);

$request_uri = $_SERVER['REQUEST_URI'];

if (strpos($request_uri, '/api/')) {
    $route = FrameworkMain::getApiRoute($request_uri);
    $apiRouter = new ApiRouter();

    if ($route->route != '' && $apiRouter->existRoute($route->route)) {

        $controller = ucfirst($route->route) . 'Controller';

        include 'api/controllers/' . $controller . '.php';

        $controller = new $controller;

        $routerOperations = $controller::routes($route->operation);

        if (FrameworkMain::validateMethod($routerOperations->method)) {
            $controller->{$routerOperations->fnt}();
        } else {
            FrameworkMain::genericApiResponse([
                "status" => false,
                "msg" => "Route not found",
            ]);
        }
    } else {
        FrameworkMain::genericApiResponse([
            "status" => false,
            "msg" => "Route not found",
        ]);
    }
} else {
    FrameworkMain::genericApiResponse([
        "dbStatus" => $status,
        "msg" => "Esta herramienta está diseñada solo para la reación de APIs :)",
        "info" => GlobalConfig::frameworkInformation(),
    ]);
}