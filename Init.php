<?php

namespace FacturaScripts\Plugins\OpenServBus;

use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Base\InitClass;

class Init extends InitClass
{
    public function init()
    {
        // se ejecuta cada vez que carga FacturaScripts (si este plugin está activado).
        $this->loadExtension(new Extension\Controller\EditRole());
        $this->loadExtension(new Extension\Controller\EditUser());
    }

    public function update()
    {
        // eliminamos las columnas deseadas de las tablas seleccionadas
        $dataBase = new DataBase();
        $columns = ['nombre'];
        $tables = ['employee_contracts', 'employee_attendance_management_yn', 'drivers', 'helpers', 'collaborators'];
        foreach ($tables as $table) {
            foreach ($dataBase->getColumns($table) as $column) {
                if (in_array($column->name, $columns)) {
                    $sql = 'ALTER TABLE ' . $table . ' DROP COLUMN ' . $column->name;
                    $dataBase->exec($sql);
                }
            }
        }
    }
}