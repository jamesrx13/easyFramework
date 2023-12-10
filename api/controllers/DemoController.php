<?php

use api\models\DemoModel;
use core\main\FrameworkMain;
use core\utils\Utils;

class DemoController
{

    public static function list()
    {
        $model = new DemoModel();
        FrameworkMain::genericApiResponse($model->getAll(false));
    }

    public static function createdFnt()
    {
        $requiredParams = [
            'name',
        ];

        if (Utils::validateRequestParams($requiredParams)) {
            $params = Utils::getRequestParams($requiredParams);
            $model = new DemoModel();
            $model->load(null, $params);
            $response = $model->save(false);
            FrameworkMain::genericApiResponse($response);
        }
    }

    public static function updatedFnt()
    {
        $requiredParams = [
            'id',
            'name',
        ];

        if (Utils::validateRequestParams($requiredParams)) {
            $params = (object) Utils::getRequestParams($requiredParams);

            $model = new DemoModel($params->id);

            $model->name = $params->name;

            $response = $model->update(false);

            FrameworkMain::genericApiResponse($response);
        }
    }

    public static function deleteFnt()
    {
        $requiredParams = [
            'id',
        ];

        if (Utils::validateRequestParams($requiredParams)) {
            $params = (object) Utils::getRequestParams($requiredParams);

            $model = new DemoModel($params->id);

            $model->delete();
        }
    }

    public static function routes($operation)
    {
        // Se establece la ruta por defecto
        if ($operation == '') {
            $operation = 'list';
        }

        $operations = [
            'list' => [
                'fnt' => 'list',
                'method' => 'GET',
            ],
            'created' => [
                'fnt' => 'createdFnt',
                'method' => 'POST',
                'auth' => true,
            ],
            'updated' => [
                'fnt' => 'updatedFnt',
                'method' => 'POST',
                'auth' => true,
            ],
            'delete' => [
                'fnt' => 'deleteFnt',
                'method' => 'GET',
                'auth' => true,
            ],
        ];

        if (!array_key_exists($operation, $operations)) {
            Utils::RouteNotFound();
            die();
        }

        return (object) $operations[$operation];
    }
}
