<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

 use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditService_assembly extends EditController {
    
    public function getModelClassName() {
        return 'Service_assembly';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();

        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Montaje de servicios';

        $pageData['icon'] = 'fas fa-business-time';
        

        return $pageData;
    }
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {
            // Pestaña con el mismo nombre que este controlador EditXxxxx
            case 'EditService_assembly': 
                parent::loadData($viewName, $view);
                
                // Guardamos que usuario pulsará guardar
                $this->views[$viewName]->model->user_nick = $this->user->nick;

                // Guardamos cuando el usuario pulsará guardar
             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");
                
                $this->prepararFechasParaVista($viewName);
                $this->prepararHorasParaVista($viewName);
                
                if ($this->views[$viewName]->model->salida_desde_nave_sn === true) {
                    $this->views[$viewName]->model->salida_desde_nave_text = 'SI';
                } else {
                    $this->views[$viewName]->model->salida_desde_nave_text = 'NO';
                }

                break;
        }
    }


    // ** *************************************** ** //
    // ** FUNCIONES CREADAS PARA ESTE CONTROLADOR ** //
    // ** *************************************** ** //
    private function prepararFechasParaVista($viewName)
    {
        if (!empty($this->views[$viewName]->model->fecha_desde)){
            $this->views[$viewName]->model->inicio_dia = date("Y-m-d", strtotime($this->views[$viewName]->model->fecha_desde));
        } else {
            $this->views[$viewName]->model->inicio_dia = null;
        }

        if (!empty($this->views[$viewName]->model->fecha_hasta)){
            $this->views[$viewName]->model->fin_dia = date("Y-m-d", strtotime($this->views[$viewName]->model->fecha_hasta));
        } else {
            $this->views[$viewName]->model->fin_dia = null;
        }
    }

    private function prepararHorasParaVista($viewName)
    {
        if (!empty($this->views[$viewName]->model->hora_anticipacion)){
            $this->views[$viewName]->model->inicio_horaAnt = date("H:i:s", strtotime($this->views[$viewName]->model->hora_anticipacion));
        } else {
            $this->views[$viewName]->model->inicio_horaAnt = null;
        }
        
        if (!empty($this->views[$viewName]->model->hora_desde)){
            $this->views[$viewName]->model->inicio_hora = date("H:i:s", strtotime($this->views[$viewName]->model->hora_desde));
        } else {
            $this->views[$viewName]->model->inicio_hora = null;
        }

        if (!empty($this->views[$viewName]->model->hora_hasta)){
            $this->views[$viewName]->model->fin_hora = date("H:i:s", strtotime($this->views[$viewName]->model->hora_hasta));
        } else {
            $this->views[$viewName]->model->fin_hora = null;
        }
    }
    
}
