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
        
        $this->createView__Employee_contract();
        $this->createView__Employee_attendance_management_yn();
        $this->createView__Employee_documentation();
        
        $this->setTabsPosition('top'); // Las posiciones de las pestañas pueden ser left, top, down
    }
    
    protected function createView__Employee_contract($model = 'Employee_contract')
    {
        // $this->addListView($viewName, $modelName, $viewTitle, $viewIcon)
        // $viewName: el identificador o nombre interno de esta pestaña o sección. Por ejemplo: ListProducto.
        // $modelName: el nombre del modelo que usará este listado. Por ejemplo: Producto.
        // $viewTitle: el título de la pestaña o sección. Será tarducido. Por ejemplo: products.
        // $viewIcon: (opcional) el icono a utilizar. Por ejemplo: fas fa-search.
        $this->addListView('List' . $model, $model . '_2', 'Contratos realizados', 'fas fa-id-badge');    


        $this->views['List' . $model]->addSearchFields(['nombre']); 


        $this->views['List' . $model]->addOrderBy(['nombre'], 'Nombre', 1);
        $this->views['List' . $model]->addOrderBy(['fecha_inicio', 'fecha_fin'], 'F.inicio + F.fin.');
        $this->views['List' . $model]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        
        // Filtro de TIPO SELECT para filtrar por registros activos (SI, NO, o TODOS)
        // Sustituimos el filtro activo (checkBox) por el filtro activo (select)
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views['List' . $model]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);

        
        $this->views['List' . $model]->addFilterAutocomplete('xIdEmpresa', 'Empresa', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->views['List' . $model]->addFilterAutocomplete('xIdEmployee', 'Empleado', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->views['List' . $model]->addFilterAutocomplete('xIdemployee_contract_type', 'Contrato - tipo', 'idemployee_contract_type', 'employee_contract_types', 'idemployee_contract_type', 'nombre');
    }
    
    protected function createView__Employee_attendance_management_yn($model = 'Employee_attendance_management_yn')
    {
        // $this->addListView($viewName, $modelName, $viewTitle, $viewIcon)
        // $viewName: el identificador o nombre interno de esta pestaña o sección. Por ejemplo: ListProducto.
        // $modelName: el nombre del modelo que usará este listado. Por ejemplo: Producto.
        // $viewTitle: el título de la pestaña o sección. Será tarducido. Por ejemplo: products.
        // $viewIcon: (opcional) el icono a utilizar. Por ejemplo: fas fa-search.
        $this->addListView('List' . $model, $model, '¿Está obligado al control de presencia?', 'fas fa-business-timee');  
        
        
        $this->views['List' . $model]->addSearchFields(['idemployee', 'nombre']);


        $this->views['List' . $model]->addOrderBy(['nombre'], 'Nombre', 1);
        $this->views['List' . $model]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');


        // Filtro de TIPO SELECT para filtrar por registros activos (SI, NO, o TODOS)
        // Sustituimos el filtro activo (checkBox) por el filtro activo (select)
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views['List' . $model]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);
        
    }
    
    protected function createView__Employee_documentation($model = 'Employee_documentation')
    {
        // $this->addListView($viewName, $modelName, $viewTitle, $viewIcon)
        // $viewName: el identificador o nombre interno de esta pestaña o sección. Por ejemplo: ListProducto.
        // $modelName: el nombre del modelo que usará este listado. Por ejemplo: Producto.
        // $viewTitle: el título de la pestaña o sección. Será tarducido. Por ejemplo: products.
        // $viewIcon: (opcional) el icono a utilizar. Por ejemplo: fas fa-search.
        $this->addListView('List' . $model, $model, 'Documentación', 'far fa-file-pdf');    
        
        
        $this->views['List' . $model]->addSearchFields(['nombre']);

        
        $this->views['List' . $model]->addOrderBy(['nombre'], 'Nombre', 1);
        $this->views['List' . $model]->addOrderBy(['iddocumentation_type', 'nombre'], 'Tipo Doc. + nombre');
        $this->views['List' . $model]->addOrderBy(['fecha_caducidad'], 'F. caducidad.');
        $this->views['List' . $model]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');


        // Filtro de TIPO SELECT para filtrar por registros activos (SI, NO, o TODOS)
        // Sustituimos el filtro activo (checkBox) por el filtro activo (select)
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views['List' . $model]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);

        
        $this->views['List' . $model]->addFilterAutocomplete('xIdEmployee', 'Empleado', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->views['List' . $model]->addFilterAutocomplete('xiddocumentation_type', 'Documentación - tipo', 'iddocumentation_type', 'documentation_types', 'iddocumentation_type', 'nombre');

        
        $this->views['List' . $model]->addFilterPeriod('porFechaCaducidad', 'Fecha de caducidad', 'fecha_caducidad');
    }
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {
            case 'ListEmployee_documentation':
                $idemployee = $this->getViewModelValue('EditEmployee', 'idemployee'); // Le pedimos que guarde en la variable local $idemployee el valor del campo idemployee del controlador EditEmployee.php
                $where = [new DatabaseWhere('idemployee', $idemployee)];
                $view->loadData('', $where);
                break;
                    
            case 'ListEmployee_contract':
                $idemployee = $this->getViewModelValue('EditEmployee', 'idemployee'); // Le pedimos que guarde en la variable local $idemployee el valor del campo idemployee del controlador EditEmployee.php
                $where = [new DatabaseWhere('idemployee', $idemployee)];
                $view->loadData('', $where);
                break;
                    
            case 'ListEmployee_attendance_management_yn':
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
