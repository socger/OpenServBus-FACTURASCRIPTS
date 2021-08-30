<?php
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListService_itinerary extends ListController {

    /* Esta parte no hace falta puesto que no vamos a tener este controlador como parte del menú de OpenServBus
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pageData['menu'] = 'OpenServBus';
        $pageData['submenu'] = 'Serv. discrecionales';
        $pageData['title'] = 'Serv. discrecionales - Itinerarios';
        
        $pageData['icon'] = 'fas fa-road';
        
        return $pageData;
    }
    */
    
    protected function createViews() {
        $this->createViewService_itinerary();
    }
    
    protected function createViewService_itinerary($viewName = 'ListService_itinerary')
    {
        $this->addView($viewName, 'Service_itinerary');
        
        // Opciones de búsqueda rápida
        $this->addSearchFields($viewName, ['nombre']);
        
        // Tipos de Ordenación
            // Primer parámetro es la pestaña
            // Segundo parámetro es los campos por los que ordena (array)
            // Tercer parámetro es la etiqueta a poner
            // Cuarto parámetro, si se rellena, le está diciendo cual es el order by por defecto, y además las opciones son
               // 1 Orden ascendente
               // 2 Orden descendente
        $this->addOrderBy($viewName, ['idservice', 'orden'], 'Por itinerario', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        
        // Filtros
        // Filtro checkBox por campo Activo ... addFilterCheckbox($viewName, $key, $label, $field);
            // $viewName ... nombre del controlador
            // $key ... es el nombre que le ponemos al filtro
            // $label ... la etiqueta a mostrar al usuario
            // $field ... el campo del modelo sobre el que vamos a comprobar
        // $this->addFilterCheckbox($viewName, 'conductor', 'Ver sólo conductores', 'driver_yn');
        // $this->addFilterCheckbox($viewName, 'activo', 'Ver sólo los activos', 'activo');

        // Filtro de TIPO SELECT para filtrar por registros activos (SI, NO, o TODOS)
        // Sustituimos el filtro activo (checkBox) por el filtro activo (select)
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);        

        // Filtro autoComplete ... addFilterAutocomplete($viewName, $key, $label, $field, $table, $fieldcode, $fieldtitle)
        // Aunque lo vamos a hacer sobre la tabla empresa que normalmente tiene pocos registros
        // este tipo de filtros está pensado para tablas como clientes, proveedores, etc que tengan muchos registros
        // Para estas tablas no vamos a usar un filtro Select ... faltaría memoria al equipo para ello
            // $viewName ... nombre del controlador
            // $key ... es el nombre que le ponemos al filtro, que puede ser el campo sobre el que quiero filtrar
            // $label ...  parámetro es la etiqueta a mostrar al usuario
            // $field ... es el campo del modelo
            // $table ... es el nombre de la tabla en la BD
            // $fieldcode ... es el campo interno que quiero consultar
            // $fieldtitle ... es el campo a mostar al usuario
        $this->addFilterAutocomplete($viewName, 'xIdservice', 'Serv. discrecional', 'idservice', 'services', 'idservice', 'nombre');
        
        
        
        // Filtro periodo de fechas
        // addFilterPeriod($viewName, $key, $label, $field)
            // $key ... es el nombre que le ponemos al filtro
            // $label ... es la etiqueta a mostrar al cliente
            // $field ... es el campo sobre el que filtraremos
        // $this->addFilterPeriod($viewName, 'porFechaAlta', 'Fecha de alta', 'fechaalta');
        
        // Filtro de fecha sin periodo
        // addFilterDatePicker($viewName, $key, $label, $field)
    }
    
}
