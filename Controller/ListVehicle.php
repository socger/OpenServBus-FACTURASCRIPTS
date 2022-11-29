<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListVehicle extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Vehículos';
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
        $this->addView($viewName, 'Vehicle', 'Vehículos', 'fas fa-bus-alt');
        $this->addSearchFields($viewName, ['cod_vehicle', 'nombre', 'matricula']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['cod_vehicle'], 'Código');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $this->addFilterCheckbox($viewName, 'solo_Colaboradores', 'Ver sólo colaboradores', 'idcollaborator', 'IS NOT', null);
        $this->addFilterCheckbox($viewName, 'solo_VehiculosNtros', 'Ver sólo vehículos nuestros', 'idempresa', 'IS NOT', null);

        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'Empresa', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdGarage', 'Cochera', 'idgarage', 'garages', 'idgarage', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xidfuel_type', 'T.Combustible', 'idfuel_type', 'fuel_types', 'idfuel_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdCollaborator', 'Colaborador', 'idcollaborator', 'collaborators', 'idcollaborator', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdvehicle_type', 'Tipo vehículo', 'idvehicle_type', 'vehicle_types', 'idvehicle_type', 'nombre');
    }

    protected function createViewVehicleEquipament($viewName = 'ListVehicleEquipament')
    {
        $this->addView($viewName, 'VehicleEquipament', 'Equipamiento', 'fas fa-bus-alt');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['idvehicle', 'idvehicle_equipament_type'], 'Vehículo + Equipamiento', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdVehicle', 'Vehículo', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdVehicle_equipament_type', 'Equipamiento - Tipo', 'idvehicle_equipament_type', 'vehicle_equipament_types', 'idvehicle_equipament_type', 'nombre');
    }
}