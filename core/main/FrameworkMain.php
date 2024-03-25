<?php

namespace core\main;

use core\config\GlobalConfig as gloablConfig;
use core\ApplicationClass;
use core\main\models\JwtModel;
use core\main\models\UserModel;
use core\utils\Utils;
use PDO;

spl_autoload_register(function ($className) {
    $fileName = str_replace("\\", '/', $className) . '.php';
    if (file_exists($fileName)) {
        require_once($fileName);
    }
});

class FrameworkMain
{

    protected $db;

    const IMAGES_FORMAT = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
    ];

    const VIDEO_FORMATS = [
        'mp4'  => 'video/mp4',
        'avi'  => 'video/x-msvideo',
        'mov'  => 'video/quicktime',
    ];

    const DOCUMENT_FORMATS = [
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'pdf'  => 'application/pdf',
    ];

    const ALL_FILE_FORMATS = ['*' => '*'];
    

    function __construct()
    {
        $db = new ApplicationClass();
        $this->db = $db->dataBase();
    }


    public static function genericApiResponse($data = [])
    {

        self::setApiHeaders();

        if (!empty($data)) {
            echo json_encode($data);
        } else {
            echo json_encode([
                "status" => false,
                "msg" => "Empty data"
            ]);
        }
        die();
    }

    protected static function setApiHeaders()
    {
        foreach (gloablConfig::getArryHeaders() as $header) {
            header($header);
        }
    }

    public function getAllData($table, $autoResponse = true, $pagination = false)
    {
        if ($table != "") {
            $sql = "SELECT * FROM {$table}";

            if ($autoResponse) {
                self::executeQuery($sql, [], $pagination);
            } else {
                return self::executeQueryNoResponse($sql, [], $pagination);
            }
        } else {
            self::genericApiResponse([
                "status" => false,
                "msg" => "Table is required",
            ]);
        }
    }

    public function getAllDataBy($table, $whereCondition, $autoResponse = true, $pagination = false, $data = [])
    {
        if ($table != "" && $whereCondition != "") {
            $sql = "SELECT * FROM {$table} WHERE {$whereCondition}";
            if ($autoResponse) {
                self::executeQuery($sql, $data, $pagination);
            } else {
                return self::executeQueryNoResponse($sql, $data, $pagination);
            }
        } else {
            self::genericApiResponse([
                "status" => false,
                "msg" => "Table and condition are required",
            ]);
        }
    }

    public function executeQuery($sql, $data = [], $pagination = false)
    {
        FrameworkMain::genericApiResponse(self::executeQueryNoResponse($sql, $data, $pagination));
    }

    public function executeQueryNoResponse($sql, $data = [], $pagination = false)
    {
        extract($this->db);

        if ($status) {
            try {

                if($pagination){
                
                    $request = (object) Utils::getRequestParams(['page', 'countData']);

                    $page = isset($request->page) && $request->page != 0 ? $request->page : 1;
                    $countData = isset($request->countData) && $request->countData != 0 ? $request->countData : 10;
    
                    $page = (int) $page;
                    $countData = (int) $countData;    
                    
                    $compliteData = (object) self::executeQueryNoResponse(explode('WHERE',$sql)[0]);
                    
                    $hasNexPage = $compliteData->totalElements > ($page * $countData);
                    $hasPrePage = $page > 1;
                    $totalPages = ceil($compliteData->totalElements / $countData);

                    $offset = ($page - 1) * $countData;
    
                    $sql .= " LIMIT {$countData} OFFSET {$offset}";
                }

                $prepareQuery = $dataBase->prepare($sql);

                if ($prepareQuery->execute($data)) {
                    $queryResult = $prepareQuery->fetchAll(PDO::FETCH_ASSOC);
                    if($pagination){
                        $response = [
                            "status" => true,
                            "totalElements" => count($queryResult),
                            "totalRecords" => $compliteData->totalElements,
                            "totalPages" => $totalPages,
                        ];
                        
                        if($hasNexPage){
                            $response['nexPage'] = $page + 1;
                        }

                        if($hasPrePage){
                            $response['prePage'] = $page - 1;
                        }

                        $response['data'] = $queryResult;

                        return $response;
                    } else {
                        return [
                            "status" => true,
                            "totalElements" => count($queryResult),
                            "data" => $queryResult,
                        ];                        
                    }

                } else {
                    return [
                        "status" => false,
                        "msg" => $prepareQuery->errorInfo()[2]
                    ];
                }
            } catch (\Throwable $th) {
                return [
                    "status" => false,
                    "msg" => $th->getMessage(),
                ];
            }
        } else {
            return [
                "status" => false,
                "msg" => "Database error",
            ];
        }
    }

    public static function getApiRoute($request_uri, $isUser = false)
    {
        $request_uri = explode("/api/", $request_uri);

        if ($isUser) {
            $request_uri = explode("/user/", $request_uri[0]);
        }

        if (count($request_uri) > 1) {

            if ($request_uri[1] != '') {
                $request_uri = explode("/", $request_uri[1]);
                if (count($request_uri) > 0) {

                    if ($isUser) {
                        return $request_uri[0];
                    }

                    return (object) [
                        'route' => $request_uri[0],
                        'operation' => $request_uri[1],
                    ];
                } else {
                    return (object) [
                        'route' => '',
                        'operation' => '',
                    ];
                }
            } else {
                return (object) [
                    'route' => '',
                    'operation' => '',
                ];
            }
        } else {
            return (object) [
                'route' => '',
                'operation' => '',
            ];
        }
    }

    public static function validateMethod($method)
    {
        $method = strtolower($method);

        if ($method == 'get') {
            return Utils::OnlyGetRequest();
        } elseif ($method == 'post') {
            return Utils::OnlyPostRequest();
        } elseif ($method == 'put') {
            return Utils::OnlyPutRequest();
        } elseif ($method == 'delete') {
            return Utils::OnlyDeleteRequest();
        } else {
            return false;
        }
    }

    public function getDB()
    {
        return $this->db;
    }

    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public static function encrypt_decrypt($action, $string): string
    {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', Utils::getEnv('SECRET_KEY'));
        $iv = substr(hash('sha256', Utils::getEnv('SECRET_IV')), 0, 16);
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }

    public static function getRequestHeader($header)
    {
        $header = 'HTTP_' . str_replace('-', '_', strtoupper($header));
        return isset($_SERVER[$header]) ? $_SERVER[$header] : false;
    }

    public static function getUser(){
        $jwtModel = new JwtModel();
        $jwtModel->token = FrameworkMain::getRequestHeader(Utils::getEnv('HEADER_TOKEN'));
        return new UserModel($jwtModel->getUserId());
    }
}
