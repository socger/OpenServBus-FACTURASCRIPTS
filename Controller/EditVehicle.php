<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditVehicle extends EditController
{
    public function getModelClassName(): string
    {
        return 'Vehicle';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Vehículo';
        $pageData['icon'] = 'fas fa-bus-alt';
        return $pageData;
    }

    protected function createViews()
    {
        parent::createViews();
        $this->createViewVehicleEquipament();
        $this->createViewVehicleDocumentation();
        $this->setTabsPosition('top');
    }

    protected function createViewVehicleDocumentation($viewName = 'ListVehicleDocumentation')
    {
        $this->addListView($viewName, 'VehicleDocumentation', 'Documentación', 'far fa-file-pdf');
        $this->views[$viewName]->addSearchFields(['nombre']);
        $this->views[$viewName]->addOrderBy(['nombre'], 'Nombre', 1);
        $this->views[$viewName]->addOrderBy(['idvehicle', 'nombre'], 'Vehículo + Tipo Doc.');
        $this->views[$viewName]->addOrderBy(['fecha_caducidad'], 'F. caducidad.');
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);
        $this->views[$viewName]->addFilterAutocomplete('xIdVehicle', 'Vehículo', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xiddocumentation_type', 'Documentación - tipo', 'iddocumentation_type', 'documentation_types', 'iddocumentation_type', 'nombre');
        $this->views[$viewName]->addFilterPeriod('porFechaCaducidad', 'Fecha de caducidad', 'fecha_caducidad');
    }

    protected function createViewVehicleEquipament($viewName = 'ListVehicleEquipament')
    {
        $this->addListView($viewName, 'VehicleEquipament', 'Equipamiento', 'fab fa-accessible-icon');
        $this->views[$viewName]->addOrderBy(['idvehicle', 'idvehicle_equipament_type'], 'Vehículo + Equipamiento', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->views[$viewName]->addFilterAutocomplete('xIdVehicle', 'Vehículo', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdVehicle_equipament_type', 'Equipamiento - Tipo', 'idvehicle_equipament_type', 'vehicle_equipament_types', 'idvehicle_equipament_type', 'nombre');
    }

    protected function loadData($viewName, $view)
    {
        $mvn = $this->getMainViewName();
        switch ($viewName) {
            case 'ListVehicleDocumentation':
            case 'ListVehicleEquipament':
                $idvehicle = $this->getViewModelValue($mvn, 'idvehicle');
                $where = [new DatabaseWhere('idvehicle', $idvehicle)];
                $view->loadData('', $where);
                break;

            case $mvn:
                parent::loadData($viewName, $view);
                $this->PonerEnVistaLaEdad($viewName);
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }

    protected function PonerEnVistaLaEdad($p_viewName)
    {
        if (!empty($this->views[$p_viewName]->model->fecha_matriculacion_primera)) {
            $intervalo = date_diff(date_create(date("Y-m-d H:i:s"))
                , date_create($this->views[$p_viewName]->model->fecha_matriculacion_primera)
            );
            $this->views[$p_viewName]->model->edad_vehiculo = $intervalo->format('%y a, %m m, %d d');
        }
    }
}