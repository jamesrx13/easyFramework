<?php

namespace core\config;

use core\main\FrameworkMain;
use core\utils\Utils;

class GlobalConfig
{
    public static function getArryHeaders()
    {
        return [
            "Content-type: application/json",
            "Access-Control-Allow-Origin: *"
        ];
    }

    public static function frameworkInformation()
    {
        return [
            'framework' => [
                'name' => 'easyFramework',
                'author' => 'James Rudas',
                'version' => '1.0.0',
                'phpVersion' => PHP_VERSION,
                'ipRequest' => Utils::getIp(),
            ],
            'project' => [
                'projectName' => Utils::getEnv('APP_NAME'),
                'projectAuthor' => Utils::getEnv('APP_AUTHOR'),
                'projectVersion' => Utils::getEnv('APP_VERSION'),
            ],
        ];
    }

    public static function defauldAuthUsers()
    {
        return [
            [
                'user_name' => 'root',
                'email' => 'root@email.com',
                'name' => 'Root',
                'last_name' => 'User',
                'password' => FrameworkMain::hashPassword('root123456'),
            ],
            [
                'user_name' => 'admin',
                'email' => 'admin@email.com',
                'name' => 'Admin',
                'last_name' => 'User',
                'password' => FrameworkMain::hashPassword('admin123456'),
            ],
        ];
    }
}
