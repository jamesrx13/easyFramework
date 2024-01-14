<?php

namespace api\models;

use core\main\FrameworkOrm;

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