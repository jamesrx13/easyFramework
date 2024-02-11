<?php

namespace api\models;

use core\main\FrameworkOrm;
use core\main\models\UserModel;

class DemoModel extends FrameworkOrm
{
    const TABLE = 'demo';

    const ARRAY_MAPPER = [
        'id' => [
            'type' => 'int',
            'primary' => true,
            'autoincrement' => true,
            'nullable' => false,
        ],
        'name' => [
            'type' => 'varchar',
            'nullable' => false,
        ],
        'imageUrl' => [
            'type' => 'longtext',
            'nullable' => false,
        ],
        'createdBy' => [
            'type' => 'int',
            'nullable' => false,
            'relation' => UserModel::class,
        ],
        'updatedBy' => [
            'type' => 'int',
            'relation' => UserModel::class,
            'nullable' => true,
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
}