<?php
/**
 * This file is part of OpenServBus plugin for FacturaScripts
 * Copyright (C) 2021-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 * Copyright (C) 2021 Jerónimo Pedro Sánchez Manzano <socger@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

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
        $this->deleteColumnFromTable();
    }

    protected function deleteColumnFromTable()
    {
        // eliminamos las columnas deseadas de las tablas seleccionadas
        // al actualizar a la versión 3.0
        $dataBase = new DataBase();
        $columns = ['nombre'];
        $tables = ['employee_contracts', 'employees_attendance_management_yn', 'drivers', 'helpers', 'collaborators'];
        foreach ($tables as $table) {
            // preguntamos si exite la tabla
            if (false === $dataBase->tableExists($table)) {
                continue;
            }
            foreach ($dataBase->getColumns($table) as $column) {
                if (in_array($column->name, $columns)) {
                    $sql = 'ALTER TABLE ' . $table . ' DROP COLUMN ' . $column->name;
                    $dataBase->exec($sql);
                }
            }
        }
    }
}