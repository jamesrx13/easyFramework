<?php

// spl_autoload_register(function ($className) {
//     $fileName = str_replace("\\", '/', $className) . '.php';
//     if (file_exists($fileName)) {
//         echo $fileName;
//         require_once($fileName);
//     }
// });
require_once("../../../core/main/FrameworkMain.php");

use core\main\FrameworkMain;


// RUTAS
// const ROUTER = [
//     "/" => ""
// ];

FrameworkMain::genericApiResponse([
    "element" => "HOLA",
]);
