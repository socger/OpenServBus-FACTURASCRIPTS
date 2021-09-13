<?php
    
// SI MODIFICAMOS ESTE CONTROLADOR TENEMOS QUE VER SI HAY QUE HACER LOS MISMOS CAMBIOS EN ListAdvertisment_user2.php
    
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListAdvertisment_user extends ListController {
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Empleados
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pageData['menu'] = 'OpenServBus';
        $pageData['submenu'] = 'Avisos';
        $pageData['title'] = 'Avisos';
        
        $pageData['icon'] = 'fas fa-exclamation-triangle';


        return $pageData;
    }
    
    protected function createViews() {
        $this->createAdvertisment_user();

        /*
        $this->addListView($viewName, $modelName, $viewTitle, $viewIcon)
         * 
        $viewName: el identificador o nombre interno de esta pestaña o sección. Por ejemplo: ListProducto.
        $modelName: el nombre del modelo que usará este listado. Por ejemplo: Producto.
        $viewTitle: el título de la pestaña o sección. Será tarducido. Por ejemplo: products.
        $viewIcon: (opcional) el icono a utilizar. Por ejemplo: fas fa-search.
        */
        //$this->addListView('ListAdvertisment_user2', 'Advertisment_user', 'Avisos para el usuario de la sesión');    
        
        //$this->setTabsPosition('top'); // Las posiciones de las pestañas pueden ser left, top, down
    }
    
    protected function createAdvertisment_user($viewName = 'ListAdvertisment_user')
    {
        $this->addView($viewName, 'Advertisment_user');
        
        // Opciones de búsqueda rápida
        $this->addSearchFields($viewName, ['nombre']); // Las búsqueda la hará por el campo nombre
        
        // Tipos de Ordenación
            // Primer parámetro es la pestaña
            // Segundo parámetro es los campos por los que ordena (array)
            // Tercer parámetro es la etiqueta a poner
            // Cuarto parámetro, si se rellena, le está diciendo cual es el order by por defecto, y además las opciones son
               // 1 Orden ascendente
               // 2 Orden descendente
        // $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['nombre', 'inicio', 'fin'], 'Aviso + Inicio + Fin', 1);
        $this->addOrderBy($viewName, ['nick', 'nombre', 'inicio', 'fin'], 'Usuario + Aviso + Inicio + Fin');
        $this->addOrderBy($viewName, ['codrole', 'nombre', 'inicio', 'fin'], 'Grupo Usuarios + Aviso + Inicio + Fin');

        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

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
        $this->addFilterAutocomplete($viewName, 'xNick', 'Usuario', 'nick', 'users', 'nick', 'nick');
        $this->addFilterAutocomplete($viewName, 'xCodRole', 'Grupos de usuarios', 'codrole', 'roles', 'codrole', 'descripcion');
        
        
        // Filtro de fecha sin periodo
        // addFilterDatePicker($viewName, $key, $label, $field)
        $this->addFilterDatePicker($viewName, 'inicio', 'Avisos desde ...', 'inicio');
        $this->addFilterDatePicker($viewName, 'fin', 'Avisos hasta ...', 'fin');
                
                
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
        
    }

}
