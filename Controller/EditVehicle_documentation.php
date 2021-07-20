<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditVehicle_documentation extends EditController {

    use \FacturaScripts\Core\Lib\ExtendedController\DocFilesTrait;

    public function getModelClassName() {
        return 'Vehicle_documentation';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Doc. Vehículo';
        
        $pageData['icon'] = 'far fa-file-pdf';

        return $pageData;
    }
    
    protected function createViews()
    {
        parent::createViews();
        $this->setTabsPosition('top');

        $this->createViewDocFiles();
    }

    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {
            case 'docfiles':
                $this->loadDataDocFiles($view, $this->getModelClassName(), $this->getModel()->primaryColumnValue());
                break;

            // Pestaña con el mismo nombre que este controlador EditXxxxx
            case 'EditVehicle_documentation': 
                parent::loadData($viewName, $view);
                
                // Guardamos que usuario y cuando pulsará guardar
                $this->views[$viewName]->model->user_nick = $this->user->nick;

             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");
                
                break;
            
            default:
                parent::loadData($viewName, $view);
                break;
            
        }
    }

    // Para ejecutar acciones que vienen de .html.twig
    protected function execPreviousAction($action)
    {
        switch ($action) {
            case 'add-file':
                return $this->addFileAction();

            case 'delete-file':
                return $this->deleteFileAction();

            case 'edit-file':
                return $this->editFileAction();

            case 'unlink-file':
                return $this->unlinkFileAction();
        }

        return parent::execPreviousAction($action);
    }
    
}
