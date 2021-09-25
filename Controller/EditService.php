<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditService extends EditController {
    
    public function getModelClassName() {
        return 'Service';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();

        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Servicio discrecional';

        $pageData['icon'] = 'fas fa-book-reader';
        

        return $pageData;
    }
    
    protected function createViews() {
        parent::createViews();
        
        $this->createViewContacts();
        $this->createViewItineraries();
        $this->createViewValuations();
        
        $this->setTabsPosition('top'); // Las posiciones de las pestañas pueden ser left, top, down
    }
    
    protected function createViewContacts(string $viewName = 'EditDireccionContacto')
    {
        $this->addEditListView($viewName, 'Contacto', 'addresses-and-contacts', 'fas fa-address-book');
        $this->views[$viewName]->setInLine(true);
    }

    protected function createViewItineraries($model = 'Service_itinerary')
    {
        // $this->addListView($viewName, $modelName, $viewTitle, $viewIcon)
        // $viewName: el identificador o nombre interno de esta pestaña o sección. Por ejemplo: ListProducto.
        // $modelName: el nombre del modelo que usará este listado. Por ejemplo: Producto.
        // $viewTitle: el título de la pestaña o sección. Será tarducido. Por ejemplo: products.
        // $viewIcon: (opcional) el icono a utilizar. Por ejemplo: fas fa-search.
        $this->addListView('List' . $model, $model, 'Itinerarios', 'fas fa-road');    

        $this->views['List' . $model]->addSearchFields(['nombre']);

        
        $this->views['List' . $model]->addOrderBy(['idservice', 'orden'], 'Por itinerario', 1);
        $this->views['List' . $model]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        
        // Filtro de TIPO SELECT para filtrar por registros activos (SI, NO, o TODOS)
        // Sustituimos el filtro activo (checkBox) por el filtro activo (select)
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views['List' . $model]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->views['List' . $model]->addFilterAutocomplete('xIdservice', 'Servicio discrecional', 'idservice', 'services', 'idservice', 'nombre');
    }
    
    protected function createViewValuations($model = 'Service_valuation')
    {
        // $this->addListView($viewName, $modelName, $viewTitle, $viewIcon)
        // $viewName: el identificador o nombre interno de esta pestaña o sección. Por ejemplo: ListProducto.
        // $modelName: el nombre del modelo que usará este listado. Por ejemplo: Producto.
        // $viewTitle: el título de la pestaña o sección. Será tarducido. Por ejemplo: products.
        // $viewIcon: (opcional) el icono a utilizar. Por ejemplo: fas fa-search.
        $this->addListView('List' . $model, $model, 'Valoraciones', 'fas fa-dollar-sign');    
        
        $this->views['List' . $model]->addOrderBy(['idservice', 'orden'], 'Por valoración', 1);
        $this->views['List' . $model]->addOrderBy(['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        
        // Filtro de TIPO SELECT para filtrar por registros activos (SI, NO, o TODOS)
        // Sustituimos el filtro activo (checkBox) por el filtro activo (select)
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->views['List' . $model]->addFilterSelect('soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->views['List' . $model]->addFilterAutocomplete('xIdservice', 'Servicio discrecional', 'idservice', 'services', 'idservice', 'nombre');
        $this->views['List' . $model]->addFilterAutocomplete('xIdservice_valuation_type', 'Conceptos - valoración', 'idservice_valuation_type', 'service_valuation_types', 'idservice_valuation_type', 'nombre');
    }
    
    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {
            case 'EditDireccionContacto':
                $codcliente = $this->getViewModelValue('EditService', 'codcliente');
                $where = [new DatabaseWhere('codcliente', $codcliente)];
                $view->loadData('', $where);
                break;
            
            case 'ListService_itinerary':
                $idservice = $this->getViewModelValue('EditService', 'idservice');
                $where = [new DatabaseWhere('idservice', $idservice)];
                $view->loadData('', $where);
                break;
            
            case 'ListService_valuation':
                $idservice = $this->getViewModelValue('EditService', 'idservice');
                $where = [new DatabaseWhere('idservice', $idservice)];
                $view->loadData('', $where);
                break;
            
            // Pestaña con el mismo nombre que este controlador EditXxxxx
            case 'EditService': 
                parent::loadData($viewName, $view);
                
                // Guardamos que usuario pulsará guardar
                $this->views[$viewName]->model->user_nick = $this->user->nick;

                // Guardamos cuando el usuario pulsará guardar
             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");
                
                $this->prepararFechasParaVista($viewName);
                $this->prepararHorasParaVista($viewName);

                if ($this->views[$viewName]->model->aceptado === true) {
                    $this->views[$viewName]->model->aceptado_text = 'SI';
                } else {
                    $this->views[$viewName]->model->aceptado_text = 'NO';
                }
                
                if ($this->views[$viewName]->model->salida_desde_nave_sn === true) {
                    $this->views[$viewName]->model->salida_desde_nave_text = 'SI';
                } else {
                    $this->views[$viewName]->model->salida_desde_nave_text = 'NO';
                }
                
                if ($this->views[$viewName]->model->fuera_del_municipio === true) {
                    $this->views[$viewName]->model->fuera_del_municipio_text = 'SI';
                } else {
                    $this->views[$viewName]->model->fuera_del_municipio_text = 'NO';
                }
                
                if ($this->views[$viewName]->model->facturar_SN === true) {
                    $this->views[$viewName]->model->facturar_SN_text = 'SI';
                } else {
                    $this->views[$viewName]->model->facturar_SN_text = 'NO';
                }
                
                if ($this->views[$viewName]->model->activo === true) {
                    $this->views[$viewName]->model->activo_text = 'SI';
                } else {
                    $this->views[$viewName]->model->activo_text = 'NO';
                }
                
                $this->readOnlyFields($viewName);
                break;
        }
    }


    // ** *************************************** ** //
    // ** FUNCIONES CREADAS PARA ESTE CONTROLADOR ** //
    // ** *************************************** ** //
    private function readOnlyField($viewName, $fieldName)
    {
        $column = $this->views[$viewName]->columnForField($fieldName);
        $column->widget->readonly = 'true';
    }

    private function displayNoneField($viewName, $fieldName)
    {
        $column = $this->views[$viewName]->columnForField($fieldName);
        $column->display = 'none';
    }

    private function readOnlyFields($viewName)
    {
        if (!empty($this->views[$viewName]->model->idfactura)) 
        { // Está facturado el servicio
            $this->readOnlyField($viewName, 'idservice');
            $this->readOnlyField($viewName, 'nombre');
            $this->readOnlyField($viewName, 'plazas');
            $this->readOnlyField($viewName, 'codcliente');
            $this->readOnlyField($viewName, 'idvehicle_type');
            $this->readOnlyField($viewName, 'idhelper');
            $this->readOnlyField($viewName, 'hoja_ruta_origen');
            $this->readOnlyField($viewName, 'hoja_ruta_destino');
            $this->readOnlyField($viewName, 'hoja_ruta_expediciones');
            $this->readOnlyField($viewName, 'hoja_ruta_contratante');
            $this->readOnlyField($viewName, 'hoja_ruta_tipoidfiscal');
            $this->readOnlyField($viewName, 'hoja_ruta_cifnif');
            $this->readOnlyField($viewName, 'idservice_type');
            $this->readOnlyField($viewName, 'idempresa');
            $this->readOnlyField($viewName, 'idfactura');
            $this->readOnlyField($viewName, 'importe');
            $this->readOnlyField($viewName, 'codimpuesto');
            $this->readOnlyField($viewName, 'importe_enextranjero');
            $this->readOnlyField($viewName, 'codimpuesto_enextranjero');
            $this->readOnlyField($viewName, 'total');
            $this->readOnlyField($viewName, 'codsubcuenta_km_nacional');
            $this->readOnlyField($viewName, 'codsubcuenta_km_extranjero');
            $this->readOnlyField($viewName, 'inicio_horaAnt');
            $this->readOnlyField($viewName, 'salida_desde_nave_sn');
            $this->readOnlyField($viewName, 'inicio_dia');
            $this->readOnlyField($viewName, 'inicio_hora');
            $this->readOnlyField($viewName, 'fin_dia');
            $this->readOnlyField($viewName, 'fin_hora');
            $this->readOnlyField($viewName, 'idvehicle');
            
            $this->readOnlyField($viewName, 'iddriver_1');
            $this->readOnlyField($viewName, 'driver_alojamiento_1');
            $this->readOnlyField($viewName, 'driver_observaciones_1');
            
            $this->readOnlyField($viewName, 'iddriver_2');
            $this->readOnlyField($viewName, 'driver_alojamiento_2');
            $this->readOnlyField($viewName, 'driver_observaciones_2');
            
            $this->readOnlyField($viewName, 'iddriver_3');
            $this->readOnlyField($viewName, 'driver_alojamiento_3');
            $this->readOnlyField($viewName, 'driver_observaciones_3');
            
            $this->readOnlyField($viewName, 'observaciones');
            $this->readOnlyField($viewName, 'observaciones_montaje');
            $this->readOnlyField($viewName, 'observaciones_drivers');
            $this->readOnlyField($viewName, 'observaciones_vehiculo');
            $this->readOnlyField($viewName, 'observaciones_facturacion');
            $this->readOnlyField($viewName, 'observaciones_liquidacion');
            $this->readOnlyField($viewName, 'motivobaja');
            
            // Invisibles
            $this->displayNoneField($viewName, 'aceptado');
            $this->displayNoneField($viewName, 'fuera_del_municipio');
            $this->displayNoneField($viewName, 'facturar_SN');
            $this->displayNoneField($viewName, 'salida_desde_nave_sn');
            $this->displayNoneField($viewName, 'activo');
        } else {
            // Invisibles
            $this->displayNoneField($viewName, 'aceptado_text');
            $this->displayNoneField($viewName, 'fuera_del_municipio_text');
            $this->displayNoneField($viewName, 'facturar_SN_text');
            $this->displayNoneField($viewName, 'salida_desde_nave_text');
            $this->displayNoneField($viewName, 'activo_text');
        }
    }

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
