<?php

// NO OLVIDEMOS QUE LOS CAMBIOS QUE HAGAMOS EN ESTE MODELO TENDRÍAMOS QUE HACERLOS TAMBIEN POSIBLEMENTE EN CONTROLADOR EditEmployee_attendance_management_yn_2.php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditEmployeeAttendanceManagementYn extends EditController {
    
    public function getModelClassName() {
        return 'EmployeeAttendanceManagementYn';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Obligado - Control Presencial';
        
        $pageData['icon'] = 'fas fa-business-time';

        return $pageData;
    }
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {

            // Pestaña con el mismo nombre que este controlador EditXxxxx
            case 'EditEmployeeAttendanceManagementYn':
                parent::loadData($viewName, $view);
                
                // Guardamos que usuario y cuando pulsará guardar
                $this->views[$viewName]->model->user_nick = $this->user->nick;

             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");
                
                break;
        }
    }
    
}
