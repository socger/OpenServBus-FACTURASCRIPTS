<?php
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListService extends ListController {
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Empleados
    public function getPageData(): array {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Servicios discrecionales';
        $pageData['icon'] = 'fas fa-book-reader';
        return $pageData;
    }
    
    protected function createViews() {
        $this->createViewService();
        $this->createViewServiceItinerary();
        $this->createViewServiceValuationType();
        $this->createViewStop();
    }
    
    protected function createViewService($viewName = 'ListService')
    {
        $this->addView($viewName, 'Service', 'Servicios', 'fas fa-book-reader');
        $this->addSearchFields($viewName, ['idservicio', 'nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['idservicio'], 'Código');
        $this->addOrderBy($viewName, ['fecha_desde', 'fecha_hasta'], 'F.inicio + F.fin');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        
        // Filtros
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

        $this->addFilterAutocomplete($viewName, 'xCodCliente', 'Cliente', 'codcliente', 'clientes', 'codcliente', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdvehicle_type', 'Vehículo - tipo', 'idvehicle_type', 'vehiculos', 'idvehicle_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdhelper', 'Monitor/a', 'idhelper', 'helpers', 'idhelper', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdservice_type', 'Servicio - tipo', 'idservice_type', 'service_types', 'idservice_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdempresa', 'Empresa', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->addFilterPeriod($viewName, 'porFechaInicio', 'F.inicio', 'fecha_desde');
        $this->addFilterPeriod($viewName, 'porFechaFin', 'F.fin', 'fecha_hasta');
    }

    protected function createViewServiceItinerary($viewName = 'ListServiceItinerary')
    {
        $this->addView($viewName, 'ServiceItinerary', 'Serv. discrecionales - Itinerarios', 'fas fa-road');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['idservice', 'orden'], 'Por itinerario', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdservice', 'Serv. discrecional', 'idservice', 'services', 'idservice', 'nombre');
    }

    protected function createViewServiceValuationType($viewName = 'ListServiceValuationType')
    {
        $this->addView($viewName, 'ServiceValuationType', 'Conceptos - valoraciones', 'fas fa-hand-holding-usd');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);
    }

    protected function createViewStop($viewName = 'ListStop')
    {
        $this->addView($viewName, 'Stop', 'Paradas', 'fas fa-stopwatch');
        $this->addSearchFields($viewName, ['nombre', 'ciudad', 'provincia', 'codpostal', 'direccion']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['provincia', 'ciudad', 'codpostal', 'direccion'], 'Provincia+Ciudad+C.Postal+Dirección');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);
    }
}