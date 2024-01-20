<?php

use api\models\DemoModel;
use core\main\FrameworkMain;
use core\main\models\UserModel;
use core\utils\Utils;

class DemoController
{

    const FOLDER_UPLOAD = 'demo/img';

    public static function list()
    {
        $model = new DemoModel();
        $resp = $model->getAll();
        
        $data = $resp['data'];
        $newData = [];

        foreach($data as $register){
            $register['imageUrl'] = Utils::getMainUrl() . $register['imageUrl'];
            $newData[] = $register;
        }

        $resp['data'] = $newData;

        FrameworkMain::genericApiResponse($resp);
    }

    public static function createdFnt()
    {
        $requiredParams = [
            'name',
        ];

        $requiredFiles = [
            'image',
        ];

        if (Utils::validateRequestParams($requiredParams) && Utils::validateRequestFiles($requiredFiles)) {

            $params = (object) Utils::getRequestParams($requiredParams);
            $files = Utils::getRequestFiles($requiredFiles);

            $model = new DemoModel();
            $model->load(null, $params);

            $imagesPath = Utils::uploadAccess($files, FrameworkMain::IMAGES_FORMAT, self::FOLDER_UPLOAD);

            $model->imageUrl = $imagesPath[0];

            $model->save();
        }
    }

    public static function updatedFnt()
    {
        $requiredParams = [
            'id',
            'name',
        ];

        $files = [
            'image'
        ];

        if (Utils::validateRequestParams($requiredParams)) {
            $params = (object) Utils::getRequestParams($requiredParams);
            $files = Utils::getRequestFiles($files);

            $model = new DemoModel($params->id);

            $model->name = $params->name;

            if(!empty($files) && $model->id != null){
                $currentImagen = explode('/', $model->imageUrl);
                $currentImagen = explode('.', $currentImagen[count($currentImagen) - 1]);
                $currentImagen = $currentImagen[0];
                Utils::uploadAccess($files, FrameworkMain::IMAGES_FORMAT, self::FOLDER_UPLOAD, $currentImagen);
            }

            $model->update();
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

        $editAndCreatedAccess = [
            UserModel::USER_ROL_ADMIN,
            UserModel::USER_ROL_USER,
        ];

        $deleteAccess = UserModel::USER_ROL_ADMIN;

        $operations = [
            'list' => [
                'fnt' => 'list',
                'method' => 'GET',
            ],
            'created' => [
                'fnt' => 'createdFnt',
                'method' => 'POST',
                'auth' => true,
                'roles' => $editAndCreatedAccess,
            ],
            'updated' => [
                'fnt' => 'updatedFnt',
                'method' => 'POST',
                'auth' => true,
                'roles' => $editAndCreatedAccess,
            ],
            'delete' => [
                'fnt' => 'deleteFnt',
                'method' => 'GET',
                'auth' => true,
                'roles' => $deleteAccess,
            ],
        ];

        if (!array_key_exists($operation, $operations)) {
            Utils::RouteNotFound();
            die();
        }

        return (object) $operations[$operation];
    }
}
