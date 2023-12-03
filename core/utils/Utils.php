<?php

namespace core\utils;

use core\main\FrameworkMain;

class Utils
{
    public static function is_local()
    {
        return $_SERVER['HTTP_HOST'] == "localhost" ? true : false;
    }

    public static function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public static function getServerMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function OnlyGetRequest()
    {
        return $_SERVER['REQUEST_METHOD'] == "GET" ? true : false;
    }

    public static function OnlyPostRequest()
    {
        return $_SERVER['REQUEST_METHOD'] == "POST" ? true : false;
    }

    public static function OnlyPutRequest()
    {
        return $_SERVER['REQUEST_METHOD'] == "PUT" ? true : false;
    }

    public static function OnlyDeleteRequest()
    {
        return $_SERVER['REQUEST_METHOD'] == "DELETE" ? true : false;
    }

    public static function RouteNotFound()
    {
        FrameworkMain::genericApiResponse([
            "status" => false,
            "msg" => "Route not found",
        ]);
    }
}
