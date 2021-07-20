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
        $this->addListView('ListVehicle_documentation', 'Vehicle_documentation', 'Documentación');    
        
        $this->setTabsPosition('top'); // Las posiciones de las pestañas pueden ser left, top, down
    }
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {
            case 'ListVehicle_documentation':
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
                
                break;
        }
    }
    
}
