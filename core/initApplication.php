<?php

include "database/mainClassConnection.php";
include "utils/main.php";
include "config/databaseConfig.php";

class InitAplicationClass
{

    private $config = null;

    function __construct()
    {
        if (is_local()) {
            $this->config = DATBASE_DEFAULT_CONFIG_LOCAL;
        } else {
            $this->config = DATBASE_CONFIG_PRODUCTION;
        }
    }

    public function run()
    {
        $runDbConnection = new DbConnectionMainClass($this->config);
        return $runDbConnection->initDatabase();
    }
}
