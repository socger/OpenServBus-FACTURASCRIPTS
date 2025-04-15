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

class ListTarjeta extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'cards';
        $pageData['icon'] = 'fab fa-cc-mastercard';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewTarjeta();
    }

    protected function createViewTarjeta($viewName = 'ListTarjeta')
    {
        $this->addView($viewName, 'Tarjeta', 'cards', 'fas fa-credit-card');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'name', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'company', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'employee', 'idemployee', 'employees_open', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdDriver', 'driver', 'iddriver', 'drivers', 'iddriver', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdTarjeta_Type', 'card-type', 'idtarjeta_type', 'tarjeta_types', 'idtarjeta_type', 'nombre');

        $esDePago = [
            ['code' => '1', 'description' => 'is-paid-yes'],
            ['code' => '0', 'description' => 'is-paid-no'],
        ];
        $this->addFilterSelect($viewName, 'esDepago', 'is-paid-all', 'de_pago', $esDePago);
    }
}