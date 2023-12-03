<?php

namespace core\config;

class DatabaseConfig
{
    public static function getLocalConfig()
    {
        return [
            "host" => "localhost",
            "nameDB" => "easy_framework_db",
            "userDB" => "root",
            "passDB" => "",
            "driverDB" => "mysql",
            "charseCodeDB" => "utf8",
            "portDB" => "",
        ];
    }

    public static function getProductionConfig()
    {
        return [
            "host" => "",
            "nameDB" => "",
            "userDB" => "",
            "passDB" => "",
            "driverDB" => "",
            "charseCodeDB" => "",
            "portDB" => "",
        ];
    }
}