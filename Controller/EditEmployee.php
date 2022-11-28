<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditEmployee extends EditController {
    
    public function getModelClassName() {
        return 'Employee';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Empleado/a';
        
        $pageData['icon'] = 'far fa-id-card';

        return $pageData;
    }
    
    protected function createViews() {
        parent::createViews();
        
        $this->createViewEmployeeContract();
        $this->createViewEmployeeAttendanceManagementYn();
        $this->createViewEmployeeDocumentation();
        
        $this->setTabsPosition('top'); // Las posiciones de las pestañas pueden ser left, top, down
    }
    
    protected function createViewEmployeeContract($viewName = 'ListEmployeeContract')
    {
        $this->addListView($viewName, 'EmployeeContract_2', 'Contratos realizados', 'fas fa-id-badge');
        $this->views[$viewName]->addSearchFields(['nombre']);
        $this->views[$viewName]->addOrderBy(['nombre'], 'Nombre', 1);
        $this->views[$viewName]->addOrderBy(['fecha_inicio', 'fecha_fin'], 'F.inicio + F.fin.');
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->views[$viewName]->addFilterAutocomplete('xIdEmpresa', 'Empresa', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdEmployee', 'Empleado', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdemployee_contract_type', 'Contrato - tipo', 'idemployee_contract_type', 'employee_contract_types', 'idemployee_contract_type', 'nombre');
    }
    
    protected function createViewEmployeeAttendanceManagementYn($viewName = 'ListEmployeeAttendanceManagementYn')
    {
        $this->addListView($viewName, 'EmployeeAttendanceManagementYn_2', '¿Está obligado al control de presencia?', 'fas fa-business-timee');
        $this->views[$viewName]->addSearchFields(['idemployee', 'nombre']);
        $this->views[$viewName]->addOrderBy(['nombre'], 'Nombre', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);
    }
    
    protected function createViewEmployeeDocumentation($viewName = 'ListEmployeeDocumentation')
    {
        $this->addListView($viewName, 'EmployeeDocumentation_2', 'Documentación', 'far fa-file-pdf');
        $this->views[$viewName]->addSearchFields(['nombre']);
        $this->views[$viewName]->addOrderBy(['nombre'], 'Nombre', 1);
        $this->views[$viewName]->addOrderBy(['iddocumentation_type', 'nombre'], 'Tipo Doc. + nombre');
        $this->views[$viewName]->addOrderBy(['fecha_caducidad'], 'F. caducidad.');
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->views[$viewName]->addFilterAutocomplete('xIdEmployee', 'Empleado', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xiddocumentation_type', 'Documentación - tipo', 'iddocumentation_type', 'documentation_types', 'iddocumentation_type', 'nombre');
        $this->views[$viewName]->addFilterPeriod('porFechaCaducidad', 'Fecha de caducidad', 'fecha_caducidad');
    }
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {
            case 'ListEmployeeDocumentation':
                $idemployee = $this->getViewModelValue('EditEmployee', 'idemployee'); // Le pedimos que guarde en la variable local $idemployee el valor del campo idemployee del controlador EditEmployee.php
                $where = [new DatabaseWhere('idemployee', $idemployee)];
                $view->loadData('', $where);
                break;
                    
            case 'ListEmployeeContract':
                $idemployee = $this->getViewModelValue('EditEmployee', 'idemployee'); // Le pedimos que guarde en la variable local $idemployee el valor del campo idemployee del controlador EditEmployee.php
                $where = [new DatabaseWhere('idemployee', $idemployee)];
                $view->loadData('', $where);
                break;
                    
            case 'ListEmployeeAttendanceManagementYn':
                $idemployee = $this->getViewModelValue('EditEmployee', 'idemployee'); // Le pedimos que guarde en la variable local $idemployee el valor del campo idemployee del controlador EditEmployee.php
                $where = [new DatabaseWhere('idemployee', $idemployee)];
                $view->loadData('', $where);
                break;
                    
            // Pestaña con el mismo nombre que este controlador EditXxxxx
            case 'EditEmployee': 
                parent::loadData($viewName, $view);
                
                $this->PonerContratoActivoEnVista($viewName);
                
                // Guardamos que usuario pulsará guardar
                $this->views[$viewName]->model->user_nick = $this->user->nick;

                // Guardamos cuando el usuario pulsará guardar
             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");
                
                // Guardamos si es conductor o no para la vista
                $this->views[$viewName]->model->es_Conductor_SI_NO = 'NO';
                if ($this->views[$viewName]->model->driver_yn == 1) {
                    $this->views[$viewName]->model->es_Conductor_SI_NO = 'SI';
                }
                        
                break;
        }
    }


    // ** *************************************** ** //
    // ** FUNCIONES CREADAS PARA ESTE CONTROLADOR ** //
    // ** *************************************** ** //
    private function PonerContratoActivoEnVista(string $p_viewName)
    {
    
        // Rellenamos el widget de tipo text para el tipo de contrato
        $idemployee = $this->getViewModelValue('EditEmployee', 'idemployee'); // Le pedimos que guarde en la variable local $idemployee el valor del campo idemployee del controlador EditEmployee.php
        
        if (!empty($idemployee)){
            $sql = " SELECT employee_contract_types.nombre "
                     .       ", employee_contracts.fecha_inicio "   
                     .       ", employee_contracts.fecha_fin "   
                     . " FROM employee_contracts "
                     . " LEFT JOIN employee_contract_types ON (employee_contract_types.idemployee_contract_type = employee_contracts.idemployee_contract_type) "   
                     . " WHERE employee_contracts.idemployee = " . $idemployee . " "
                     .   " AND employee_contracts.activo = 1 "
                     . " ORDER BY employee_contracts.idemployee "
                     .        " , employee_contracts.fecha_inicio DESC "
                     .        " , employee_contracts.fecha_fin DESC "
                     . " LIMIT 1 ";

            $registros = $this->dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

            foreach ($registros as $fila) {
                $this->views[$p_viewName]->model->tipo_contrato = $fila['nombre'];
                $this->views[$p_viewName]->model->fecha_inicio = $fila['fecha_inicio'];
                $this->views[$p_viewName]->model->fecha_fin = $fila['fecha_fin'];
            }
        }
        
    }
    
}
