<?php
// Configuración de la base de datos en local / desarrollo
const DATBASE_DEFAULT_CONFIG_LOCAL = [
    "host" => "localhost",
    "nameDB" => "easy_framework_db",
    "userDB" => "root",
    "passDB" => "",
    "driverDB" => "mysql",
    "charseCodeDB" => "utf8",
    "portDB" => "",
];

// Configuración de la base de datos en producción
const DATBASE_CONFIG_PRODUCTION = [
    "host" => "",
    "nameDB" => "",
    "userDB" => "",
    "passDB" => "",
    "driverDB" => "",
    "charseCodeDB" => "",
    "portDB" => "",
];
