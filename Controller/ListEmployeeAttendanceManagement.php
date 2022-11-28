<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListEmployeeAttendanceManagement extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Control presencial';
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
        $this->addView($viewName, 'AbsenceReason', 'Ausencias - motivos', 'fas fa-first-aid');
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

    protected function createViewEmployeeAttendanceManagement($viewName = 'ListEmployeeAttendanceManagement')
    {
        $this->addView($viewName, 'EmployeeAttendanceManagement', 'Fichajes y asistencias', 'fas fa-hourglass-half');
        $this->addOrderBy($viewName, ['fecha'], 'Fecha', 1);
        $this->addOrderBy($viewName, ['idemployee', 'fecha'], 'Empleado + Fecha');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'Empleado', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdidentification_mean', 'Identificacion - medio', 'ididentification_mean', 'identification_means', 'ididentification_mean', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdabsence_reason', 'Ausencia - motivo', 'idabsence_reason', 'absence_reasons', 'idabsence_reason', 'nombre');
        $this->addFilterPeriod($viewName, 'porFecha', 'Fecha', 'fecha');

        $origen = [
            ['code' => '0', 'description' => 'Origen = EXTERNO'],
            ['code' => '1', 'description' => 'Origen = MANUAL'],
        ];
        $this->addFilterSelect($viewName, 'elOrigen', 'Origen = TODOS', 'origen', $origen);

        $origen = [
            ['code' => '1', 'description' => 'Tipo fichaje = ENTRADA'],
            ['code' => '0', 'description' => 'Tipo fichaje = SALIDA'],
        ];
        $this->addFilterSelect($viewName, 'elTipoFichaje', 'Tipo fichaje = TODOS', 'tipoFichaje', $origen);
    }

    protected function createViewEmployeeAttendanceManagementYn($viewName = 'ListEmployeeAttendanceManagementYn')
    {
        $this->addView($viewName, 'EmployeeAttendanceManagementYn', 'Obligar control presencial a ...', 'fas fa-fingerprint');
        $this->addSearchFields($viewName, ['idemployee', 'nombre']);
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