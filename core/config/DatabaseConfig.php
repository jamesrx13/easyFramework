<?php

namespace core\config;

use core\utils\Utils;

class DatabaseConfig
{
    public static function getLocalConfig()
    {
        return [
            "host" => Utils::getEnv('HOST'),
            "nameDB" => Utils::getEnv('DB_NAME'),
            "userDB" => Utils::getEnv('USER_NAME'),
            "passDB" => Utils::getEnv('PASSWORD'),
            "driverDB" => Utils::getEnv('DRIVER'),
            "charseCodeDB" => Utils::getEnv('CHARSE_CODE'),
            "portDB" => Utils::getEnv('PORT'),
        ];
    }

    public static function getProductionConfig()
    {
        return [
            "host" => Utils::getEnv('HOST'),
            "nameDB" => Utils::getEnv('DB_NAME'),
            "userDB" => Utils::getEnv('USER_NAME'),
            "passDB" => Utils::getEnv('PASSWORD'),
            "driverDB" => Utils::getEnv('DRIVER'),
            "charseCodeDB" => Utils::getEnv('CHARSE_CODE'),
            "portDB" => Utils::getEnv('PORT'),
        ];
    }
}