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

class ListFuelKm extends ListController
{

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'refueling-kms';
        $pageData['icon'] = 'fas fa-gas-pump';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewFuelKm();
        $this->createViewFuel_pump();
    }

    protected function createViewFuelKm($viewName = 'ListFuelKm')
    {
        $this->addView($viewName, 'FuelKm', 'refueling-kms', 'fas fa-gas-pump');
        $this->addOrderBy($viewName, ['fecha'], 'Fecha', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'company', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdVehicle', 'vehicle', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdFuel_Type', 'fuel', 'idfuel_type', 'fuel_types', 'idfuel_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdFuel_Pumps', 'internal-fuel-dispenser', 'idfuel_pump', 'fuel_pumps', 'idfuel_pump', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'employee', 'idemployee', 'employees_open', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xCodProveedor', 'supplier', 'codproveedor', 'proveedores', 'codproveedor', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdTarjeta', 'card', 'idtarjeta', 'tarjetas', 'idtarjeta', 'nombre');
        $this->addFilterPeriod($viewName, 'porFecha', 'refueling-date', 'fecha');

        $esDepositoLleno = [
            ['code' => '1', 'description' => 'full-tank-yes'],
            ['code' => '0', 'description' => 'full-tank-no'],
        ];
        $this->addFilterSelect($viewName, 'esDepositoLleno', 'full-tank-all', 'deposito_lleno', $esDepositoLleno);
    }

    protected function createViewFuel_pump($viewName = 'ListFuelPump')
    {
        $this->addView($viewName, 'FuelPump', 'internal-spout', 'fas fa-thumbtack');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'name', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);
    }
}