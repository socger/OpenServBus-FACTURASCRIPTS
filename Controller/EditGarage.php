<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditGarage extends EditController {
    
    public $seModifica;  
    
    public function getModelClassName() {
        return 'Garage';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Cochera';
        
        $pageData['icon'] = 'fas fa-warehouse';

        return $pageData;
    }
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    // en este caso EditGarage
    protected function loadData($viewName, $view) {
        switch ($viewName) {

            // Pestaña EditProject
            case 'EditGarage': 
                parent::loadData($viewName, $view);
                
                // Asigna por defecto el usuario que da el alta, con el usuario que se ha registrado en la web
                // Pero sólo si estamos en modo Insert/Append, en modo Edit no lo hace

//                if(!$this->views[$viewName]->model->exists()) { 
//                    // Estamos en un alta
//                    $this->views[$viewName]->model->useralta = $this->user->nick; // Rellenamos useralta
//                    $this->views[$viewName]->model->fechaalta = date('d-m-Y');
//                } else {
//                    // Estamos en una modificación
//                    // $this->views[$viewName]->model->usermodificacion = $this->user->nick;
//                    // $this->views[$viewName]->model->fechamodificacion = date('d-m-Y');
//                    $this->seModifica = 'SI';
//                }
//
                // Guardamos quien pulsó guardar y cuando
                $this->views[$viewName]->model->user_nick = $this->user->nick;

             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");
                
                break;
        }
    }
    
    protected function saveData($viewName, $view) {
        if ($this->seModifica == 'SI') {
            $this->views[$viewName]->model->usermodificacion = $this->user->nick;
            $this->views[$viewName]->model->fechamodificacion = date('d-m-Y');
        };
    }
}
