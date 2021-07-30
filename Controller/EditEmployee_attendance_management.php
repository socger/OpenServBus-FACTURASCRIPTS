<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditEmployee_attendance_management extends EditController {
    
    public function getModelClassName() {
        return 'Employee_attendance_management';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Fichaje / Asistencia';
        
        $pageData['icon'] = 'fas fa-hourglass-half';

        return $pageData;
    }

    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {

            // Pestaña con el mismo nombre que este controlador EditXxxxx
            case 'EditEmployee_attendance_management': 
                parent::loadData($viewName, $view);
                
                // Guardamos que usuario y cuando pulsará guardar
                $this->views[$viewName]->model->user_nick = $this->user->nick;

             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");

                if (empty($this->views[$viewName]->model->fecha)){
                    $this->views[$viewName]->model->fecha = date("Y-m-d H:i:s"); // por si están creando que les ponga la hora y fecha actual
                }
                
                // Partimos la fecha en dia y hora para usar los widget de dia y hora
                $this->views[$viewName]->model->fecha_dia = date("Y-m-d", strtotime($this->views[$viewName]->model->fecha));
                $this->views[$viewName]->model->fecha_hora = date("H:i:s", strtotime($this->views[$viewName]->model->fecha));
                
                break;
        }
    }


    // ** *************************************** ** //
    // ** FUNCIONES CREADAS PARA ESTE CONTROLADOR ** //
    // ** *************************************** ** //
    
}
