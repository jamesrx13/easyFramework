<?php

namespace core\main\models;

use core\main\FrameworkMain;
use core\main\FrameworkOrm;
use core\utils\Utils;

class JwtModel extends FrameworkOrm
{

    const TABLE = 'jwt';

    const ONE_REQUEST_TOKEN = 1;
    const HOUR_TOKEN = 2;
    const DAY_TOKEN = 3;
    const MONTH_TOKEN = 4;
    const YEAR_TOKEN = 5;
    const INFINITE_TOKEN = 6;

    const TIME_BY_TYPE = [
        self::ONE_REQUEST_TOKEN => 'only_one_request',
        self::HOUR_TOKEN => '+1 hour',
        self::DAY_TOKEN => '+1 day',
        self::MONTH_TOKEN => '+1 month',
        self::YEAR_TOKEN => '+1 year',
        self::INFINITE_TOKEN => 'infinite_auth',
    ];

    const TOKEN_TEXT = [
        self::ONE_REQUEST_TOKEN => 'Single request token',
        self::HOUR_TOKEN => 'One hour token',
        self::DAY_TOKEN => 'One day token',
        self::MONTH_TOKEN => 'One month token',
        self::YEAR_TOKEN => 'One year token',
        self::INFINITE_TOKEN => 'Infinite token',
    ];

    const ARRAY_MAPPER = [
        'id' => [
            'type' => 'int',
            'primary' => true,
            'autoincrement' => true,
            'nullable' => false,
        ],
        'user_id' => [
            'nullable' => false,
            'relation' => UserModel::class,
        ],
        'token' => [
            'type' => 'varchar',
            'nullable' => false,
            'length' => 999,
        ],
        'isSessionToken' => [
            'type' => 'boolean',
            'nullable' => false,
            'default' => true,
        ],
        'description' => [
            'type' => 'text',
            'nullable' => true,
        ],
        'status' => [
            'type' => 'boolean',
            'nullable' => false,
            'default' => true,
        ],
        'created_at' => [
            'type' => 'datetime',
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ],
    ];

    public function dateExpriationByType($type = self::DAY_TOKEN)
    {

        if ($type == self::INFINITE_TOKEN || $type == self::ONE_REQUEST_TOKEN) {
            return self::TIME_BY_TYPE[$type];
        }

        return date('Y-m-d:m-s', strtotime(self::TIME_BY_TYPE[$type]));
    }

    public function deleteTokenByUserId($id)
    {
        $sql = "DELETE FROM :table WHERE user_id = {$id} AND isSessionToken = 1";
        return $this->executeMainQuery($sql);
    }

    public function validateToken()
    {
        $token = $this->token;
        $token = (object) $this->getAllBy("token = '{$token}'");

        if(empty($token->data)){
            return false;
        }

        $dataToken = (object) $token->data[0];

        if(!$dataToken->status){
            return false;
        }

        $dataToken = $this->getTokenData();

        if (!$dataToken) {
            return false;
        }

        $dataToken = (object) $dataToken;

        $userModel = new UserModel($dataToken->userId);

        if (!$userModel->id) {
            return false;
        }

        if($userModel->status != UserModel::STATUS_ACTIVATE){
            Utils::userNotActivate($userModel->status);
        }

        // Tokens de un solo uso
        if ($dataToken->tokenType == self::ONE_REQUEST_TOKEN) {
            $currentTokenData = (object) $token->data[0];
            (new JwtModel($currentTokenData->id))->delete(false);
            return true;
        }
        // Token infinito
        if ($dataToken->tokenType == self::INFINITE_TOKEN) {
            return true;
        }
        // Tokents de tiempo
        if ($dataToken->expiration < date('Y-m-d')) {
            return false;
        } else {
            return true;
        }
    }

    public function getTokenData()
    {
        /*
        * Tipo de token
        * Id del usuario
        * Fecha de expiracion
        */
        $dataToken = explode('.', $this->token);

        if (count($dataToken) != 3) {
            return false;
        }

        $dataToken['tokenType'] = FrameworkMain::encrypt_decrypt('decrypt', $dataToken[0]);
        $dataToken['userId'] = FrameworkMain::encrypt_decrypt('decrypt', $dataToken[1]);
        $dataToken['expiration'] = FrameworkMain::encrypt_decrypt('decrypt', $dataToken[2]);

        unset($dataToken[0]);
        unset($dataToken[1]);
        unset($dataToken[2]);

        return $dataToken;
    }

    public function getUserId(){
        return $this->getTokenData()['userId'];
    }

    public function generateToken($tokenType, $userID){

        if(!key_exists($tokenType, self::TIME_BY_TYPE)){
            return false;
        }

        $jwt = FrameworkMain::encrypt_decrypt('encrypt', $tokenType); //Tipo de token
        $jwt .= '.' . FrameworkMain::encrypt_decrypt('encrypt', $userID); // Id del usuario
        $jwt .= '.' . FrameworkMain::encrypt_decrypt('encrypt', $this->dateExpriationByType($tokenType)); //Fecha de expiracio
        return $jwt;
    }
}
