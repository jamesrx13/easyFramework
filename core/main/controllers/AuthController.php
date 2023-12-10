<?php

namespace core\main\controllers;

use core\config\GlobalConfig;
use core\main\FrameworkMain;
use core\main\models\JwtModel;
use core\main\models\UserModel;
use core\utils\Utils;

class AuthController
{

    public function __construct()
    {
    }

    public static function login()
    {
        $requiredParams = [
            'user_name',
            'password',
        ];

        if (Utils::validateRequestParams($requiredParams)) {
            $values = Utils::getRequestParams($requiredParams);
            $userModel = new UserModel();

            $userData = $userModel->existUser($values);

            if ($userData) {

                $userModel->load(null, $userData);

                if (FrameworkMain::verifyPassword($values['password'], $userModel->password)) {


                    $jwtModel = new JwtModel();

                    $response = $jwtModel->deleteTokenByUserId($userModel->id);

                    if ($response['status']) {
                        $expiration = $jwtModel->dateExpriationByType();

                        $jwt = FrameworkMain::encrypt_decrypt('encrypt', JwtModel::DAY_TOKEN); //Tipo de token
                        $jwt .= '.' . FrameworkMain::encrypt_decrypt('encrypt', $userModel->id); // Id del usuario
                        $jwt .= '.' . FrameworkMain::encrypt_decrypt('encrypt', $expiration); //Fecha de expiracion

                        $jwtModel->load(null, [
                            'user_id' => $userModel->id,
                            'token' => $jwt,
                        ]);

                        $jwtModel->save(false);

                        FrameworkMain::genericApiResponse([
                            "status" => true,
                            "AuthToken" => $jwt,
                            "data" => $userModel->publicData()
                        ]);
                    } else {
                        FrameworkMain::genericApiResponse($response);
                    }
                } else {
                    Utils::UserIncorrectPassword();
                }
            } else {
                Utils::UserNotFound();
            }
        }
    }

    public static function isValidToken()
    {
        $jwtModel = new JwtModel();
        $jwtModel->token = FrameworkMain::getRequestHeader(GlobalConfig::HEADER_TOKEN);
        return $jwtModel->validateToken();
    }

    public static function routes($operation)
    {
        // Se establece la ruta por defecto

        if (is_object($operation)) {
            $operation = $operation->route;
        }

        if ($operation == '') {
            // $operation = 'login';
        }

        $operations = [
            'login' => [
                'fnt' => 'login',
                'method' => 'POST',
            ],
        ];

        if (!array_key_exists($operation, $operations)) {
            Utils::RouteNotFound();
            die();
        }

        return (object) $operations[$operation];
    }
}
