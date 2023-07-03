<?php

include "database/mainClassConnection.php";
include "utils/main.php";
include "config/databaseConfig.php";

class ApplicationClass
{

    private $dataBase = null;

    function __construct()
    {
        self::run();
    }

    protected function run()
    {
        if (is_local()) {
            $dataBase = new DbConnectionMainClass(DATBASE_DEFAULT_CONFIG_LOCAL);
        } else {
            $dataBase = new DbConnectionMainClass(DATBASE_CONFIG_PRODUCTION);
        }
        $dataBase->initDatabase();
        $this->dataBase = $dataBase->getDataBase();
    }

    public function dataBase()
    {
        return $this->dataBase;
    }
}