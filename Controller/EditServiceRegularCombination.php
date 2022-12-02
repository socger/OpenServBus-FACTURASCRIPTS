<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Plugins\OpenServBus\Model\Driver;

class EditServiceRegularCombination extends EditController
{
    public function getModelClassName(): string
    {
        return 'ServiceRegularCombination';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'serv-regular-combination';
        $pageData['icon'] = 'fas fa-briefcase';
        return $pageData;
    }

    protected function createViews()
    {
        parent::createViews();
        $this->createViewServiceRegularCombination_serv();
        $this->setTabsPosition('top');
    }

    protected function createViewServiceRegularCombination_serv($viewName = 'ListServiceRegularCombinationServ')
    {
        $this->addListView($viewName, 'ServiceRegularCombinationServ', 'services', 'fas fa-cogs');
        $this->views[$viewName]->addOrderBy(['idservice_regular_combination', 'idservice_regular'], 'name', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);
        $this->views[$viewName]->addFilterAutocomplete('xIdDriver', 'driver', 'iddriver', 'drivers', 'iddriver', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdVehicle', 'vehicle', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdservice_regular', 'service-regular', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
    }

    protected function loadData($viewName, $view)
    {
        $mvn = $this->getMainViewName();
        switch ($viewName) {
            case 'ListServiceRegularCombinationServ':
                $idservice_regular_combination = $this->getViewModelValue($mvn, 'idservice_regular_combination');
                $where = [new DatabaseWhere('idservice_regular_combination', $idservice_regular_combination)];
                $view->loadData('', $where);
                break;

            case $mvn:
                parent::loadData($viewName, $view);
                $this->loadValuesSelectDrivers($mvn, 'usual-driver-1');
                $this->loadValuesSelectDrivers($mvn, 'usual-driver-2');
                $this->loadValuesSelectDrivers($mvn, 'usual-driver-3');
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }

    protected function loadValuesSelectDrivers(string $mvn, string $columnName)
    {
        $column = $this->views[$mvn]->columnForName($columnName);
        if($column && $column->widget->getType() === 'select') {
            // obtenemos los conductores
            $customValues = [];
            $driversModel = new Driver();
            foreach ($driversModel->all([], [], 0, 0) as $driver) {
                $customValues[] = [
                    'value' => $driver->iddriver,
                    'title' => $driver->nombre,
                ];
            }
            $column->widget->setValuesFromArray($customValues, false, true);
        }
    }
}