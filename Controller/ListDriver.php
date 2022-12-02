<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListDriver extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'drivers';
        $pageData['icon'] = 'fas fa-user-astronaut';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewDriver();
    }

    protected function createViewDriver($viewName = 'ListDriver')
    {
        $this->addView($viewName, 'Driver', 'drivers', 'fas fa-user-astronaut');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterSelectWhere(
            $viewName,
            'status',
            [
                ['label' => 'Colaboradores/Empleados - Todos', 'where' => []],
                ['label' => 'Colaboradores sólo', 'where' => [new DataBaseWhere('idcollaborator', '0', '>')]],
                ['label' => 'Empleados sólo', 'where' => [new DataBaseWhere('idemployee', '0', '>')]]
            ]
        );
    }
}