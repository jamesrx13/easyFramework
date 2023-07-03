<?php

namespace core\config;

class GlobalConfig
{
    public static function getArryHeaders()
    {
        return [
            "Content-type: application/json",
            // "Content-type: text/html",
        ];
    }
}
