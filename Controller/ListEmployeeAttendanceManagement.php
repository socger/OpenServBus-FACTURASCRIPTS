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

class ListEmployeeAttendanceManagement extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'site-control';
        $pageData['icon'] = 'fas fa-hourglass-half';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewEmployeeAttendanceManagement();
        $this->createViewAbsenceReason();
        $this->createViewEmployeeAttendanceManagementYn();
    }

    protected function createViewAbsenceReason($viewName = 'ListAbsenceReason')
    {
        $this->addView($viewName, 'AbsenceReason', 'absences-reasons', 'fas fa-first-aid');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'name', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);
    }

    protected function createViewEmployeeAttendanceManagement($viewName = 'ListEmployeeAttendanceManagement')
    {
        $this->addView($viewName, 'EmployeeAttendanceManagement', 'transfers-and-assists', 'fas fa-hourglass-half');
        $this->addOrderBy($viewName, ['fecha'], 'date', 1);
        $this->addOrderBy($viewName, ['idemployee', 'fecha'], 'employee-date');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'employee', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdidentification_mean', 'Identificacion - medio', 'ididentification_mean', 'identification_means', 'ididentification_mean', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdabsence_reason', 'absence-reason', 'idabsence_reason', 'absence_reasons', 'idabsence_reason', 'nombre');
        $this->addFilterPeriod($viewName, 'porFecha', 'date', 'fecha');

        $origen = [
            ['code' => '0', 'description' => 'origin-external'],
            ['code' => '1', 'description' => 'origin-manual'],
        ];
        $this->addFilterSelect($viewName, 'elOrigen', 'origin-all', 'origen', $origen);

        $origen = [
            ['code' => '1', 'description' => 'transfer-type-entry'],
            ['code' => '0', 'description' => 'transfer-type-output'],
        ];
        $this->addFilterSelect($viewName, 'elTipoFichaje', 'transfer-type-all', 'tipoFichaje', $origen);
    }

    protected function createViewEmployeeAttendanceManagementYn($viewName = 'ListEmployeeAttendanceManagementYn')
    {
        $this->addView($viewName, 'EmployeeAttendanceManagementYn', 'force-face-to-face-control', 'fas fa-fingerprint');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'employee', 'idemployee', 'employees', 'idemployee', 'nombre');
    }
}