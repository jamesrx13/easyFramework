<?php

namespace core\utils;

class Utils
{
    public static function is_local()
    {
        return $_SERVER['HTTP_HOST'] == "localhost" ? true : false;
    }
}
