<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

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
        $pageData['title'] = 'Serv. regular';
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
        $this->addListView($viewName, 'ServiceRegularCombinationServ', 'Combinaciones', 'fas fa-briefcase');
        $this->views[$viewName]->addOrderBy(['idservice_regular_combination', 'idservice_regular'], 'Nombre', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->views[$viewName]->addFilterAutocomplete('xIdDriver', 'driver', 'iddriver', 'drivers', 'iddriver', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdVehicle', 'vehicle', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdVehicle', 'vehicle', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdservice_regular_combination', 'combination-service', 'idservice_regular_combination', 'service_regular_combinations', 'idservice_regular_combination', 'nombre');
    }

    protected function createViewItineraries($viewName = 'ListServiceRegularItinerary')
    {
        $this->addListView($viewName, 'ServiceRegularItinerary', 'Itinerarios', 'fas fa-road');
        $this->views[$viewName]->addOrderBy(['idservice_regular', 'orden'], 'Por itinerario', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->views[$viewName]->addFilterAutocomplete('xIdservice_regular', 'Servicio regular', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdstop', 'Parada', 'idstop', 'stops', 'idstop', 'nombre');
    }

    protected function createViewPeriods($viewName = 'ListServiceRegularPeriod')
    {
        $this->addListView($viewName, 'ServiceRegularPeriod', 'Periodos', 'fas fa-calendar-day');
        $this->views[$viewName]->addOrderBy(['idservice_regular', 'fecha_desde', 'fecha_hasta', 'hora_desde', 'hora_hasta'], 'Por periodo', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);

        $salidaDesdeNave = [
            ['code' => '1', 'description' => 'Salida desde nave = SI'],
            ['code' => '0', 'description' => 'Salida desde nave = NO'],
        ];
        $this->views[$viewName]->addFilterSelect('salidaDesdeNave', 'Salida desde nave = TODOS', 'salida_desde_nave_sn', $salidaDesdeNave);

        $this->views[$viewName]->addFilterAutocomplete('xIdservice_regular', 'Servicio regular', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
        $this->views[$viewName]->addFilterPeriod('porFechaInicio', 'F.inicio', 'fecha_desde');
        $this->views[$viewName]->addFilterPeriod('porFechaFin', 'F.fin', 'fecha_hasta');
    }

    protected function createViewValuations($viewName = 'ListServiceRegularValuation')
    {
        $this->addListView($viewName, 'ServiceRegularValuation', 'Valoraciones', 'fas fa-dollar-sign');

        $this->views[$viewName]->addOrderBy(['idservice_regular', 'orden'], 'Por valoración', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->views[$viewName]->addFilterAutocomplete('xIdservice_regular', 'Servicio regular', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdservice_valuation_type', 'Conceptos - valoración', 'idservice_valuation_type', 'service_valuation_types', 'idservice_valuation_type', 'nombre');
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
            case 'ListServiceRegularPeriod':
                $idservice_regular = $this->getViewModelValue($mvn, 'idservice_regular');
                $where = [new DatabaseWhere('idservice_regular', $idservice_regular)];
                $view->loadData('', $where);
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }
}