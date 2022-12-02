<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListEmployeeAttendanceManagement extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'site-control';
        $pageData['icon'] = 'fas fa-hourglass-half';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewEmployeeAttendanceManagement();
        $this->createViewAbsenceReason();
        $this->createViewEmployeeAttendanceManagementYn();
    }

    protected function createViewAbsenceReason($viewName = 'ListAbsenceReason')
    {
        $this->addView($viewName, 'AbsenceReason', 'absences-reasons', 'fas fa-first-aid');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'name', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);
    }

    protected function createViewEmployeeAttendanceManagement($viewName = 'ListEmployeeAttendanceManagement')
    {
        $this->addView($viewName, 'EmployeeAttendanceManagement', 'transfers-and-assists', 'fas fa-hourglass-half');
        $this->addOrderBy($viewName, ['fecha'], 'date', 1);
        $this->addOrderBy($viewName, ['idemployee', 'fecha'], 'employee-date');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'employee', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdidentification_mean', 'Identificacion - medio', 'ididentification_mean', 'identification_means', 'ididentification_mean', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdabsence_reason', 'absence-reason', 'idabsence_reason', 'absence_reasons', 'idabsence_reason', 'nombre');
        $this->addFilterPeriod($viewName, 'porFecha', 'date', 'fecha');

        $origen = [
            ['code' => '0', 'description' => 'origin-external'],
            ['code' => '1', 'description' => 'origin-manual'],
        ];
        $this->addFilterSelect($viewName, 'elOrigen', 'origin-all', 'origen', $origen);

        $origen = [
            ['code' => '1', 'description' => 'transfer-type-entry'],
            ['code' => '0', 'description' => 'transfer-type-output'],
        ];
        $this->addFilterSelect($viewName, 'elTipoFichaje', 'transfer-type-all', 'tipoFichaje', $origen);
    }

    protected function createViewEmployeeAttendanceManagementYn($viewName = 'ListEmployeeAttendanceManagementYn')
    {
        $this->addView($viewName, 'EmployeeAttendanceManagementYn', 'force-face-to-face-control', 'fas fa-fingerprint');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'employee', 'idemployee', 'employees', 'idemployee', 'nombre');
    }
}