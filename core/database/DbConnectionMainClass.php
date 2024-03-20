<?php


namespace core\database;

use PDO;

class DbConnectionMainClass
{

    private string $host = "";
    private string $nameDB = "";
    private string $userDB = "";
    private string $passDB = "";
    private string $driverDB = "";
    private $newControllerPDO = null;
    private string $charseCodeDB = "";
    private string $portDB = "";

    function __construct($objParameters)
    {
        if (!empty($objParameters) && is_array($objParameters)) {
            $objParameters = (object) $objParameters;
            $this->host = $objParameters->host;
            $this->nameDB = $objParameters->nameDB;
            $this->userDB = $objParameters->userDB;
            $this->passDB = $objParameters->passDB;
            $this->charseCodeDB = $objParameters->charseCodeDB;
            $this->driverDB = $objParameters->driverDB;
            $this->portDB = $objParameters->portDB;
        } else {
            return [
                "status" => false,
                "msg" => "Unable to use assigned configuration",
            ];
            die();
        }
    }

    protected function validateConnectionOnDataBase()
    {
        try {
            $connectionString = $this->driverDB . ":host=" . $this->host . ";dbname=" . $this->nameDB;
            $PDOconnection = new PDO($connectionString, $this->userDB, $this->passDB);
            $this->newControllerPDO = $PDOconnection;
            return [
                "status" => true,
                "msg" => "Successful connection to the database",
            ];
        } catch (\Throwable $th) {
            return [
                "status" => false,
                "msg" => $th->getMessage(),
            ];
        }
    }

    public function initDatabase()
    {
        return self::validateConnectionOnDataBase();
    }

    public function getDataBase()
    {
        return [
            "status" => is_null($this->newControllerPDO) ? false : true,
            "dataBase" => $this->newControllerPDO,
        ];
    }
}
