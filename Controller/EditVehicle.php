<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

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
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {

            // Pestaña con el mismo nombre que este controlador EditXxxxx
            case 'EditVehicle': 
                parent::loadData($viewName, $view);
                
                // Rellenamos el widget de tipo select para la empresa colaboradora
                $sql = ' SELECT COLLABORATORS.IDCOLLABORATOR AS value '
                     .      ' , PROVEEDORES.NOMBRE AS title '
                     . ' FROM COLLABORATORS '
                     . ' LEFT JOIN PROVEEDORES ON (PROVEEDORES.CODPROVEEDOR = COLLABORATORS.CODPROVEEDOR) ';

                $data = $this->dataBase->select($sql);
                $columnToModify = $this->views[$viewName]->columnForName('Colaborador');
                if($columnToModify) {
                    $columnToModify->widget->setValuesFromArray($data);
                }
                
                // Guardamos que usuario y cuando pulsará guardar
                $this->views[$viewName]->model->user_nick = $this->user->nick;

             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");
                
                break;
        }
    }
    
}
