<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListVehicleDocumentation extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'documentation';
        $pageData['icon'] = 'far fa-file-pdf';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewVehicleDocumentation();
        $this->createViewEmployeeDocumentation();
    }

    protected function createViewEmployeeDocumentation($viewName = 'ListEmployeeDocumentation')
    {
        $this->addView($viewName, 'EmployeeDocumentation', 'employee', 'fas fa-user');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'name', 1);
        $this->addOrderBy($viewName, ['iddocumentation_type', 'nombre'], 'doctype-name');
        $this->addOrderBy($viewName, ['fecha_caducidad'], 'date-expiration');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'employee', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xiddocumentation_type', 'documentation-type', 'iddocumentation_type', 'documentation_types', 'iddocumentation_type', 'nombre');
        $this->addFilterPeriod($viewName, 'porFechaCaducidad', 'date-expiration', 'fecha_caducidad');
    }

    protected function createViewVehicleDocumentation($viewName = 'ListVehicleDocumentation')
    {
        $this->addView($viewName, 'VehicleDocumentation', 'vehicles', 'far fa-file-pdf');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'name', 1);
        $this->addOrderBy($viewName, ['idvehicle', 'nombre'], 'vehicle-type-doc');
        $this->addOrderBy($viewName, ['fecha_caducidad'], 'date-expiration');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdVehicle', 'vehicle', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xiddocumentation_type', 'documentation-type', 'iddocumentation_type', 'documentation_types', 'iddocumentation_type', 'nombre');
        $this->addFilterPeriod($viewName, 'porFechaCaducidad', 'date-expiration', 'fecha_caducidad');
    }
}