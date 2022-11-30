<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListServiceRegular extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Servicios regulares';
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
    }

    protected function createViewServiceRegular($viewName = 'ListServiceRegular')
    {
        $this->addView($viewName, 'ServiceRegular', 'Servicios regulares', 'fas fa-book-open');
        $this->addSearchFields($viewName, ['cod_servicio', 'nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['cod_servicio'], 'Código');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $this->addFilterCheckbox($viewName, 'lunes', 'Lunes', 'lunes');
        $this->addFilterCheckbox($viewName, 'martes', 'Martes', 'martes');
        $this->addFilterCheckbox($viewName, 'miercoles', 'Miercoles', 'miercoles');
        $this->addFilterCheckbox($viewName, 'jueves', 'Jueves', 'jueves');
        $this->addFilterCheckbox($viewName, 'viernes', 'Viernes', 'viernes');
        $this->addFilterCheckbox($viewName, 'sabado', 'Sábado', 'sabado');
        $this->addFilterCheckbox($viewName, 'domingo', 'Domingo', 'domingo');

        $aceptados = [
            ['code' => '1', 'description' => 'Aceptados = SI'],
            ['code' => '0', 'description' => 'Aceptados = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloAceptados', 'Aceptados = TODOS', 'aceptado', $aceptados);

        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $crearFtraSN = [
            ['code' => '1', 'description' => 'Facturable = SI'],
            ['code' => '0', 'description' => 'Facturable = NO'],
        ];
        $this->addFilterSelect($viewName, 'crearFtra', 'Crear ftra. = TODOS', 'facturar_SN', $crearFtraSN);

        $facturarAgrupandoSN = [
            ['code' => '1', 'description' => 'Ftra.agrupando = SI'],
            ['code' => '0', 'description' => 'Ftra.agrupando = NO'],
        ];
        $this->addFilterSelect($viewName, 'facturarAgrupando', 'Ftra.agrupando = TODOS', 'facturar_agrupando', $facturarAgrupandoSN);

        $combinadosSN = [
            ['code' => '1', 'description' => 'Combinado = SI'],
            ['code' => '0', 'description' => 'Combinado = NO'],
        ];
        $this->addFilterSelect($viewName, 'xCombinadoSN', 'Combinado = TODOS', 'combinadoSN', $combinadosSN);

        $this->addFilterAutocomplete($viewName, 'xCodCliente', 'Cliente', 'codcliente', 'clientes', 'codcliente', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdvehicle_type', 'Vehículo - tipo', 'idvehicle_type', 'vehiculos', 'idvehicle_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdhelper', 'Monitor/a', 'idhelper', 'helpers', 'idhelper', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdservice_type', 'Servicio - tipo', 'idservice_type', 'service_types', 'idservice_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdempresa', 'Empresa', 'idempresa', 'empresas', 'idempresa', 'nombre');
    }

    protected function createViewServiceRegularCombination($viewName = 'ListServiceRegularCombination')
    {
        $this->addView($viewName, 'ServiceRegularCombination', 'Combinaciones', 'fas fa-briefcase');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdDriver', 'driver', 'iddriver', 'drivers', 'iddriver', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdVehicle', 'vehicle', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
    }

    protected function createViewServiceRegularCombinationServ($viewName = 'ListServiceRegularCombinationServ')
    {
        $this->addView($viewName, 'ServiceRegularCombinationServ', 'Combinaciones - Servicios', 'fas fa-cogs');
        $this->addOrderBy($viewName, ['idservice_regular_combination', 'idservice_regular'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdDriver', 'driver', 'iddriver', 'drivers', 'iddriver', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdVehicle', 'vehicle', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdservice_regular_combination', 'combination-service', 'idservice_regular_combination', 'service_regular_combinations', 'idservice_regular_combination', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdservice_regular', 'service-regular', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
    }

    protected function createViewServiceRegularItinerary($viewName = 'ListServiceRegularItinerary')
    {
        $this->addView($viewName, 'ServiceRegularItinerary', 'Serv. regulares - Itinerarios', 'fas fa-road');
        $this->addOrderBy($viewName, ['idservice_regular', 'orden'], 'Por itinerario', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdservice_regular', 'Servicio regular', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdstop', 'Parada', 'idstop', 'stops', 'idstop', 'nombre');
    }

    protected function createViewServiceRegularPeriod($viewName = 'ListServiceRegularPeriod')
    {
        $this->addView($viewName, 'ServiceRegularPeriod', 'Periodos', 'fas fa-calendar-alt');
        $this->addOrderBy($viewName, ['idservice_regular', 'fecha_desde', 'fecha_hasta', 'hora_desde', 'hora_hasta'], 'Por periodo', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $salidaDesdeNave = [
            ['code' => '1', 'description' => 'Salida desde nave = SI'],
            ['code' => '0', 'description' => 'Salida desde nave = NO'],
        ];
        $this->addFilterSelect($viewName, 'salidaDesdeNave', 'Salida desde nave = TODOS', 'salida_desde_nave_sn', $salidaDesdeNave);

        $this->addFilterAutocomplete($viewName, 'xIdservice_regular', 'Servicio regular', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
        $this->addFilterPeriod($viewName, 'porFechaInicio', 'F.inicio', 'fecha_desde');
        $this->addFilterPeriod($viewName, 'porFechaFin', 'F.fin', 'fecha_hasta');
    }
}