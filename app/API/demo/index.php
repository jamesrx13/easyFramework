<?php

// RUTAS
// const ROUTER = [
//     "/" => ""
// ];

include "demoClass.php";

$demoElement = new DemoClass();

FrameworkMain::genericApiResponse([
    "element" => $demoElement
]);
