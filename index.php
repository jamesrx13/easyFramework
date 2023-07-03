<?php

use core\main\FrameworkMain;
use core\ApplicationClass;

spl_autoload_register(function ($className) {
    $fileName = str_replace("\\", "/", $className) . '.php';
    if (file_exists($fileName)) {
        require_once($fileName);
    }
});

$application = new ApplicationClass;
$application = $application->dataBase();

extract($application);

if ($status) {
    FrameworkMain::genericApiResponse([
        "status" => true,
        "msg" => "Welcome!"
    ]);
} else {
    FrameworkMain::genericApiResponse([
        "status" => false,
        "msg" => "Failed to connect to the database",
    ]);
}