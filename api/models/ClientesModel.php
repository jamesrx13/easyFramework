<?php

namespace api\models;

use core\main\FrameworkOrm;

class ClientesModel extends FrameworkOrm
{
    const TABLE = 'clientes';

    const CLIENTES_STATUS_ACTIVO = 1;
    const CLIENTES_STATUS_INACTIVO = 0;

    const CLIENTES_STATUS_MSG = [
        self::CLIENTES_STATUS_ACTIVO => 'Activo',
        self::CLIENTES_STATUS_INACTIVO => 'Inactivo',
    ];

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
        'lastName' => [
            'type' => 'varchar',
            'nullable' => false,
        ],
        'age' => [
            'type' => 'int',
            'nullable' => false,
        ],
        'status' => [
            'type' => 'int',
            'nullable' => false,
            'default' => 1
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