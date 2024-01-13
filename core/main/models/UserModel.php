<?php

namespace core\main\models;

use core\main\FrameworkOrm;
use core\utils\Utils;

class UserModel extends FrameworkOrm
{

    const STATUS_DESACTIVATE = 0;
    const STATUS_ACTIVATE = 1;
    const STATUS_BLOCK = 2;

    const STATUS_PLACEHOLDER = [
        self::STATUS_DESACTIVATE => 'Desactivado',
        self::STATUS_ACTIVATE => 'Activado',
        self::STATUS_BLOCK => 'Bloqueado',
    ];

    const TABLE = 'users';

    const ARRAY_MAPPER = [
        'id' => [
            'type' => 'int',
            'primary' => true,
            'autoincrement' => true,
            'nullable' => false,
        ],
        'user_name' => [
            'type' => 'varchar',
            'nullable' => false,
            'unique' => true,
        ],
        'email' => [
            'type' => 'varchar',
            'nullable' => false,
            'unique' => true,
        ],
        'password' => [
            'type' => 'varchar',
            'nullable' => false,
            'length' => 999,
        ],
        'name' => [
            'type' => 'varchar',
            'nullable' => false,
        ],
        'last_name' => [
            'type' => 'varchar',
            'nullable' => false,
        ],
        'status' => [
            'type' => 'int',
            'nullable' => false,
            'default' => self::STATUS_ACTIVATE,
        ],
        'created_at' => [
            'type' => 'datetime',
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ],
        'updated_at' => [
            'type' => 'datetime',
            'nullable' => true,
        ],
    ];

    public function existUser(array $userData)
    {
        extract($userData);

        $response = (object) $this->getAllBy("user_name = '$user_name' OR email = '$user_name' LIMIT 1");

        if (!$response->status) {
            Utils::RouteNotFound();
            die();
        }


        $data = $response->data;

        if (empty($data)) {
            return false;
        }

        return $data[0];
    }

    public function publicData()
    {
        $userData = clone ($this);
        $userData->frameworkMain = null;

        $userData = get_object_vars($userData);

        unset($userData['frameworkMain']);
        unset($userData['password']);

        $userData['statusPlaceholder'] = self::STATUS_PLACEHOLDER[$userData['status']];

        return $userData;
    }

    public function changeStatus(){
        if($this->status == self::STATUS_ACTIVATE){
            $this->status = self::STATUS_DESACTIVATE;
        } else {
            $this->status = self::STATUS_ACTIVATE;
        }
        $this->update();
    }

    public function blockUser(){
        $this->status = self::STATUS_BLOCK;
        $this->update();
    }
}