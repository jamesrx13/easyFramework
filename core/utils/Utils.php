<?php

namespace core\utils;

use core\main\FrameworkMain;

class Utils
{
    public static function is_local()
    {
        if (!isset($_SERVER['HTTP_HOST'])) return false;
        return $_SERVER['HTTP_HOST'] == "localhost" ? true : false;
    }

    public static function isServer()
    {
        return isset($_SERVER['HTTP_HOST']);
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

    public static function UserNotFound()
    {
        FrameworkMain::genericApiResponse([
            "status" => false,
            "msg" => "User not found",
        ]);
    }

    public static function NotValidToken()
    {
        FrameworkMain::genericApiResponse([
            "status" => false,
            "msg" => "You need a valid token",
        ]);
    }

    public static function UserIncorrectPassword()
    {
        FrameworkMain::genericApiResponse([
            "status" => false,
            "msg" => "User or password incorrect",
        ]);
    }

    public static function validateRequestParams($requiredParams)
    {
        foreach ($requiredParams as $param) {
            if (!isset($_REQUEST[$param]) || empty($_REQUEST[$param])) {
                FrameworkMain::genericApiResponse([
                    "status" => false,
                    "msg" => "El parámetro '{$param}' es requerido.",
                ]);
                return false;
            }
        }
        return true;
    }

    public static function getRequestParams($params)
    {
        $data = [];
        foreach ($params as $param) {
            $data[$param] = $_REQUEST[$param];
        }
        return $data;
    }

    public static function getFiles($path)
    {
        $files = [];
        $dir = opendir($path);
        while ($elemento = readdir($dir)) {
            if ($elemento != "." && $elemento != "..") {
                $files[] = $elemento;
            }
        }
        return $files;
    }

    public static function getEnv(String $key = '') {
        
        if($key == ''):            
            return $_ENV;
        endif;

        if(key_exists($key, $_ENV)){
            return $_ENV[$key];
        } else {
            return false;
        }
    }

    public static function sendEmail($to, $title, $msg)
    {        
        //TODO: 
    }
}
