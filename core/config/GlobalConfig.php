<?php

namespace core\config;

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
                'projectName' => 'easyFramework',
                'projectAuthor' => 'James Rudas',
                'projectVersion' => '1.0.0',
            ],
        ];
    }
}
