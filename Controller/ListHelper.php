<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListHelper extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Archivos';
        $pageData['icon'] = 'fas fa-archive';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewHelper();
        $this->createViewGarage();
        $this->createViewDepartment();
        $this->createViewCollaborator();
        $this->createViewIdentificationMean();
    }

    protected function createViewCollaborator($viewName = 'ListCollaborator')
    {
        $this->addView($viewName, 'Collaborator', 'collaborator', 'fas fa-business-time');
        $this->addSearchFields($viewName, ['codproveedor', 'nombre']);
        $this->addOrderBy($viewName, ['codproveedor'], 'Cod.Proveedor');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);
    }

    protected function createViewDepartment($viewName = 'ListDepartment')
    {
        $this->addView($viewName, 'Department', 'Departamentos', 'fas fa-book-reader');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);
    }

    protected function createViewGarage($viewName = 'ListGarage')
    {
        $this->addView($viewName, 'Garage', 'Garajes', 'fas fa-warehouse');
        $this->addSearchFields($viewName, ['nombre', 'direccion']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'Empresa', 'idempresa', 'empresas', 'idempresa', 'nombre');
    }

    protected function createViewHelper($viewName = 'ListHelper')
    {
        $this->addView($viewName, 'Helper', 'Monitores', 'fas fa-user-graduate');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $status = [
            ['label' => 'Colaboradores/Empleados - Todos', 'where' => []],
            ['label' => 'Colaboradores sólo', 'where' => [new DataBaseWhere('idcollaborator', '0', '>')]],
            ['label' => 'Empleados sólo', 'where' => [new DataBaseWhere('idemployee', '0', '>')]]
        ];
        $this->addFilterSelectWhere($viewName, 'status', $status);
    }

    protected function createViewIdentificationMean($viewName = 'ListIdentificationMean')
    {
        $this->addView($viewName, 'IdentificationMean', 'Medios de Identificación', 'far fa-hand-point-right');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);
    }
}
