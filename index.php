<?php

include "./core/main/frameworkMainCore.php";

$application = new ApplicationClass();

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