<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

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
        $pageData['title'] = 'Serv. regulares - Combinación';
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
        $this->addListView($viewName, 'ServiceRegularCombinationServ', 'Servicios', 'fas fa-cogs');
        $this->views[$viewName]->addOrderBy(['idservice_regular_combination', 'idservice_regular'], 'Nombre', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);
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

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }
}