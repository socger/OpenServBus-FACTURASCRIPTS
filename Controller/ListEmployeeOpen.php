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

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListEmployeeOpen extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'employees';
        $pageData['icon'] = 'far fa-id-card';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewEmployee();
        $this->createViewEmployeeContract();
    }

    protected function createViewEmployee($viewName = 'ListEmployeeOpen')
    {
        $this->addView($viewName, 'EmployeeOpen', 'employees', 'far fa-id-card');
        $this->addSearchFields($viewName, ['cod_employee', 'nombre', 'direccion']);
        $this->addOrderBy($viewName, ['nombre'], 'name', 1);
        $this->addOrderBy($viewName, ['cod_employee'], 'code');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'company', 'idempresa', 'empresas', 'idempresa', 'nombre');

        $esConductor = [
            ['code' => '1', 'description' => 'driver-yes'],
            ['code' => '0', 'description' => 'driver-no'],
        ];
        $this->addFilterSelect($viewName, 'esConductor', 'driver-all', 'driver_yn', $esConductor);
    }

    protected function createViewEmployeeContract($viewName = 'ListEmployeeContract')
    {
        $this->addView($viewName, 'EmployeeContract', 'contracts', 'fas fa-file-contract');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['fecha_inicio', 'fecha_fin'], 'fstart-fend');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'company', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'employee', 'idemployee', 'employees_open', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdemployee_contract_type', 'contract-type', 'idemployee_contract_type', 'employee_contract_types', 'idemployee_contract_type', 'nombre');
    }
}