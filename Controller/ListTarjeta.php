<?php
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListTarjeta extends ListController {
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Empleados
    public function getPageData(): array {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Tarjetas';
        $pageData['icon'] = 'fab fa-cc-mastercard';
        return $pageData;
    }
    
    protected function createViews() {
        $this->createViewTarjeta();
    }
    
    protected function createViewTarjeta($viewName = 'ListTarjeta')
    {
        $this->addView($viewName, 'Tarjeta', 'Tarjetas', 'fas fa-credit-card');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        
        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);        

        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'Empresa', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'Empleado', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdDriver', 'Conductor', 'iddriver', 'drivers', 'iddriver', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdTarjeta_Type', 'Tipo tarjeta', 'idtarjeta_type', 'tarjeta_types', 'idtarjeta_type', 'nombre');

        $esDePago = [
            ['code' => '1', 'description' => 'De pago = SI'],
            ['code' => '0', 'description' => 'De pago = NO'],
        ];
        $this->addFilterSelect($viewName, 'esDepago', 'De pago = TODO', 'de_pago', $esDePago);
    }
}