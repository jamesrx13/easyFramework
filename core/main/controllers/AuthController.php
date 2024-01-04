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

    public static function userRegister()
    {
        $requiredParams = [
            'user_name',
            'email',
            'password',
            'name',
            'last_name',
        ];

        if (Utils::validateRequestParams($requiredParams)) {
            $values = Utils::getRequestParams($requiredParams);
            $userModel = new UserModel();
            $userModel->load(null, $values);
            $userModel->password = FrameworkMain::hashPassword($userModel->password);
            $userModel->save();
        }
    }

    public static function updateUser()
    {
        $requiredParams = [
            'user_name',
            'email',
            'name',
            'last_name',
            'password',
        ];

        if (Utils::validateRequestParams($requiredParams)) {

            $values = (object) Utils::getRequestParams($requiredParams);

            $jwtModel = new JwtModel();
            $jwtModel->token = FrameworkMain::getRequestHeader(GlobalConfig::HEADER_TOKEN);

            $tokenData = (object) $jwtModel->getTokenData();

            $userModel = new UserModel($tokenData->userId);

            if ($userModel->id != null) {

                if (FrameworkMain::verifyPassword($values->password, $userModel->password)) {
                    $userModel->load(null, $values);
                    $userModel->password = FrameworkMain::hashPassword($userModel->password);
                    $userModel->update();
                } else {
                    Utils::UserIncorrectPassword();
                }
            } else {
                Utils::UserNotFound();
            }
        }
    }

    public static function resetPassword()
    {
        // TODO:
        // Utils::sendEmail('rudasmarinjf@hotmail.com', 'Test demo', 'Hola mundo test');
    }

    public static function logoutFnt()
    {
        $token = FrameworkMain::getRequestHeader(GlobalConfig::HEADER_TOKEN);
        $res = (new JwtModel())->executeMainQuery("DELETE FROM :table WHERE token = '{$token}'");
        FrameworkMain::genericApiResponse($res);
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
            'register' => [
                'fnt' => 'userRegister',
                'method' => 'POST',
            ],
            'update' => [
                'fnt' => 'updateUser',
                'method' => 'POST',
                'auth' => true,
            ],
            // 'resetPassword' => [
            //     'fnt' => 'resetPassword',
            //     'method' => 'POST',
            // ],
            'logout' => [
                'fnt' => 'logoutFnt',
                'method' => 'POST',
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