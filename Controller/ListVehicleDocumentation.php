<?php
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListVehicleDocumentation extends ListController {
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Empleados
    public function getPageData(): array {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Documentación';
        $pageData['icon'] = 'far fa-file-pdf';
        return $pageData;
    }
    
    protected function createViews() {
        $this->createViewVehicleDocumentation();
        $this->createViewEmployeeDocumentation();
    }

    protected function createViewEmployeeDocumentation($viewName = 'ListEmployeeDocumentation')
    {
        $this->addView($viewName, 'EmployeeDocumentation', 'employee', 'fas fa-user');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['iddocumentation_type', 'nombre'], 'Tipo Doc. + nombre');
        $this->addOrderBy($viewName, ['fecha_caducidad'], 'F. caducidad.');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'Empleado', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xiddocumentation_type', 'Documentación - tipo', 'iddocumentation_type', 'documentation_types', 'iddocumentation_type', 'nombre');
        $this->addFilterPeriod($viewName, 'porFechaCaducidad', 'Fecha de caducidad', 'fecha_caducidad');
    }
    
    protected function createViewVehicleDocumentation($viewName = 'ListVehicleDocumentation')
    {
        $this->addView($viewName, 'VehicleDocumentation', 'Vehículos', 'far fa-file-pdf');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['idvehicle', 'nombre'], 'Vehículo + Tipo Doc.');
        $this->addOrderBy($viewName, ['fecha_caducidad'], 'F. caducidad.');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        
        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);        

        $this->addFilterAutocomplete($viewName, 'xIdVehicle', 'Vehículo', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xiddocumentation_type', 'Documentación - tipo', 'iddocumentation_type', 'documentation_types', 'iddocumentation_type', 'nombre');
        $this->addFilterPeriod($viewName, 'porFechaCaducidad', 'Fecha de caducidad', 'fecha_caducidad');
    }
}
