<?php

namespace core\main;

use core\config\GlobalConfig;
use core\main\models\JwtModel;
use core\main\models\UserModel;
use core\utils\Utils;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

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
        $param2 = isset($argv[3]) ? $argv[3] : null;

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
        } elseif ($command == 'delete') {
            if($param){
                $this->delete($param, $param2);
            } else {
                echo "Error => La función 'delete' requiere un parámetro, por ejemplo: 'delete %MODEL_NAME%'";
                die;
            }

        } else {
            echo "Error => El comando '{$command}' no existe, por favor revisar la documentación.";
            die();
        }
    }

    private function getModelsInDirectory($directory)
    {
        $allModels = [];

        $dir = new RecursiveDirectoryIterator($directory);
        $iter = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);
        $files = new RegexIterator($iter, '/^.+\.php$/i');
        foreach ($files as $file) {
            $file = $file->getPathname();
            $file = explode('/', $file);
            $file = $file[count($file) - 1];
            $file = explode('\\', $file);
            $allModels[] = $file[count($file) - 1];
        }

        return $allModels;
    }

    private function migrateAll()
    {
        $mainPath = './api/models/';
        $allModels = Utils::getFiles($mainPath);

        foreach ($allModels as $i => $value) {
            if (!strpos($value, '.php')) {
                unset($allModels[$i]);
                $allModels = array_merge($allModels, $this->getModelsInDirectory($mainPath . $value));
            }
        }

        foreach ($allModels as $model) {
            $this->migrate($model, true);
        }
    }

    private function migrate($model_name, $isAll = false)
    {

        if (!$isAll) {
            $model_name = ucfirst($model_name) . 'Model';
        }

        $relativeData = $this->mainController->getRelativeApiPathFile($model_name);

        if ($relativeData && file_exists($relativeData->path)) {

            $model = $relativeData->namespace;

            if (class_exists($model)) {
                $model = new $model();
                $resp = (object) $this->mainController->executeQueryNoResponse($model->generateTableSql());
                if ($resp->status) {
                    echo "Modelo '{$model_name}' migrado con exito." . PHP_EOL;
                } else {
                    echo "El modelo '{$model_name}' no se pudo migrar, por favor revisar." . PHP_EOL;
                    echo "Error => " . $resp->msg . PHP_EOL;
                    die();
                }
            } else {
                echo "Error => El modelo '{$model_name}' no existe, por favor revisar." . PHP_EOL;
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

    private function delete($model_name, $file)
    {

        $modelFileName = ucfirst($model_name) . 'Model';
        $relativeData = $this->mainController->getRelativeApiPathFile($modelFileName);
        $resp = (Object) [];

        if ($relativeData && file_exists($relativeData->path)) {

            $model = $relativeData->namespace;
            if (class_exists($model)) {

                $existTable = (object) $this->mainController->executeQueryNoResponse( 
                    "SELECT COUNT(*) AS table_count 
                    FROM INFORMATION_SCHEMA.TABLES 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = '{$model_name}';"
                ); 

                print_r($existTable);
                
                if(isset($existTable->totalElements) && $existTable->totalElements == 1){
                    //$resp = (object) $this->mainController->executeQueryNoResponse("DROP TABLE IF EXISTS {$model_name};");  
                } else {
                    $resp->status = false;
                    $resp->msg = "La tabla '{$model_name}' no existe en la base de datos, por favor revisar.";
                }

                print_r($resp);
                exit;

                if($file != null && $file == 'rmfile'){
                    if(unlink($relativeData->path)){
                        echo "Fichero '{$modelFileName}' eliminado con exito." . PHP_EOL;
                    } else {
                        echo "El fiechero '{$modelFileName}' no se pudo eliminar, por favor revisar." . PHP_EOL;
                    }
                }

                if ($resp->status) {
                    echo "Modelo '{$model_name}' eliminado con exito." . PHP_EOL;
                } else {
                    echo "Error => " . $resp->msg . PHP_EOL;
                    die();
                }
            } else {
                echo "Error => El modelo '{$model_name}' no existe, por favor revisar." . PHP_EOL;
                die();
            }
        } else {
            echo "Error => El modelo '{$model_name}' no existe, por favor revisar." . PHP_EOL;
            die();
        }
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
