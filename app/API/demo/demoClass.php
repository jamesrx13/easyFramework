<?php

include "../../../core/main/frameworkMainCore.php";

class DemoClass extends FrameworkMain
{

    public string $table = "tbl_demo";

    // Mapeado de las columnas de la Db representados en variables
    public int $demo_id = 0;
    public string $demo_name = "";
    public int $demo_status = 0;
    public string $demo_create = "";
    public string $demo_update = "";

    function __construct($id = null)
    {
        $id = !is_null($id) ? $id : 0;

        if ($id != 0) {
            self::getCurrentElement($id);
        }
    }

    protected function getCurrentElement($id)
    {
        $element = self::getAllDataBy($this->table, "demo_id = {$id}");

        return $element;
    }
}
