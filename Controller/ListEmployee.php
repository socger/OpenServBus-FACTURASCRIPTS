<?php
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListEmployee extends ListController {
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Empleados
    public function getPageData(): array {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Empleados';
        $pageData['icon'] = 'far fa-id-card';
        return $pageData;
    }
    
    protected function createViews() {
        $this->createViewEmployee();
        $this->createViewEmployeeContract();
    }
    
    protected function createViewEmployee($viewName = 'ListEmployee')
    {
        $this->addView($viewName, 'Employee', 'Empleados', 'far fa-id-card');
        $this->addSearchFields($viewName, ['cod_employee', 'nombre','direccion']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['cod_employee'], 'Código');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        
        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);        

        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'Empresa', 'idempresa', 'empresas', 'idempresa', 'nombre');

        $esConductor = [
            ['code' => '1', 'description' => 'Conductor = SI'],
            ['code' => '0', 'description' => 'Conductor = NO'],
        ];
        $this->addFilterSelect($viewName, 'esConductor', 'Conductor = TODOS', 'driver_yn', $esConductor);        
    }

    protected function createViewEmployeeContract($viewName = 'ListEmployeeContract')
    {
        $this->addView($viewName, 'EmployeeContract', 'Contratos', 'fas fa-file-contract');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['fecha_inicio', 'fecha_fin'], 'F.inicio + F.fin.');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'Empresa', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'Empleado', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdemployee_contract_type', 'Contrato - tipo', 'idemployee_contract_type', 'employee_contract_types', 'idemployee_contract_type', 'nombre');
    }
}
