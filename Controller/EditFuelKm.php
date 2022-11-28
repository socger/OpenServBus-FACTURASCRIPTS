<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditFuelKm extends EditController {
    
    public function getModelClassName() {
        return 'FuelKm';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Repostaje - Kms';
        
        $pageData['icon'] = 'fas fa-gas-pump';

        return $pageData;
    }
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {

            // Pestaña con el mismo nombre que este controlador EditXxxxx
            case 'EditFuelKm':
                parent::loadData($viewName, $view);
                
                // Guardamos que usuario y cuando pulsará guardar
                $this->views[$viewName]->model->user_nick = $this->user->nick;

             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");
                
                $this->TraerTipoTarjeta($viewName);
                
                break;
        }
    }

    // ** *************************************** ** //
    // ** FUNCIONES CREADAS PARA ESTE CONTROLADOR ** //
    // ** *************************************** ** //
    private function TraerTipoTarjeta(string $p_viewName)
    {
        if (!empty($this->views[$p_viewName]->model->idtarjeta)){
            $sql = " SELECT tarjeta_types.nombre "
                 .      " , tarjeta_types.de_pago "
                 . " FROM tarjetas "
                 . " LEFT JOIN tarjeta_types ON (tarjeta_types.idtarjeta_type = tarjetas.idtarjeta_type) "
                 . " WHERE tarjetas.idtarjeta = " . $this->views[$p_viewName]->model->idtarjeta . " ";

            $registros = $this->dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

            foreach ($registros as $fila) {
                $this->views[$p_viewName]->model->tipo_tarjeta = $fila['nombre'];

                if ($fila['de_pago'] == 1){
                    $this->views[$p_viewName]->model->es_de_pago = 'Si';
                } else {
                    $this->views[$p_viewName]->model->es_de_pago = 'No';
                }
            }
        }
    }
    
}



                
