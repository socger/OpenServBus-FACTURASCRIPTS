<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

// use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditService_regular extends EditController {
    
    public function getModelClassName() {
        return 'Service_regular';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Servicio fijo/regular';

        $pageData['icon'] = 'fas fa-book-open';
        

        return $pageData;
    }
    
/*
    protected function createViews() {
        parent::createViews();
        
        // $this->addListView($viewName, $modelName, $viewTitle, $viewIcon)
 
        // $viewName: el identificador o nombre interno de esta pestaña o sección. Por ejemplo: ListProducto.
        // $modelName: el nombre del modelo que usará este listado. Por ejemplo: Producto.
        // $viewTitle: el título de la pestaña o sección. Será tarducido. Por ejemplo: products.
        // $viewIcon: (opcional) el icono a utilizar. Por ejemplo: fas fa-search.

        $this->addListView('ListService_regular_itineario', 'Service_regular_itineario', 'Itinerarios');    
        
        $this->setTabsPosition('top'); // Las posiciones de las pestañas pueden ser left, top, down
    }
*/    
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {
            /*
            case 'ListEmployee_documentation':
                $idemployee = $this->getViewModelValue('EditEmployee', 'idemployee'); // Le pedimos que guarde en la variable local $idemployee el valor del campo idemployee del controlador EditEmployee.php
                $where = [new DatabaseWhere('idemployee', $idemployee)];
                $view->loadData('', $where);
                break;
            */
            
            // Pestaña con el mismo nombre que este controlador EditXxxxx
            case 'EditService_regular': 
                parent::loadData($viewName, $view);
                
                // Guardamos que usuario pulsará guardar
                $this->views[$viewName]->model->user_nick = $this->user->nick;

                // Guardamos cuando el usuario pulsará guardar
             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");
                
                break;
        }
    }


    // ** *************************************** ** //
    // ** FUNCIONES CREADAS PARA ESTE CONTROLADOR ** //
    // ** *************************************** ** //
        
    
}
