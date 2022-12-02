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
use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditEmployee extends EditController
{
    public function getModelClassName(): string
    {
        return 'Employee';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'employee';
        $pageData['icon'] = 'far fa-id-card';
        return $pageData;
    }

    protected function createViews()
    {
        parent::createViews();
        $this->createViewEmployeeContract();
        $this->createViewEmployeeAttendanceManagementYn();
        $this->createViewEmployeeDocumentation();
        $this->setTabsPosition('top');
    }

    protected function createViewEmployeeContract($viewName = 'ListEmployeeContract')
    {
        $this->addListView($viewName, 'EmployeeContract', 'contracts-made', 'fas fa-id-badge');
        $this->views[$viewName]->addSearchFields(['nombre']);
        $this->views[$viewName]->addOrderBy(['fecha_inicio', 'fecha_fin'], 'fstart-fend');
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);

        $this->views[$viewName]->addFilterAutocomplete('xIdEmpresa', 'company', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdEmployee', 'employee', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdemployee_contract_type', 'contract-type', 'idemployee_contract_type', 'employee_contract_types', 'idemployee_contract_type', 'nombre');
    }

    protected function createViewEmployeeAttendanceManagementYn($viewName = 'ListEmployeeAttendanceManagementYn')
    {
        $this->addListView($viewName, 'EmployeeAttendanceManagementYn', 'are-you-required-to-check-presence', 'fas fa-business-timee');
        $this->views[$viewName]->addSearchFields(['idemployee', 'nombre']);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);
    }

    protected function createViewEmployeeDocumentation($viewName = 'ListEmployeeDocumentation')
    {
        $this->addListView($viewName, 'EmployeeDocumentation', 'documentation', 'far fa-file-pdf');
        $this->views[$viewName]->addSearchFields(['nombre']);
        $this->views[$viewName]->addOrderBy(['nombre'], 'name', 1);
        $this->views[$viewName]->addOrderBy(['iddocumentation_type', 'nombre'], 'doctype-name');
        $this->views[$viewName]->addOrderBy(['fecha_caducidad'], 'date-expiration');
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);

        $this->views[$viewName]->addFilterAutocomplete('xIdEmployee', 'employee', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xiddocumentation_type', 'documentation - tipo', 'iddocumentation_type', 'documentation_types', 'iddocumentation_type', 'nombre');
        $this->views[$viewName]->addFilterPeriod('porFechaCaducidad', 'date-expiration', 'fecha_caducidad');
    }

    protected function loadData($viewName, $view)
    {
        $mvn = $this->getMainViewName();
        switch ($viewName) {
            case 'ListEmployeeDocumentation':
            case 'ListEmployeeContract':
            case 'ListEmployeeAttendanceManagementYn':
                $idemployee = $this->getViewModelValue($mvn, 'idemployee');
                $where = [new DatabaseWhere('idemployee', $idemployee)];
                $view->loadData('', $where);
                break;

            case $mvn:
                parent::loadData($viewName, $view);
                $this->ponerContratoActivoEnVista($viewName);
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }

    protected function ponerContratoActivoEnVista(string $mvn)
    {
        // Rellenamos el widget de tipo text para el tipo de contrato
        $idemployee = $this->getViewModelValue($mvn, 'idemployee');
        if (!empty($idemployee)) {
            $sql = " SELECT employee_contract_types.nombre "
                . ", employee_contracts.fecha_inicio "
                . ", employee_contracts.fecha_fin "
                . " FROM employee_contracts "
                . " LEFT JOIN employee_contract_types ON (employee_contract_types.idemployee_contract_type = employee_contracts.idemployee_contract_type) "
                . " WHERE employee_contracts.idemployee = " . $idemployee . " "
                . " AND employee_contracts.activo = 1 "
                . " ORDER BY employee_contracts.idemployee "
                . " , employee_contracts.fecha_inicio DESC "
                . " , employee_contracts.fecha_fin DESC "
                . " LIMIT 1 ";

            $registros = $this->dataBase->select($sql);
            foreach ($registros as $fila) {
                $this->views[$mvn]->model->tipo_contrato = $fila['nombre'];
            }
        }
    }
}