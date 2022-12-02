<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListEmployee extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'employees';
        $pageData['icon'] = 'far fa-id-card';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewEmployee();
        $this->createViewEmployeeContract();
    }

    protected function createViewEmployee($viewName = 'ListEmployee')
    {
        $this->addView($viewName, 'Employee', 'employees', 'far fa-id-card');
        $this->addSearchFields($viewName, ['cod_employee', 'nombre', 'direccion']);
        $this->addOrderBy($viewName, ['nombre'], 'name', 1);
        $this->addOrderBy($viewName, ['cod_employee'], 'code');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'company', 'idempresa', 'empresas', 'idempresa', 'nombre');

        $esConductor = [
            ['code' => '1', 'description' => 'driver-yes'],
            ['code' => '0', 'description' => 'driver-no'],
        ];
        $this->addFilterSelect($viewName, 'esConductor', 'driver-all', 'driver_yn', $esConductor);
    }

    protected function createViewEmployeeContract($viewName = 'ListEmployeeContract')
    {
        $this->addView($viewName, 'EmployeeContract', 'contracts', 'fas fa-file-contract');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['fecha_inicio', 'fecha_fin'], 'fstart-fend');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'company', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'employee', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdemployee_contract_type', 'contract-type', 'idemployee_contract_type', 'employee_contract_types', 'idemployee_contract_type', 'nombre');
    }
}