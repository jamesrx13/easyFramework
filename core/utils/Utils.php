<?php

namespace core\utils;

use core\main\FrameworkMain;
use core\main\models\UserModel;
use finfo;
use RuntimeException;

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

    public static function userNotActivate($staus)
    {
        $msg = '';

        if ($staus == UserModel::STATUS_DESACTIVATE) {
            $msg = "Your user is deactivated";
        } elseif ($staus == UserModel::STATUS_BLOCK) {
            $msg = "Your user is blocked";
        }

        FrameworkMain::genericApiResponse([
            "status" => false,
            "msg" => $msg,
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

    public static function NoPermission()
    {
        FrameworkMain::genericApiResponse([
            "status" => false,
            "msg" => "You do not have permission to access this route",
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

    public static function validateRequestFiles($requireFiles, $endProcess = true)
    {
        foreach ($requireFiles as $param) {
            if (!isset($_FILES[$param]) || empty($_FILES[$param])) {
                if ($endProcess) {
                    FrameworkMain::genericApiResponse([
                        "status" => false,
                        "msg" => "El archivo '{$param}' es requerido.",
                    ]);
                }
                return false;
            }
        }
        return true;
    }

    public static function getRequestParams($params)
    {
        $data = [];
        foreach ($params as $param) {
            if (isset($_REQUEST[$param])) {
                $data[$param] = $_REQUEST[$param];
            }
        }
        return $data;
    }

    public static function getRequestFiles($files)
    {
        $data = [];
        foreach ($files as $param) {
            if (isset($_FILES[$param])) {
                $data[$param] = $_FILES[$param];
                $data[$param]['key'] = $param;
            }
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

    public static function getEnv(String $key = '')
    {

        if ($key == '') :
            return $_ENV;
        endif;

        if (key_exists($key, $_ENV)) {
            return $_ENV[$key];
        } else {
            return false;
        }
    }

    public static function uploadAccess(array $allFiles, array $format, String $folder, String $name = null)
    {
        try {

            $imagesUploads = [];

            if ($folder != '/') {
                $folder = trim($folder, '/');
            }

            // Validar que exista la carpeta de destino en API
            if (!is_dir('/api/uploads')) {
                @mkdir('./api/uploads/', 0777);
            }

            foreach ($allFiles as $currentFile) {
                $fileObj = (object) $currentFile;

                // Validación de posibles errores al subir
                if (!isset($_FILES[$fileObj->key]['error']) || ($_FILES[$fileObj->key]['error'] >= 1)) {
                    throw new RuntimeException('Invalid parameters.');
                }

                // Validación del peso del archivo
                // if ($_FILES[$fileObj->key]['size'] > FILE_MAX_SIZE) {
                //     throw new RuntimeException('Exceeded filesize limit.');
                // }

                // Validación del formato del archivo
                if ($format != FrameworkMain::ALL_FILE_FORMATS) {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    if (false === $ext = array_search(
                        $finfo->file($_FILES[$fileObj->key]['tmp_name']),
                        $format,
                        true
                    )) {
                        throw new RuntimeException('Invalid file format.');
                    }
                } else {
                    $ext = $fileObj;
                    $ext = explode('.', $fileObj->name);
                    $ext = $ext[count($ext) - 1];
                }

                // Validación de la carpeta destino
                if ($folder == '') {
                    throw new RuntimeException('Destination folder not specified');
                }

                // Crear la carpeta destino si esta no existe
                if (!is_dir($folder)) {
                    $folder = trim($folder, '/');
                    $folder = explode('/', $folder);

                    if (count($folder) > 1) {
                        $initPath = '';
                        foreach ($folder as $fold) {
                            $initPath .= $fold . '/';
                            @mkdir('./api/uploads/' . $initPath, 0777);
                        }
                        $folder = trim($initPath, '/');
                    } else {
                        $folder = $folder[0];
                        @mkdir('./api/uploads/' . $folder, 0777);
                    }
                }

                // Mover el archivo al servidor 
                $newFileName = $name != null && $name != '' ? $name : uniqid();
                if ($folder != '/') {
                    $folder = '/' . $folder . '/';
                }
                $path = sprintf("/api/uploads{$folder}%s.%s", $newFileName, $ext);

                if (!move_uploaded_file($_FILES[$fileObj->key]['tmp_name'],  '.' . $path)) {
                    throw new RuntimeException('Failed to move uploaded file.');
                }

                $imagesUploads[] = $path;
            }

            return $imagesUploads;
        } catch (\Throwable $th) {
            FrameworkMain::genericApiResponse([
                'status' => false,
                'msg' => $th->getMessage(),
            ]);
        }
    }

    public static function getMainUrl($usingBaseRoute = false)
    {
        // Obtener la información del servidor
        $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
        $serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
        $serverPort = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '';
        $requestUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : './';

        // Construir la URL base
        $baseUrl = $scheme . '://' . $serverName;

        // Agregar el puerto si es diferente del estándar
        if (($scheme == 'http' && $serverPort != '80') || ($scheme == 'https' && $serverPort != '443')) {
            $baseUrl .= ':' . $serverPort;
        }

        // Agregar la ruta base del proyecto
        if ($usingBaseRoute) {
            $baseUrl .=  '/' . explode('/', trim($requestUrl, '/'))[0];
        }

        return $baseUrl;
    }

    public static function sendEmail($to, $title, $msg, $isHTML = false)
    {
        $headers = array(
            'From' => self::getEnv('APP_EMAIL'),
            'Reply-To' => self::getEnv('APP_EMAIL'),
            'X-Mailer' => 'PHP/' . phpversion()
        );

        if ($isHTML) {
            $headers['Content-type'] = 'text/html; charset=iso-8859-1';
        }

        if (mail($to, $title, $msg, $headers)) {
            return true;
        } else {
            return false;
        }
    }

    public static function getFileName(String $fileName)
    {
        $filename = explode('/', $fileName);
        $filename = $filename[count($filename) - 1];
        $filename = explode('.', $filename);
        return $filename[0];
    }

    public static function cURL(
        String $url,
        String $method = 'GET',
        array $formData = null,
        array $headers = null,
        bool $responseAsObj = true,
        int $timeout = 5,
        bool $isLocal = false
    ) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        if ($isLocal) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        // Agregar los headers (son recibidos como array asociativo)
        if ($headers != null && count($headers) > 0) {
            $strHeaders = [];
            foreach ($headers as $key => $value) {
                $strHeaders[] = $key . ': ' . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $strHeaders);
        }

        // Agregar datos del formulario
        if (in_array(strtoupper($method), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            if ($formData === null) {
                return [
                    'status' => false,
                    'msg' => 'Datos del formulario no propocionados.',
                ];
            }
            $fields_string = http_build_query($formData);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        }

        $data = curl_exec($ch);

        // Manejo de errores de cURL
        if ($data === false) {
            return [
                'status' => false,
                'msg' => 'Error en la solicitud cURL: ' . curl_error($ch),
            ];
        }

        curl_close($ch);

        // Decodificación del JSON si se solicita como objeto
        if ($responseAsObj) {
            $decodedData = json_decode($data);
            if ($decodedData === null) {
                return [
                    'status' => false,
                    'msg' => 'Error al decodificar el JSON de la respuesta',
                ];
            }
            return $decodedData;
        }

        return $data;
    }
}
