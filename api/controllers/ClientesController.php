<?php

use api\models\ClientesModel;
use core\main\FrameworkMain;
use core\utils\Utils;

class ClientesController
{

    public static function list()
    {
        $model = new ClientesModel();
        $response = (object) $model->getAll(false);

        $data = $response->data;

        foreach ($data as $key => $info) :

            if ($info['status'] == ClientesModel::CLIENTES_STATUS_ACTIVO) {
                $info['placeStatus'] = ClientesModel::CLIENTES_STATUS_MSG[ClientesModel::CLIENTES_STATUS_ACTIVO];
            } elseif ($info['status'] == ClientesModel::CLIENTES_STATUS_INACTIVO) {
                $info['placeStatus'] = ClientesModel::CLIENTES_STATUS_MSG[ClientesModel::CLIENTES_STATUS_INACTIVO];
            }

            $data[$key] = $info;

        endforeach;

        $response->data = $data;

        FrameworkMain::genericApiResponse($response);
    }

    public static function createdFnt()
    {
        $requiredParams = [
            'name',
            'lastName',
            'age',
        ];

        if (Utils::validateRequestParams($requiredParams)) {
            $values = Utils::getRequestParams($requiredParams);

            $model = new ClientesModel();

            $model->load(null, $values);

            $model->save();
        }
    }

    public static function updatedFnt()
    {
        $requiredParams = [
            'id',
            'name',
            'lastName',
            'age',
        ];

        if (Utils::validateRequestParams($requiredParams)) {
            $values = (object) Utils::getRequestParams($requiredParams);

            $model = new ClientesModel($values->id);

            $model->load(null, $values);

            $model->update();
        }
    }
    public static function deleteFnt()
    {
        $requiredParams = [
            'id',
        ];

        if (Utils::validateRequestParams($requiredParams)) {
            $values = (object) Utils::getRequestParams($requiredParams);

            $model = new ClientesModel($values->id);

            if ($model->status == ClientesModel::CLIENTES_STATUS_ACTIVO) {
                $model->status = ClientesModel::CLIENTES_STATUS_INACTIVO;
            } else {
                $model->status = ClientesModel::CLIENTES_STATUS_ACTIVO;
            }

            $model->update();
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
            ],
            'updated' => [
                'fnt' => 'updatedFnt',
                'method' => 'POST',
            ],
            'delete' => [
                'fnt' => 'deleteFnt',
                'method' => 'GET',
            ],
        ];

        if (!array_key_exists($operation, $operations)) {
            Utils::RouteNotFound();
            die();
        }

        return (object) $operations[$operation];
    }
}