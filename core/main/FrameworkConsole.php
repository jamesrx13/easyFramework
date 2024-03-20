<?php

namespace core\main;

use core\config\GlobalConfig;
use core\main\models\JwtModel;
use core\main\models\UserModel;
use core\utils\Utils;

include 'FrameworkMain.php';

// Variables de entorno
$_ENV = parse_ini_file('.env.local');
// Base de datos
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
        $command = isset($argv[1]) ? $argv[1] : null;
        $param = isset($argv[2]) ? $argv[2] : null;

        if ($command == null) {
            echo 'Error => Una instrucción como minimo es requerida.';
            die();
        }

        if ($command == 'migrate') {
            if ($param != null && $param == 'all') {
                $this->migrateAll();
            } elseif ($param != null) {
                $this->migrate($param);
            } else {
                echo 'Error => La acción de migración requiere un parámetro, por ejemplo: "migrate all" o "migrate %MODEL_NAME%"';
                die();
            }
        } elseif ($command == 'auth') {
            $this->auth();
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
                $model = new $model();;
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
                    echo "-- El modelo '{$model_name}' no se pudo migrar, por favor revisar --\n";
                    echo "Error => " . $resp->msg . "\n";
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

    private function auth()
    {
        $userModel = new UserModel();
        $jwtModel = new JwtModel();
        $relation = 'fk_jwt_user_id';
        $jwtModel->executeMainQuery("ALTER TABLE :table DROP FOREIGN KEY {$relation};");
        $this->mainController->executeQueryNoResponse($userModel->generateTableSql());
        $this->mainController->executeQueryNoResponse($jwtModel->generateTableSql());

        foreach (GlobalConfig::defauldAuthUsers() as $user) {
            $defauldUser = new UserModel();
            $defauldUser->load(null, $user);
            $defauldUser->save(false);
        }

        echo "Se han creado los medios de autenticación con exito.\n\nLos usuarios por defecto son:\n";
        print_r([
            [
                'user' => 'root',
                'password' => 'root123456',
            ],
            [
                'user' => 'admin',
                'password' => 'admin123456',
            ],
        ]);
        die();
    }
}

// Validamos el estado de conexión de la DB y ejecutamos los comandos
if ($database->status) {
    $console = new FrameworkConsole();
    $console->run($argv);
} else {
    echo 'Error => No fue posible establecer conexión con la base de datos.';
    die();
}