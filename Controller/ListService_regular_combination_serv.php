<?php
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListService_regular_combination_serv extends ListController {
    
    /* Esta parte la quitamos porque ya no será una opción del menú principal, sino que estará dentro del mantenimiento de SERVICIOS REGULARES - COMBINACIONES
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pageData['menu'] = 'OpenServBus';
        $pageData['submenu'] = 'Serv. regulares';
        $pageData['title'] = 'Combinaciones - Servicios';
        
        $pageData['icon'] = 'fas fa-cogs';
        

        return $pageData;
    }
    */
    
    protected function createViews() {
        $this->createViewService_regular_combination_serv();
    }
    
    protected function createViewService_regular_combination_serv($viewName = 'ListService_regular_combination_serv')
    {
        $this->addView($viewName, 'Service_regular_combination_serv');
        
        // Opciones de búsqueda rápida
        // $this->addSearchFields($viewName, ['nombre']);
        
        // Tipos de Ordenación
            // Primer parámetro es la pestaña
            // Segundo parámetro es los campos por los que ordena (array)
            // Tercer parámetro es la etiqueta a poner
            // Cuarto parámetro, si se rellena, le está diciendo cual es el order by por defecto, y además las opciones son
               // 1 Orden ascendente
               // 2 Orden descendente
        $this->addOrderBy($viewName, ['idservice_regular_combination', 'idservice_regular'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        
        // Filtros
        // Filtro checkBox por campo Activo ... addFilterCheckbox($viewName, $key, $label, $field);
            // $viewName ... nombre del controlador
            // $key ... es el nombre que le ponemos al filtro
            // $label ... la etiqueta a mostrar al usuario
            // $field ... el campo del modelo sobre el que vamos a comprobar
        // $this->addFilterCheckbox($viewName, 'activo', 'Ver sólo los activos', 'activo');
     
        // Filtro de TIPO SELECT para filtrar por registros activos (SI, NO, o TODOS)
        // Sustituimos el filtro activo (checkBox) por el filtro activo (select)
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);        

        // Filtro periodo de fechas
        // addFilterPeriod($viewName, $key, $label, $field)
            // $key ... es el nombre que le ponemos al filtro
            // $label ... es la etiqueta a mostrar al cliente
            // $field ... es el campo sobre el que filtraremos
        // $this->addFilterPeriod($viewName, 'porFechaAlta', 'Fecha de alta', 'fechaalta');

        $this->addFilterAutocomplete($viewName, 'xIdDriver', 'driver', 'iddriver', 'drivers', 'iddriver', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdVehicle', 'vehicle', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdservice_regular_combination', 'combination-service', 'idservice_regular_combination', 'service_regular_combinations', 'idservice_regular_combination', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdservice_regular', 'service-regular', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
    }
    
}
