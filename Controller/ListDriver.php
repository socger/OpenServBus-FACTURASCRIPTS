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

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListDriver extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'drivers';
        $pageData['icon'] = 'fas fa-user-astronaut';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewDriver();
    }

    protected function createViewDriver($viewName = 'ListDriver')
    {
        $this->addView($viewName, 'Driver', 'drivers', 'fas fa-user-astronaut');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterSelectWhere(
            $viewName,
            'status',
            [
                ['label' => 'Colaboradores/Empleados - Todos', 'where' => []],
                ['label' => 'Colaboradores sólo', 'where' => [new DataBaseWhere('idcollaborator', '0', '>')]],
                ['label' => 'Empleados sólo', 'where' => [new DataBaseWhere('idemployee', '0', '>')]]
            ]
        );
    }
}