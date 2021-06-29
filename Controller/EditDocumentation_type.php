<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditDocumentation_type extends EditController {
    
    public function getModelClassName() {
        return 'Documentation_type';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Empleados - Tipo Documentación';
        
        $pageData['icon'] = 'far fa-address-card';

        return $pageData;
    }
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    // en este caso EditDocumentation_type
    protected function loadData($viewName, $view) {
        switch ($viewName) {

            // Pestaña EditDocumentation_type
            case 'EditDocumentation_type': 
                parent::loadData($viewName, $view);
                
                // Guardamos quien pulsó guardar y cuando
                $this->views[$viewName]->model->user_nick = $this->user->nick;

             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");
                
                break;
        }
    }
    
}
