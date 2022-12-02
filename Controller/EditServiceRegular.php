<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Plugins\OpenServBus\Model\Driver;
use FacturaScripts\Plugins\OpenServBus\Model\Helper;

class EditServiceRegular extends EditController
{
    public function getModelClassName(): string
    {
        return 'ServiceRegular';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'regular-service';
        $pageData['icon'] = 'fas fa-book-open';
        return $pageData;
    }

    protected function createViews()
    {
        parent::createViews();
        $this->createViewContacts();
        $this->createViewPeriods();
        $this->createViewItineraries();
        $this->createViewCombinationServs();
        $this->createViewValuations();
        $this->setTabsPosition('top');
    }

    protected function createViewContacts(string $viewName = 'EditDireccionContacto')
    {
        $this->addEditListView($viewName, 'Contacto', 'addresses-and-contacts', 'fas fa-address-book');
        $this->views[$viewName]->setInLine(true);
    }

    protected function createViewCombinationServs($viewName = 'ListServiceRegularCombinationServ')
    {
        $this->addListView($viewName, 'ServiceRegularCombinationServ', 'combinations', 'fas fa-briefcase');
        $this->views[$viewName]->addOrderBy(['idservice_regular_combination', 'idservice_regular'], 'name', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);

        $this->views[$viewName]->addFilterAutocomplete('xIdVehicle', 'vehicle', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdVehicle', 'vehicle', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdservice_regular_combination', 'combination-service', 'idservice_regular_combination', 'service_regular_combinations', 'idservice_regular_combination', 'nombre');
    }

    protected function createViewItineraries($viewName = 'ListServiceRegularItinerary')
    {
        $this->addListView($viewName, 'ServiceRegularItinerary', 'itineraries', 'fas fa-road');
        $this->views[$viewName]->addOrderBy(['idservice_regular', 'orden'], 'by-itinerary', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);

        $this->views[$viewName]->addFilterAutocomplete('xIdservice_regular', 'regular-service', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdstop', 'stop', 'idstop', 'stops', 'idstop', 'nombre');
    }

    protected function createViewPeriods($viewName = 'ListServiceRegularPeriod')
    {
        $this->addListView($viewName, 'ServiceRegularPeriod', 'periods', 'fas fa-calendar-day');
        $this->views[$viewName]->addOrderBy(['idservice_regular', 'fecha_desde', 'fecha_hasta', 'hora_desde', 'hora_hasta'], 'by-period', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);

        $salidaDesdeNave = [
            ['code' => '1', 'description' => 'departure-from-ship-yes'],
            ['code' => '0', 'description' => 'departure-from-ship-no'],
        ];
        $this->views[$viewName]->addFilterSelect('salidaDesdeNave', 'departure-from-ship-all', 'salida_desde_nave_sn', $salidaDesdeNave);

        $this->views[$viewName]->addFilterAutocomplete('xIdservice_regular', 'regular-service', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
        $this->views[$viewName]->addFilterPeriod('porFechaInicio', 'date-start', 'fecha_desde');
        $this->views[$viewName]->addFilterPeriod('porFechaFin', 'date-end', 'fecha_hasta');
    }

    protected function createViewValuations($viewName = 'ListServiceRegularValuation')
    {
        $this->addListView($viewName, 'ServiceRegularValuation', 'ratings', 'fas fa-dollar-sign');

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
            case 'EditDireccionContacto':
                $codcliente = $this->getViewModelValue($mvn, 'codcliente');
                $where = [new DatabaseWhere('codcliente', $codcliente)];
                $view->loadData('', $where);
                break;

            case 'ListServiceRegularValuation':
            case 'ListServiceRegularItinerary':
            case 'ListServiceRegularCombinationServ':
            $this->loadValuesSelectDrivers($mvn, 'usual-driver');
            case 'ListServiceRegularPeriod':
                $idservice_regular = $this->getViewModelValue($mvn, 'idservice_regular');
                $where = [new DatabaseWhere('idservice_regular', $idservice_regular)];
                $view->loadData('', $where);
                break;

            case $mvn:
                parent::loadData($viewName, $view);
                $this->loadValuesSelectHelpers($mvn);
                $this->loadValuesSelectDrivers($mvn, 'usual-driver-1');
                $this->loadValuesSelectDrivers($mvn, 'usual-driver-2');
                $this->loadValuesSelectDrivers($mvn, 'usual-driver-3');
                break;

            default:
                parent::loadData($viewName, $view);
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

    protected function loadValuesSelectHelpers(string $mvn)
    {
        $column = $this->views[$mvn]->columnForName('helper');
        if($column && $column->widget->getType() === 'select') {
            // obtenemos los monitores
            $customValues = [];
            $helpersModel = new Helper();
            foreach ($helpersModel->all([], [], 0, 0) as $helper) {
                $customValues[] = [
                    'value' => $helper->idhelper,
                    'title' => $helper->nombre,
                ];
            }
            $column->widget->setValuesFromArray($customValues, false, true);
        }
    }
}