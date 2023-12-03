<?php

namespace core\main;

use core\config\GlobalConfig as gloablConfig;
use core\ApplicationClass;
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
    }

    protected static function setApiHeaders()
    {
        foreach (gloablConfig::getArryHeaders() as $header) {
            header($header);
        }
    }

    public function getAllData($table, $autoResponse = true)
    {
        if ($table != "") {
            $sql = "SELECT * FROM {$table}";
            if ($autoResponse) {
                self::executeQuery($sql);
            } else {
                return self::executeQueryNoResponse($sql);
            }
        } else {
            self::genericApiResponse([
                "status" => false,
                "msg" => "Table is required",
            ]);
        }
    }

    public function getAllDataBy($table, $whereCondition, $autoResponse = true)
    {
        if ($table != "" && $whereCondition != "") {
            $sql = "SELECT * FROM {$table} WHERE {$whereCondition}";
            if ($autoResponse) {
                self::executeQuery($sql);
            } else {
                return self::executeQueryNoResponse($sql);
            }
        } else {
            self::genericApiResponse([
                "status" => false,
                "msg" => "Table and condition are required",
            ]);
        }
    }

    public function executeQuery($sql, $data = [])
    {
        extract($this->db);

        if ($status) {
            try {
                $prepareQuery = $dataBase->prepare($sql);
                if ($prepareQuery->execute($data)) {
                    $queryResult = $prepareQuery->fetchAll(PDO::FETCH_ASSOC);
                    self::genericApiResponse([
                        "status" => true,
                        "totalElements" => count($queryResult),
                        "data" => $queryResult,
                    ]);
                } else {
                    self::genericApiResponse([
                        "status" => false,
                        "msg" => "Wrong request"
                    ]);
                }
            } catch (\Throwable $th) {
                self::genericApiResponse([
                    "status" => false,
                    "msg" => $th->getMessage(),
                ]);
            }
        } else {
            self::genericApiResponse([
                "status" => false,
                "msg" => "Database error",
            ]);
        }
    }

    public function executeQueryNoResponse($sql, $data = [])
    {
        extract($this->db);

        if ($status) {
            try {
                $prepareQuery = $dataBase->prepare($sql);

                if ($prepareQuery->execute($data)) {
                    $queryResult = $prepareQuery->fetchAll(PDO::FETCH_ASSOC);
                    return [
                        "status" => true,
                        "totalElements" => count($queryResult),
                        "data" => $queryResult,
                    ];
                } else {
                    return [
                        "status" => false,
                        "msg" => "Wrong request"
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

    public static function getApiRoute($request_uri)
    {
        $request_uri =  explode("/api/", $request_uri);

        if (count($request_uri) > 0) {

            if ($request_uri[1] != '') {
                $request_uri = explode("/", $request_uri[1]);
                if (count($request_uri) > 0) {
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
}