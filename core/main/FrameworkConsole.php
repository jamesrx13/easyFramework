<?php

namespace core\main;

use core\utils\Utils;

include 'FrameworkMain.php';

$mainController = new FrameworkMain();
$database = (object) $mainController->getDB();

unset($argv[0]);

class FrameworkConsole
{
    private $mainController;
    function __construct()
    {
        $this->mainController = new FrameworkMain();
    }

    public function run($argv)
    {
        $command = $argv[1];
        $param = isset($argv[2]) ? $argv[2] : null;

        if ($command == 'migrate') {
            if ($param != null && $param == 'all') {
                $this->migrateAll();
            } elseif ($param != null) {
                $this->migrate($param);
            } else {
                echo 'Error => La acción de migración requiere un parámetro, por ejemplo: migrate all o migrate %MODEL_NAME%';
                die();
            }
        } else {
            echo "Error => El comando '{$command}' no existe, por favor revisar la documentación.";
            die();
        }
    }

    private function migrateAll()
    {

        $allModels = Utils::getFiles('./api/models');

        foreach ($allModels as $model) {
            $model_name = str_replace('.php', '', $model);
            $model = '\api\models\\' . $model_name;
            if (class_exists($model)) {
                $model = new $model();
                $resp = (object) $this->mainController->executeQueryNoResponse($model->generateTableSql());
                if ($resp->status) {
                    echo "Modelo '{$model_name}' migrado con exito.\n";
                } else {
                    echo "Error => El modelo '{$model_name}' no se pudo migrar, por favor revisar.\n";
                    die();
                }
            } else {
                echo "Error => El modelo '{$model_name}' no existe, por favor revisar.\n";
                die();
            }
        }
    }

    private function migrate($model_name)
    {
        $modelFileName = ucfirst($model_name) . 'Model';

        if (file_exists('./api/models/' . $modelFileName . '.php')) {

            $model = '\api\models\\' . $modelFileName;
            if (class_exists($model)) {
                $model = new $model();
                $resp = (object) $this->mainController->executeQueryNoResponse($model->generateTableSql());
                if ($resp->status) {
                    echo "Modelo '{$model_name}' migrado con exito.";
                } else {
                    echo "Error => El modelo '{$model_name}' no se pudo migrar, por favor revisar.";
                    die();
                }
            } else {
                echo "Error => El modelo '{$model_name}' no existe, por favor revisar.";
                die();
            }
        } else {
            echo "Error => El modelo '{$model_name}' no existe, por favor revisar.";
            die();
        }
    }
}

// Validamos el estado de conexión de la DB y ejecutamos los comandos
if ($database->status) {
    $console = new FrameworkConsole();
    $console->run($argv);
} else {
    echo 'Error => Los parametros de la base de datos no son correctos.';
    die();
}
