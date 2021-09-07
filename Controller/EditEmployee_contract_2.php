<?php

// NO OLVIDEMOS QUE LOS CAMBIOS QUE HAGAMOS EN ESTE CONTROLADOR TENDRÍAMOS QUE HACERLOS TAMBIEN POSIBLEMENTE EN CONTROLADOR EditEmployee_contract.php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditEmployee_contract_2 extends EditController {
    
    public function getModelClassName() {
        return 'Employee_contract_2';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Contrato';
        
        $pageData['icon'] = 'fas fa-id-badge';

        return $pageData;
    }
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {

            // Pestaña con el mismo nombre que este controlador EditXxxxx
            case 'EditEmployee_contract_2': 
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
                
                // Guardamos si esta o no activo
                $this->views[$viewName]->model->esta_Activo_SI_NO = 'NO';
                if ($this->views[$viewName]->model->activo == 1) {
                    $this->views[$viewName]->model->esta_Activo_SI_NO = 'SI';
                }
                        
                
                break;
        }
    }
    
}
