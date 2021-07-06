<?php
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListFuel_type extends ListController {
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Empleados
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pageData['menu'] = 'OpenServBus';
        $pageData['submenu'] = 'Archivos';
        $pageData['title'] = 'Tipos de Combustible';
        
        $pageData['icon'] = 'fas fa-gas-pump';


        return $pageData;
    }
    
    protected function createViews() {
        $this->createViewFuel_type();
    }
    
    protected function createViewFuel_type($viewName = 'ListFuel_type')
    {
        $this->addView($viewName, 'Fuel_type');
        
        // Opciones de búsqueda rápida
        $this->addSearchFields($viewName, ['nombre']); // Las búsqueda la hará por el campo nombre
        
        // Tipos de Ordenación
            // Primer parámetro es la pestaña
            // Segundo parámetro es los campos por los que ordena (array)
            // Tercer parámetro es la etiqueta a poner
            // Cuarto parámetro, si se rellena, le está diciendo cual es el order by por defecto, y además las opciones son
               // 1 Orden ascendente
               // 2 Orden descendente
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 2);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        
        // Filtros
        // Filtro checkBox por campo Activo ... addFilterCheckbox($viewName, $key, $label, $field);
            // $viewName ... nombre del controlador
            // $key ... es el nombre que le ponemos al filtro
            // $label ... la etiqueta a mostrar al usuario
            // $field ... el campo del modelo sobre el que vamos a comprobar
        $this->addFilterCheckbox($viewName, 'activo', 'Activo', 'activo');
        
        // Filtro periodo de fechas
        // addFilterPeriod($viewName, $key, $label, $field)
            // $key ... es el nombre que le ponemos al filtro
            // $label ... es la etiqueta a mostrar al cliente
            // $field ... es el campo sobre el que filtraremos
        $this->addFilterPeriod($viewName, 'porFechaAlta', 'Fecha de alta', 'fechaalta');
        
    }
}
