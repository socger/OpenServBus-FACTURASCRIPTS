<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

 use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditService_regular extends EditController {
    
    public function getModelClassName() {
        return 'Service_regular';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();

        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Serv. regular';

        $pageData['icon'] = 'fas fa-book-open';
        

        return $pageData;
    }
    
    protected function createViews() {
        parent::createViews();
        
        $this->createViewService_regular_period();
        $this->createViewService_regular_itinerary();
        
        $this->setTabsPosition('top'); // Las posiciones de las pestañas pueden ser left, top, down
    }
    
    protected function createViewService_regular_itinerary($model = 'Service_regular_itinerary')
    {
        // $this->addListView($viewName, $modelName, $viewTitle, $viewIcon)
        // $viewName: el identificador o nombre interno de esta pestaña o sección. Por ejemplo: ListProducto.
        // $modelName: el nombre del modelo que usará este listado. Por ejemplo: Producto.
        // $viewTitle: el título de la pestaña o sección. Será tarducido. Por ejemplo: products.
        // $viewIcon: (opcional) el icono a utilizar. Por ejemplo: fas fa-search.
        $this->addListView('List' . $model, $model, 'Itinerarios', 'fas fa-road');    
        
        $this->views['List' . $model]->addOrderBy(['idservice_regular', 'orden'], 'Por itinerario', 1);
        $this->views['List' . $model]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        
        // Filtro de TIPO SELECT para filtrar por registros activos (SI, NO, o TODOS)
        // Sustituimos el filtro activo (checkBox) por el filtro activo (select)
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views['List' . $model]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->views['List' . $model]->addFilterAutocomplete('xIdservice_regular', 'Servicio regular', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
        $this->views['List' . $model]->addFilterAutocomplete('xIdstop', 'Parada', 'idstop', 'stops', 'idstop', 'nombre');
    }
    
    protected function createViewService_regular_period($model = 'Service_regular_period')
    {
        // $this->addListView($viewName, $modelName, $viewTitle, $viewIcon)
        // $viewName: el identificador o nombre interno de esta pestaña o sección. Por ejemplo: ListProducto.
        // $modelName: el nombre del modelo que usará este listado. Por ejemplo: Producto.
        // $viewTitle: el título de la pestaña o sección. Será tarducido. Por ejemplo: products.
        // $viewIcon: (opcional) el icono a utilizar. Por ejemplo: fas fa-search.
        $this->addListView('List' . $model, $model, 'Periodos', 'fas fa-calendar-day');    

        $this->views['List' . $model]->addOrderBy(['idservice_regular', 'fecha_desde', 'fecha_hasta', 'hora_desde', 'hora_hasta'], 'Por periodo', 1);
        $this->views['List' . $model]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        

        // Filtro de TIPO SELECT para filtrar por registros activos (SI, NO, o TODOS)
        // Sustituimos el filtro activo (checkBox) por el filtro activo (select)
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views['List' . $model]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);
        
        // Filtro de TIPO SELECT para filtrar por salida desde nave
        $salidaDesdeNave = [
            ['code' => '1', 'description' => 'Salida desde nave = SI'],
            ['code' => '0', 'description' => 'Salida desde nave = NO'],
        ];
        $this->views['List' . $model]->addFilterSelect('salidaDesdeNave', 'Salida desde nave = TODOS', 'salida_desde_nave_sn', $salidaDesdeNave);
        
        $this->views['List' . $model]->addFilterAutocomplete('xIdservice_regular', 'Servicio regular', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
    }
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {
            case 'ListService_regular_itinerary':
                $idservice_regular = $this->getViewModelValue('EditService_regular', 'idservice_regular');
                $where = [new DatabaseWhere('idservice_regular', $idservice_regular)];
                $view->loadData('', $where);
                break;
            
            case 'ListService_regular_period':
                $idservice_regular = $this->getViewModelValue('EditService_regular', 'idservice_regular');
                $where = [new DatabaseWhere('idservice_regular', $idservice_regular)];
                $view->loadData('', $where);
                break;
            
            // Pestaña con el mismo nombre que este controlador EditXxxxx
            case 'EditService_regular': 
                parent::loadData($viewName, $view);
                
                // Guardamos que usuario pulsará guardar
                $this->views[$viewName]->model->user_nick = $this->user->nick;

                // Guardamos cuando el usuario pulsará guardar
             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");
                
                break;
        }
    }


    // ** *************************************** ** //
    // ** FUNCIONES CREADAS PARA ESTE CONTROLADOR ** //
    // ** *************************************** ** //
        
    
}
