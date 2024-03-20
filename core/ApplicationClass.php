<?php

namespace core;

use core\database\DbConnectionMainClass;
use core\utils\Utils;
use core\config\DatabaseConfig;

class ApplicationClass
{

    private $dataBase = null;

    function __construct()
    {
        self::run();
    }

    protected function run()
    {
        if (Utils::is_local() || !Utils::isServer()) {
            $dataBase = new DbConnectionMainClass(DatabaseConfig::getLocalConfig());
        } else {
            $dataBase = new DbConnectionMainClass(DatabaseConfig::getProductionConfig());
        }
        $dataBase->initDatabase();
        $this->dataBase = $dataBase->getDataBase();
    }

    public function dataBase()
    {
        return $this->dataBase;
    }
}
