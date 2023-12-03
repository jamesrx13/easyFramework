<?php

use core\main\FrameworkMain;

class DemoController
{

    public static function list()
    {
        return FrameworkMain::genericApiResponse([
            'status' => true,
            'msg' => 'Listado desde la vista WIII',
        ]);
    }

    public static function routes($operation)
    {
        if ($operation == '') {
            // Se establece la ruta por defecto
            $operation = 'list';
        }

        $operations = [
            'list' => [
                'fnt' => 'list',
                'method' => 'GET',
            ],
            // 'create',
            // 'update',
            // 'delete',
        ];

        if (!array_key_exists($operation, $operations)) {
            FrameworkMain::genericApiResponse([
                "status" => false,
                "msg" => "Route not found",
            ]);
            die();
        }

        return (object) $operations[$operation];
    }
}
