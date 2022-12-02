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
use FacturaScripts\Plugins\OpenServBus\Model\Driver;

class ListServiceRegular extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'regular-services';
        $pageData['icon'] = 'fas fa-book-open';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewServiceRegular();
        $this->createViewServiceRegularCombination();
        $this->createViewServiceRegularCombinationServ();
        $this->createViewServiceRegularPeriod();
        $this->createViewServiceRegularItinerary();
        $this->createViewValuations();
    }

    protected function createViewServiceRegular($viewName = 'ListServiceRegular')
    {
        $this->addView($viewName, 'ServiceRegular', 'regular-services', 'fas fa-book-open');
        $this->addSearchFields($viewName, ['cod_servicio', 'nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'name', 1);
        $this->addOrderBy($viewName, ['cod_servicio'], 'code');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $this->addFilterCheckbox($viewName, 'lunes', 'monday', 'lunes');
        $this->addFilterCheckbox($viewName, 'martes', 'tuesday', 'martes');
        $this->addFilterCheckbox($viewName, 'miercoles', 'wednesday', 'miercoles');
        $this->addFilterCheckbox($viewName, 'jueves', 'thursday', 'jueves');
        $this->addFilterCheckbox($viewName, 'viernes', 'friday', 'viernes');
        $this->addFilterCheckbox($viewName, 'sabado', 'saturday', 'sabado');
        $this->addFilterCheckbox($viewName, 'domingo', 'sunday', 'domingo');

        $aceptados = [
            ['code' => '1', 'description' => 'accepted-yes'],
            ['code' => '0', 'description' => 'accepted-no'],
        ];
        $this->addFilterSelect($viewName, 'soloAceptados', 'accepted-all', 'aceptado', $aceptados);

        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $crearFtraSN = [
            ['code' => '1', 'description' => 'billable-yes'],
            ['code' => '0', 'description' => 'billable-no'],
        ];
        $this->addFilterSelect($viewName, 'crearFtra', 'billable-all', 'facturar_SN', $crearFtraSN);

        $facturarAgrupandoSN = [
            ['code' => '1', 'description' => 'grouping-invoice-yes'],
            ['code' => '0', 'description' => 'grouping-invoice-no'],
        ];
        $this->addFilterSelect($viewName, 'facturarAgrupando', 'grouping-invoice-all', 'facturar_agrupando', $facturarAgrupandoSN);

        $combinadosSN = [
            ['code' => '1', 'description' => 'combined-yes'],
            ['code' => '0', 'description' => 'combined-no'],
        ];
        $this->addFilterSelect($viewName, 'xCombinadoSN', 'combined-all', 'combinadoSN', $combinadosSN);

        $this->addFilterAutocomplete($viewName, 'xCodCliente', 'client', 'codcliente', 'clientes', 'codcliente', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdvehicle_type', 'vehicle-type', 'idvehicle_type', 'vehiculos', 'idvehicle_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdservice_type', 'service-type', 'idservice_type', 'service_types', 'idservice_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdempresa', 'company', 'idempresa', 'empresas', 'idempresa', 'nombre');
    }

    protected function createViewServiceRegularCombination($viewName = 'ListServiceRegularCombination')
    {
        $this->addView($viewName, 'ServiceRegularCombination', 'combinations', 'fas fa-briefcase');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'name', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdVehicle', 'vehicle', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
    }

    protected function createViewServiceRegularCombinationServ($viewName = 'ListServiceRegularCombinationServ')
    {
        $this->addView($viewName, 'ServiceRegularCombinationServ', 'combinations-services', 'fas fa-cogs');
        $this->addOrderBy($viewName, ['idservice_regular_combination', 'idservice_regular'], 'name', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdVehicle', 'vehicle', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdservice_regular_combination', 'combination-service', 'idservice_regular_combination', 'service_regular_combinations', 'idservice_regular_combination', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdservice_regular', 'service-regular', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
    }

    protected function createViewServiceRegularItinerary($viewName = 'ListServiceRegularItinerary')
    {
        $this->addView($viewName, 'ServiceRegularItinerary', 'serv-regulars-itineraries', 'fas fa-road');
        $this->addOrderBy($viewName, ['idservice_regular', 'orden'], 'by-itinerary', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdservice_regular', 'regular-service', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdstop', 'Parada', 'idstop', 'stops', 'idstop', 'nombre');
    }

    protected function createViewServiceRegularPeriod($viewName = 'ListServiceRegularPeriod')
    {
        $this->addView($viewName, 'ServiceRegularPeriod', 'periods', 'fas fa-calendar-alt');
        $this->addOrderBy($viewName, ['idservice_regular', 'fecha_desde', 'fecha_hasta', 'hora_desde', 'hora_hasta'], 'by-period', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $salidaDesdeNave = [
            ['code' => '1', 'description' => 'departure-from-ship-yes'],
            ['code' => '0', 'description' => 'departure-from-ship-no'],
        ];
        $this->addFilterSelect($viewName, 'salidaDesdeNave', 'departure-from-ship-all', 'salida_desde_nave_sn', $salidaDesdeNave);

        $this->addFilterAutocomplete($viewName, 'xIdservice_regular', 'regular-service', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
        $this->addFilterPeriod($viewName, 'porFechaInicio', 'date-start', 'fecha_desde');
        $this->addFilterPeriod($viewName, 'porFechaFin', 'date-end', 'fecha_hasta');
    }

    protected function createViewValuations($viewName = 'ListServiceRegularValuation')
    {
        $this->addView($viewName, 'ServiceRegularValuation', 'ratings', 'fas fa-dollar-sign');

        $this->views[$viewName]->addOrderBy(['idservice_regular', 'orden'], 'by-rating', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);

        $this->views[$viewName]->addFilterAutocomplete('xIdservice_regular', 'regular-service', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdservice_valuation_type', 'concepts-valuation', 'idservice_valuation_type', 'service_valuation_types', 'idservice_valuation_type', 'nombre');
    }

    protected function loadData($viewName, $view)
    {
        $mvn = $this->getMainViewName();
        switch ($viewName) {
            case 'ListServiceRegularCombination':
            case 'ListServiceRegularCombinationServ':
                $this->loadValuesSelectDrivers($mvn, 'usual-driver');
            default:
                $view->loadData();
                break;
        }
    }

    protected function loadValuesSelectDrivers(string $mvn, string $columnName)
    {
        $column = $this->views[$mvn]->columnForName($columnName);
        if($column && $column->widget->getType() === 'select') {
            // obtenemos los conductores
            $customValues = [];
            $driversModel = new Driver();
            foreach ($driversModel->all([], [], 0, 0) as $driver) {
                $customValues[] = [
                    'value' => $driver->iddriver,
                    'title' => $driver->nombre,
                ];
            }
            $column->widget->setValuesFromArray($customValues, false, true);
        }
    }
}