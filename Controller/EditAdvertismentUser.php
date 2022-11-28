<?php
    
// SI MODIFICAMOS ESTE CONTROLADOR TENEMOS QUE VER SI HAY QUE HACER LOS MISMOS CAMBIOS EN EditAdvertisment_user2.php
    
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditAdvertismentUser extends EditController {
    
    public function getModelClassName() {
        return 'AdvertismentUser';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Aviso';
        
        $pageData['icon'] = 'fas fa-exclamation-triangle';

        return $pageData;
    }
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {

            // Pestaña con el mismo nombre que este controlador EditXxxxx
            case 'EditAdvertismentUser':
                parent::loadData($viewName, $view);
                
                // Guardamos que usuario y cuando pulsará guardar
                $this->views[$viewName]->model->user_nick = $this->user->nick;

             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");
                
                // Partimos la fechas en dia y hora para usar los widget de dia y hora (campos inicio y fin)
                if (!empty($this->views[$viewName]->model->inicio)){
                    $this->views[$viewName]->model->inicio_dia = date("Y-m-d", strtotime($this->views[$viewName]->model->inicio));
                    $this->views[$viewName]->model->inicio_hora = date("H:i:s", strtotime($this->views[$viewName]->model->inicio));
                } else {
                    // $this->views[$viewName]->model->inicio_dia = date("Y-m-d");
                    // $this->views[$viewName]->model->inicio_hora = date("H:i:s");
                    $this->views[$viewName]->model->inicio_dia = null;
                    $this->views[$viewName]->model->inicio_hora = null;
                }

                if (!empty($this->views[$viewName]->model->fin)){
                    $this->views[$viewName]->model->fin_dia = date("Y-m-d", strtotime($this->views[$viewName]->model->fin));
                    $this->views[$viewName]->model->fin_hora = date("H:i:s", strtotime($this->views[$viewName]->model->fin));
                } else {
                    // $this->views[$viewName]->model->fin_dia = date("Y-m-d");
                    // $this->views[$viewName]->model->fin_hora = date("H:i:s");
                    $this->views[$viewName]->model->fin_dia = null;
                    $this->views[$viewName]->model->fin_hora = null;
                }
                
                break;
        }
    }
    
}
