<?php

namespace core\main\models;

use core\main\FrameworkMain;
use core\main\FrameworkOrm;

class JwtModel extends FrameworkOrm
{

    const TABLE = 'jwt';

    const ONE_REQUEST_TOKEN = 0;
    const HOUR_TOKEN = 1;
    const DAY_TOKEN = 2;
    const MONTH_TOKEN = 3;
    const YEAR_TOKEN = 4;
    const INFINITE_TOKEN = 5;

    const TIME_BY_TYPE = [
        self::ONE_REQUEST_TOKEN => 'only_one_request',
        self::HOUR_TOKEN => '+1 hour',
        self::DAY_TOKEN => '+1 day',
        self::MONTH_TOKEN => '+1 month',
        self::YEAR_TOKEN => '+1 year',
        self::INFINITE_TOKEN => 'infinite_auth',
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

        return date('Y-m-d', strtotime(self::TIME_BY_TYPE[$type]));
    }

    public function deleteTokenByUserId($id)
    {
        $sql = "DELETE FROM :table WHERE user_id = {$id}";
        return $this->executeMainQuery($sql);
    }

    public function validateToken()
    {

        $dataToken = $this->getTokenData();

        if (!$dataToken) {
            return false;
        }

        $dataToken = (object) $dataToken;

        $userModel = new UserModel($dataToken->userId);

        if (!$userModel->id) {
            return false;
        }

        if ($dataToken->tokenType == self::ONE_REQUEST_TOKEN) {
            // Validación de una sola request
        }

        if ($dataToken->tokenType == self::INFINITE_TOKEN) {
            return true;
        }

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
}
