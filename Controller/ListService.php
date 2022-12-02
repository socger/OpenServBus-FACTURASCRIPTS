<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListService extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'discretionary-services';
        $pageData['icon'] = 'fas fa-book-reader';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewService();
        $this->createViewServiceItinerary();
        $this->createViewServiceValuation();
        $this->createViewServiceValuationType();
        $this->createViewStop();
    }

    protected function createViewService($viewName = 'ListService')
    {
        $this->addView($viewName, 'Service', 'services', 'fas fa-book-reader');
        $this->addSearchFields($viewName, ['idservicio', 'nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'name', 1);
        $this->addOrderBy($viewName, ['idservicio'], 'code');
        $this->addOrderBy($viewName, ['fecha_desde', 'fecha_hasta'], 'fstart-fend');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
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

        $this->addFilterAutocomplete($viewName, 'xCodCliente', 'client', 'codcliente', 'clientes', 'codcliente', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdvehicle_type', 'vehicle_type', 'idvehicle_type', 'vehiculos', 'idvehicle_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdservice_type', 'service-type', 'idservice_type', 'service_types', 'idservice_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdempresa', 'company', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->addFilterPeriod($viewName, 'porFechaInicio', 'date-start', 'fecha_desde');
        $this->addFilterPeriod($viewName, 'porFechaFin', 'date-end', 'fecha_hasta');
    }

    protected function createViewServiceItinerary($viewName = 'ListServiceItinerary')
    {
        $this->addView($viewName, 'ServiceItinerary', 'serv-discretionary-itinerary', 'fas fa-road');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['idservice', 'orden'], 'by-itinerary', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdservice', 'service-discretionary', 'idservice', 'services', 'idservice', 'nombre');
    }

    protected function createViewServiceValuation($viewName = 'ListServiceValuation')
    {
        $this->addView($viewName, 'ServiceValuation', 'ratings', 'fas fa-dollar-sign');
        $this->views[$viewName]->addOrderBy(['idservice', 'orden'], 'by-rating', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);

        $this->views[$viewName]->addFilterAutocomplete('xIdservice', 'service-discretionary', 'idservice', 'services', 'idservice', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdservice_valuation_type', 'concepts-valuation', 'idservice_valuation_type', 'service_valuation_types', 'idservice_valuation_type', 'nombre');
    }

    protected function createViewServiceValuationType($viewName = 'ListServiceValuationType')
    {
        $this->addView($viewName, 'ServiceValuationType', 'concepts-valuations', 'fas fa-hand-holding-usd');
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

    protected function createViewStop($viewName = 'ListStop')
    {
        $this->addView($viewName, 'Stop', 'stops', 'fas fa-stopwatch');
        $this->addSearchFields($viewName, ['nombre', 'ciudad', 'provincia', 'codpostal', 'direccion']);
        $this->addOrderBy($viewName, ['nombre'], 'name', 1);
        $this->addOrderBy($viewName, ['provincia', 'ciudad', 'codpostal', 'direccion'], 'province-city-postal-code-address');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);
    }
}