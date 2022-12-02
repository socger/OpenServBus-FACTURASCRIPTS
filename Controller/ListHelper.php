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

class ListHelper extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'files';
        $pageData['icon'] = 'fas fa-archive';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewHelper();
        $this->createViewGarage();
        $this->createViewDepartment();
        $this->createViewCollaborator();
        $this->createViewIdentificationMean();
    }

    protected function createViewCollaborator($viewName = 'ListCollaborator')
    {
        $this->addView($viewName, 'Collaborator', 'collaborator', 'fas fa-business-time');
        $this->addSearchFields($viewName, ['codproveedor', 'nombre']);
        $this->addOrderBy($viewName, ['codproveedor'], 'cod-supplier');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);
    }

    protected function createViewDepartment($viewName = 'ListDepartment')
    {
        $this->addView($viewName, 'Department', 'departments', 'fas fa-book-reader');
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

    protected function createViewGarage($viewName = 'ListGarage')
    {
        $this->addView($viewName, 'Garage', 'garages', 'fas fa-warehouse');
        $this->addSearchFields($viewName, ['nombre', 'direccion']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'company', 'idempresa', 'empresas', 'idempresa', 'nombre');
    }

    protected function createViewHelper($viewName = 'ListHelper')
    {
        $this->addView($viewName, 'Helper', 'monitors', 'fas fa-user-graduate');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $status = [
            ['label' => 'collaborators-employess-all', 'where' => []],
            ['label' => 'collaborators-only', 'where' => [new DataBaseWhere('idcollaborator', '0', '>')]],
            ['label' => 'employees-only', 'where' => [new DataBaseWhere('idemployee', '0', '>')]]
        ];
        $this->addFilterSelectWhere($viewName, 'status', $status);
    }

    protected function createViewIdentificationMean($viewName = 'ListIdentificationMean')
    {
        $this->addView($viewName, 'IdentificationMean', 'means-of-identification', 'far fa-hand-point-right');
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
}
