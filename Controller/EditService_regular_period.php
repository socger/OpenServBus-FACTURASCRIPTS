<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditService_regular_period extends EditController {
    
    public function getModelClassName() {
        return 'Service_regular_period';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Serv. regular - Periodo';
        
        $pageData['icon'] = 'fas fa-calendar-day';
        
        return $pageData;
    }
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {

            // Pestaña con el mismo nombre que este controlador EditXxxxx
            case 'EditService_regular_period': 
                parent::loadData($viewName, $view);
                
                // Guardamos que usuario y cuando pulsará guardar
                $this->views[$viewName]->model->user_nick = $this->user->nick;

             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");
                
                $this->prepararFechasParaVista($viewName);
                $this->prepararHorasParaVista($viewName);

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

