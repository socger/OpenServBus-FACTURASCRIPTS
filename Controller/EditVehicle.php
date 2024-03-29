<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditVehicle extends EditController {
    
    public function getModelClassName() {
        return 'Vehicle';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Vehículo';
        
        $pageData['icon'] = 'fas fa-bus-alt';

        return $pageData;
    }
    
    protected function createViews() {
        parent::createViews();
        
        /*
        $this->addListView($viewName, $modelName, $viewTitle, $viewIcon)
         * 
        $viewName: el identificador o nombre interno de esta pestaña o sección. Por ejemplo: ListProducto.
        $modelName: el nombre del modelo que usará este listado. Por ejemplo: Producto.
        $viewTitle: el título de la pestaña o sección. Será tarducido. Por ejemplo: products.
        $viewIcon: (opcional) el icono a utilizar. Por ejemplo: fas fa-search.
        */
        $this->createView__Vehicle_equipament();    
        $this->createView__Vehicle_documentation();    
        
        $this->setTabsPosition('top'); // Las posiciones de las pestañas pueden ser left, top, down
    }
    
    protected function createView__Vehicle_documentation($model = 'Vehicle_documentation')
    {
        // $this->addListView($viewName, $modelName, $viewTitle, $viewIcon)
        // $viewName: el identificador o nombre interno de esta pestaña o sección. Por ejemplo: ListProducto.
        // $modelName: el nombre del modelo que usará este listado. Por ejemplo: Producto.
        // $viewTitle: el título de la pestaña o sección. Será tarducido. Por ejemplo: products.
        // $viewIcon: (opcional) el icono a utilizar. Por ejemplo: fas fa-search.
        $this->addListView('List' . $model, $model . '_2', 'Documentación', 'far fa-file-pdf'); 
        
        
        $this->views['List' . $model]->addSearchFields(['nombre']); 

        
        $this->views['List' . $model]->addOrderBy(['nombre'], 'Nombre', 1);
        $this->views['List' . $model]->addOrderBy(['idvehicle', 'nombre'], 'Vehículo + Tipo Doc.');
        $this->views['List' . $model]->addOrderBy(['fecha_caducidad'], 'F. caducidad.');
        $this->views['List' . $model]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        

        // Filtro de TIPO SELECT para filtrar por registros activos (SI, NO, o TODOS)
        // Sustituimos el filtro activo (checkBox) por el filtro activo (select)
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views['List' . $model]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);
     

        $this->views['List' . $model]->addFilterAutocomplete('xIdVehicle', 'Vehículo', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->views['List' . $model]->addFilterAutocomplete('xiddocumentation_type', 'Documentación - tipo', 'iddocumentation_type', 'documentation_types', 'iddocumentation_type', 'nombre');


        $this->views['List' . $model]->addFilterPeriod('porFechaCaducidad', 'Fecha de caducidad', 'fecha_caducidad');
    }
    
    protected function createView__Vehicle_equipament($model = 'Vehicle_equipament')
    {
        // $this->addListView($viewName, $modelName, $viewTitle, $viewIcon)
        // $viewName: el identificador o nombre interno de esta pestaña o sección. Por ejemplo: ListProducto.
        // $modelName: el nombre del modelo que usará este listado. Por ejemplo: Producto.
        // $viewTitle: el título de la pestaña o sección. Será tarducido. Por ejemplo: products.
        // $viewIcon: (opcional) el icono a utilizar. Por ejemplo: fas fa-search.
        $this->addListView('List' . $model, $model, 'Equipamiento', 'fab fa-accessible-icon'); 
        
        
        $this->views['List' . $model]->addOrderBy(['idvehicle', 'idvehicle_equipament_type'], 'Vehículo + Equipamiento', 1);
        $this->views['List' . $model]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        

        // Filtro de TIPO SELECT para filtrar por registros activos (SI, NO, o TODOS)
        // Sustituimos el filtro activo (checkBox) por el filtro activo (select)
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views['List' . $model]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);
     

        $this->views['List' . $model]->addFilterAutocomplete('xIdVehicle', 'Vehículo', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->views['List' . $model]->addFilterAutocomplete('xIdVehicle_equipament_type', 'Equipamiento - Tipo', 'idvehicle_equipament_type', 'vehicle_equipament_types', 'idvehicle_equipament_type', 'nombre');
    }
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {
            case 'ListVehicle_documentation':
                $idvehicle = $this->getViewModelValue('EditVehicle', 'idvehicle'); // Le pedimos que guarde en la variable local $idemployee el valor del campo idemployee del controlador EditEmployee.php
                $where = [new DatabaseWhere('idvehicle', $idvehicle)];
                $view->loadData('', $where);
                break;
                    
            case 'ListVehicle_equipament':
                $idvehicle = $this->getViewModelValue('EditVehicle', 'idvehicle'); // Le pedimos que guarde en la variable local $idemployee el valor del campo idemployee del controlador EditEmployee.php
                $where = [new DatabaseWhere('idvehicle', $idvehicle)];
                $view->loadData('', $where);
                break;

            // Pestaña con el mismo nombre que este controlador EditXxxxx
            case 'EditVehicle': 
                parent::loadData($viewName, $view);
                
                /* No hace falta porque ya tenemos el campo nombre físicamente en tabla collaborators
                    // Rellenamos el widget de tipo select para la empresa colaboradora
                    $sql = ' SELECT COLLABORATORS.IDCOLLABORATOR AS value '
                         .      ' , PROVEEDORES.NOMBRE AS title '
                         . ' FROM COLLABORATORS '
                         . ' LEFT JOIN PROVEEDORES ON (PROVEEDORES.CODPROVEEDOR = COLLABORATORS.CODPROVEEDOR) ';

                    $data = $this->dataBase->select($sql);

                 // $data[] = ['value' => null, 'title' => null];
                 // $data[] = ['value' => '24', 'title' => 'jeromin'];

                 // array_unshift($data, ['value' => null, '------' => null]); ... Esto no guardaba una línea nula
                 // array_unshift($data, ['value' => '0', 'title' => '------']); ... Esto me dejaba una opción que aparentemente parecía nula, pero luego en function test del modelo tenía que comprobar si devolvía 0 para ponerlo = null (idCollaborator)

                    $columnToModify = $this->views[$viewName]->columnForName('Colaborador');
                    if($columnToModify) {
                     // $columnToModify->widget->setValuesFromArray($data);
                        $columnToModify->widget->setValuesFromArray($data, false, true); // El 3er parámetro es para añadir un elemento vacío, mirar documentacion en https://github.com/NeoRazorX/facturascripts/blob/master/Core/Lib/Widget/WidgetSelect.php#L137
                    }
                */
                
                // Guardamos que usuario y cuando pulsará guardar
                $this->views[$viewName]->model->user_nick = $this->user->nick;

             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");
                
                $this->PonerEnVistaLaEdad($viewName);

                break;
        }
    }


    // ** *************************************** ** //
    // ** FUNCIONES CREADAS PARA ESTE CONTROLADOR ** //
    // ** *************************************** ** //
    private function PonerEnVistaLaEdad($p_viewName) {
        if (!empty($this->views[$p_viewName]->model->fecha_matriculacion_primera)) {
         // $this->views[$viewName]->model->edad_vehiculo = "12";
            $intervalo = date_diff( date_create(date("Y-m-d H:i:s"))
                                  , date_create($this->views[$p_viewName]->model->fecha_matriculacion_primera) 
                                  );

            $this->views[$p_viewName]->model->edad_vehiculo = $intervalo->format('%y a, %m m, %d d');
        }
    }
    
}
