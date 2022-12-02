<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\PanelController;

class ConfigOpenServBus extends PanelController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'admin';
        $pageData['title'] = 'OpenServBus';
        $pageData['icon'] = 'fas fa-bus';
        return $pageData;
    }

    protected function createViews()
    {
        $this->setTemplate('EditSettings');
        $this->createViewDocumentationType();
        $this->createViewEmployeeContractType();
        $this->createViewFuelType();
        $this->createViewServiceType();
        $this->createViewTarjetaType();
        $this->createViewVehicleEquipamentType();
        $this->createViewVehicleType();
    }

    protected function createViewDocumentationType($viewName = 'ListDocumentationType')
    {
        $this->addListView($viewName, 'DocumentationType', 'documentation_type', 'fas fa-address-card');
        $this->views[$viewName]->addSearchFields(['nombre']);
        $this->views[$viewName]->addOrderBy(['nombre'], 'name', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $this->views[$viewName]->addFilterCheckbox('fechacaducidad_obligarla', 'Obligar-F.Caducidad', 'fechacaducidad_obligarla');

        $fecha_caducidad = [
            ['code' => '1', 'description' => 'Obligar F.Caducidad = SI'],
            ['code' => '0', 'description' => 'Obligar F.Caducidad = NO'],
        ];
        $this->views[$viewName]->addFilterSelect('soloFechaCaducidad', 'Obligar F.Caducidad = TODOS', 'fechacaducidad_obligarla', $fecha_caducidad);

        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);
    }

    protected function createViewEmployeeContractType($viewName = 'ListEmployeeContractType')
    {
        $this->addListView($viewName, 'EmployeeContractType', 'employee_contract_type', 'fas fa-file-signature');
        $this->views[$viewName]->addSearchFields(['nombre']);
        $this->views[$viewName]->addOrderBy(['nombre'], 'name', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);
    }

    protected function createViewFuelType($viewName = 'ListFuelType')
    {
        $this->addListView($viewName, 'FuelType', 'fuel_type', 'fas fa-gas-pump');
        $this->views[$viewName]->addSearchFields(['nombre']);
        $this->views[$viewName]->addOrderBy(['nombre'], 'name', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);
    }

    protected function createViewServiceType($viewName = 'ListServiceType')
    {
        $this->addListView($viewName, 'ServiceType', 'service-type', 'fas fa-dolly');
        $this->views[$viewName]->addSearchFields(['nombre']);
        $this->views[$viewName]->addOrderBy(['nombre'], 'name', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);
    }

    protected function createViewTarjetaType($viewName = 'ListTarjetaType')
    {
        $this->addListView($viewName, 'TarjetaType', 'tarjeta_type', 'far fa-credit-card');
        $this->views[$viewName]->addSearchFields(['nombre']);
        $this->views[$viewName]->addOrderBy(['nombre'], 'name', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);

        $esDePago = [
            ['code' => '1', 'description' => 'De pago = SI'],
            ['code' => '0', 'description' => 'De pago = NO'],
        ];
        $this->views[$viewName]->addFilterSelect('esDepago', 'De pago = TODO', 'de_pago', $esDePago);
    }

    protected function createViewVehicleEquipamentType($viewName = 'ListVehicleEquipamentType')
    {
        $this->addListView($viewName, 'VehicleEquipamentType', 'vehicle_equipament_type', 'fas fa-wheelchair');
        $this->views[$viewName]->addSearchFields(['nombre']);
        $this->views[$viewName]->addOrderBy(['nombre'], 'name', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);
    }

    protected function createViewVehicleType($viewName = 'ListVehicleType')
    {
        $this->addListView($viewName, 'VehicleType', 'vehicle_type', 'fas fa-tractor');
        $this->views[$viewName]->addSearchFields(['nombre']);
        $this->views[$viewName]->addOrderBy(['nombre'], 'name', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);
    }

    protected function loadData($viewName, $view)
    {
        $this->hasData = true;

        switch ($viewName) {
            case 'ConfigOpenServBus':
                $view->loadData('openservbus');
                $view->model->name = 'openservbus';
                break;

            case 'ListDocumentationType':
            case 'ListEmployeeContractType':
            case 'ListFuelType':
            case 'ListServiceType':
            case 'ListTarjetaType':
            case 'ListVehicleEquipamentType':
            case 'ListVehicleType':
                $view->loadData();
                break;
        }
    }
}