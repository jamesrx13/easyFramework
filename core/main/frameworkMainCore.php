<?php

include "core/config/globalsConfig.php";
include "core/initApplication.php";


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
        foreach (API_HEADERS as $header) {
            header($header);
        }
    }

    public function getAllData($table)
    {
        if ($table != "") {
            $sql = "SELECT * FROM {$table}";
            self::executeQuery($sql);
        } else {
            self::genericApiResponse([
                "status" => false,
                "msg" => "Table is required",
            ]);
        }
    }

    public function getAllDataBy($table, $whereCondition)
    {
        if ($table != "" && $whereCondition != "") {
            $sql = "SELECT * FROM {$table} WHERE {$whereCondition}";
            self::executeQuery($sql);
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
}
