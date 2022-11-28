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
        $pageData['title'] = 'Conductores';
        $pageData['icon'] = 'fas fa-user-astronaut';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewDriver();
    }

    protected function createViewDriver($viewName = 'ListDriver')
    {
        $this->addView($viewName, 'Driver', 'Conductores', 'fas fa-user-astronaut');
        $this->addSearchFields($viewName, ['idemployee', 'nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->addFilterSelectWhere(
            $viewName,
            'status',
            [
                ['label' => 'Colaboradores/Empleados - Todos', 'where' => []],
                ['label' => 'Colaboradores sólo', 'where' => [new DataBaseWhere('idcollaborator', '0', '>')]],
                ['label' => 'Empleados sólo', 'where' => [new DataBaseWhere('idemployee', '0', '>')]]
            ]
        );

        $this->addFilterAutocomplete($viewName, 'xIdEmpleado', 'Empleado', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdCollaborator', 'Colaborador', 'idcollaborator', 'collaborators', 'idcollaborator', 'nombre');
    }
}