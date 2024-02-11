<?php

namespace core\main;

class FrameworkOrm
{
    const TABLE = '';

    const ARRAY_MAPPER = [];

    protected $frameworkMain;

    function __construct($id = null)
    {
        $this->initializeProperties();
        $this->frameworkMain = new FrameworkMain();

        if ($id != null) {
            $this->load($id);
        }
    }

    protected function initializeProperties()
    {
        foreach (static::ARRAY_MAPPER as $key => $value) {
            $this->$key = null;
        }
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            $this->initializeProperties();
        }
    }

    public function getColums()
    {
        $columns = [];
        foreach (static::ARRAY_MAPPER as $key => $value) {
            if (isset($this->$key)) {
                $columns[] = $key;
            }
        }
        return $columns;
    }

    public function load($id = null, $arryModel = [])
    {
        if ($id != null) {
            $data = (object) $this->getAllBy("{$this->getPrimaryColum()} = {$id} LIMIT 1");
            if ($data->status && !empty($data->data)) {
                $data = $data->data[0];

                foreach (static::ARRAY_MAPPER as $key => $value) {
                    $this->$key = $data[$key];
                }
            }
        } elseif (!empty($arryModel)) {
            $data = $arryModel;
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function getValues()
    {
        $values = [];
        foreach (static::ARRAY_MAPPER as $key => $value) {
            if (isset($this->$key)) {
                if (gettype($this->$key) == 'string') {
                    $values[] = "'" . $this->$key . "'";
                } else {
                    $values[] = $this->$key;
                }
            } else {
                continue;
            }
        }
        return $values;
    }

    public function generateTableSql()
    {
        $table = static::TABLE;
        $arrayMapper = static::ARRAY_MAPPER;

        $sql = "DROP TABLE IF EXISTS {$table};";

        // Motor de almacenamiento predeterminado
        $defaultStorageEngine = 'InnoDB';

        $columns = [];
        $relations = [];

        foreach ($arrayMapper as $columnName => $columnDetails) {

            if (isset($columnDetails['relation'])) {
                $relationModel = new $columnDetails['relation'];
                $relations[] = "ALTER TABLE " .  $table . " ADD COLUMN " . $columnName . " INT NOT NULL, ADD CONSTRAINT " . 'fk_' . $table . '_' . $columnName .  " FOREIGN KEY (" . $columnName . ") REFERENCES " . $relationModel::TABLE . " (id) ON DELETE CASCADE ON UPDATE CASCADE;";
                continue;
            }

            $type = $columnDetails['type'];

            // Establecer una longitud predeterminada de 255 para columnas de tipo varchar, text si no se especifica
            $length = isset($columnDetails['length']) ? $columnDetails['length'] : ($type === 'varchar' || $type === 'text' ? 255 : null);

            $columnDefinition = "$columnName $type";

            if ($length !== null) {
                $columnDefinition .= "($length)";
            }

            if (isset($columnDetails['primary']) && $columnDetails['primary']) {
                $columnDefinition .= ' PRIMARY KEY';
            }

            if (isset($columnDetails['autoincrement']) && $columnDetails['autoincrement']) {
                $columnDefinition .= ' AUTO_INCREMENT';
            }

            if (isset($columnDetails['nullable']) && !$columnDetails['nullable']) {
                $columnDefinition .= ' NOT NULL';
            }

            if (isset($columnDetails['default'])) {
                $columnDefinition .= " DEFAULT {$columnDetails['default']}";
            }

            if (isset($columnDetails['unique']) && $columnDetails['unique']) {
                $columnDefinition .= " UNIQUE";
            }

            $columns[] = $columnDefinition;
        }

        // Agregar la opción del motor de almacenamiento por defecto
        $tableOptions = "ENGINE={$defaultStorageEngine}";

        $sql .= "CREATE TABLE $table ("
            . implode(', ', $columns)
            . ") $tableOptions;" . implode(' ', $relations);

        return $sql;
    }

    public function getLastInsertId()
    {
        return $this->frameworkMain->getDB()['dataBase']->lastInsertId();
    }

    public function getAll($autoResponse = true, $pagination = false)
    {
        return $this->frameworkMain->getAllData(static::TABLE, $autoResponse, $pagination);
    }

    public function getAllBy($whereCondition, $data = [], $autoResponse = false, $pagination = false)
    {
        return $this->frameworkMain->getAllDataBy(static::TABLE, $whereCondition, $autoResponse, $pagination, $data);
    }

    public function getPrimaryColum()
    {
        foreach (static::ARRAY_MAPPER as $key => $value) {
            if (isset($value['primary']) && $value['primary']) {
                return $key;
            }
        }
    }

    public function save($autoResponse = true)
    {
        $table = static::TABLE;
        $colums = implode(', ', self::getColums());
        $values = implode(', ', self::getValues());

        $sql = "INSERT INTO {$table} ({$colums}) VALUES ({$values})";

        if ($autoResponse) {
            return $this->frameworkMain->executeQuery($sql);
        } else {
            $response = $this->frameworkMain->executeQueryNoResponse($sql);
            $this->load($this->getLastInsertId());
            unset($response['data']);
            $response['model'] = $this;
            return  $response;
        }
    }
    public function update($autoResponse = true)
    {
        $table = static::TABLE;

        if (array_key_exists('updated_at', static::ARRAY_MAPPER)) {
            $this->updated_at = date('Y-m-d H:i:s');
        }

        $colums = self::getColums();
        $values = self::getValues();

        $fields = [];

        foreach ($colums as $key => $field) {
            if ($field == $this->getPrimaryColum()) {
                continue;
            } else {
                $fields[] = $field . ' = ' . $values[$key];
            }
        }

        $fields = implode(', ', $fields);


        $sql = "UPDATE {$table} SET {$fields} WHERE {$this->getPrimaryColum()} = {$this->{$this->getPrimaryColum()}}";

        if ($autoResponse) {
            return $this->frameworkMain->executeQuery($sql);
        } else {
            $response = $this->frameworkMain->executeQueryNoResponse($sql);
            $this->load($this->{$this->getPrimaryColum()});
            unset($response['data']);
            $response['model'] = $this;
            return  $response;
        }
    }

    public function delete($autoResponse = true)
    {
        $table = static::TABLE;

        $sql = "DELETE FROM {$table} WHERE {$this->getPrimaryColum()} = {$this->{$this->getPrimaryColum()}}";

        if ($autoResponse) {
            return $this->frameworkMain->executeQuery($sql);
        } else {
            return $this->frameworkMain->executeQueryNoResponse($sql);
        }
    }

    public function executeMainQuery($sql)
    {
        $sql = str_replace(':table', static::TABLE, $sql);
        return $this->frameworkMain->executeQueryNoResponse($sql);
    }

    public function getTable()
    {
        return static::TABLE;
    }
}