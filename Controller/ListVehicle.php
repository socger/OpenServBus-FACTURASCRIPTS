<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListVehicle extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'vehicles';
        $pageData['icon'] = 'fas fa-bus-alt';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewVehicle();
        $this->createViewVehicleEquipament();
    }

    protected function createViewVehicle($viewName = 'ListVehicle')
    {
        $this->addView($viewName, 'Vehicle', 'vehicles', 'fas fa-bus-alt');
        $this->addSearchFields($viewName, ['cod_vehicle', 'name', 'matricula']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['cod_vehicle'], 'code');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $this->addFilterCheckbox($viewName, 'solo_Colaboradores', 'collaborators-only', 'idcollaborator', 'IS NOT', null);
        $this->addFilterCheckbox($viewName, 'solo_VehiculosNtros', 'our-vehicles', 'idempresa', 'IS NOT', null);

        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'company', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdGarage', 'garage', 'idgarage', 'garages', 'idgarage', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xidfuel_type', 'fuel-type', 'idfuel_type', 'fuel_types', 'idfuel_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdCollaborator', 'collaborator', 'idcollaborator', 'collaborators', 'idcollaborator', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdvehicle_type', 'vehicle-type', 'idvehicle_type', 'vehicle_types', 'idvehicle_type', 'nombre');
    }

    protected function createViewVehicleEquipament($viewName = 'ListVehicleEquipament')
    {
        $this->addView($viewName, 'VehicleEquipament', 'equipament', 'fas fa-bus-alt');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['idvehicle', 'idvehicle_equipament_type'], 'vehicle-equipment-plus', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdVehicle', 'vehicle', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdVehicle_equipament_type', 'equipment-type', 'idvehicle_equipament_type', 'vehicle_equipament_types', 'idvehicle_equipament_type', 'nombre');
    }
}