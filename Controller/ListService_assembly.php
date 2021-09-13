<?php
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListService_assembly extends ListController {
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Empleados
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pageData['menu'] = 'OpenServBus';
//        $pageData['submenu'] = 'Montaje de servicios';
        $pageData['title'] = 'Montaje de servicios';
        
        $pageData['icon'] = 'fas fa-business-time';

        return $pageData;
    }
    
    protected function createViews() {
        $this->createViewAssembly();
    }
    
    protected function createViewAssembly($viewName = 'ListService_assembly')
    {
        $this->addView($viewName, 'Service_assembly');
        
        $this->addSearchFields($viewName, ['nombre']);
        
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['codcliente'], 'Cliente');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        
        // Filtro de TIPO SELECT para filtrar por registros activos (SI, NO, o TODOS)
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);        

        // Filtro de TIPO SELECT para filtrar por SERVICIOS REGULARES FACTURABLES (SI, NO, o TODOS)
        $crearFtraSN = [
            ['code' => '1', 'description' => 'Facturable = SI'],
            ['code' => '0', 'description' => 'Facturable = NO'],
        ];
        $this->addFilterSelect($viewName, 'crearFtra', 'Crear ftra. = TODOS', 'facturar_SN', $crearFtraSN);        

        // Filtro de TIPO SELECT para filtrar por SERVICIOS REGULARES facturar agrupando (SI, NO, o TODOS)
        $facturarAgrupandoSN = [
            ['code' => '1', 'description' => 'Ftra.agrupando = SI'],
            ['code' => '0', 'description' => 'Ftra.agrupando = NO'],
        ];
        $this->addFilterSelect($viewName, 'facturarAgrupando', 'Ftra.agrupando = TODOS', 'facturar_agrupando', $facturarAgrupandoSN);        

        $this->addFilterAutocomplete($viewName, 'xCodCliente', 'Cliente', 'codcliente', 'clientes', 'codcliente', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdvehicle_type', 'Vehículo - tipo', 'idvehicle_type', 'vehiculos', 'idvehicle_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdhelper', 'Monitor/a', 'idhelper', 'helpers', 'idhelper', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdservice_type', 'Servicio - tipo', 'idservice_type', 'service_types', 'idservice_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdempresa', 'Empresa', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdservice', 'service-discretionary', 'idservice', 'services', 'idservice', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdserviceRegular', 'service-regular', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
    }
    
}
