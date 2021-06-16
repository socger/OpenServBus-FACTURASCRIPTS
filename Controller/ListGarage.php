<?php
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListGarage extends ListController {
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pageData['menu'] = 'OpenServBus';
        $pageData['submenu'] = 'Archivos';
        $pageData['title'] = 'Cocheras';
        
        $pageData['icon'] = 'fas fa-warehouse';

        return $pageData;
    }
    
    protected function createViews() {
        $this->createViewGarage();
    }
    
    protected function createViewGarage($viewName = 'ListGarage')
    {
        $this->addView($viewName, 'Garage');
        
        // Opciones de búsqueda rápida
        $this->addSearchFields($viewName, ['nombre','direccion']); // Las búsqueda la hará por el campo nombre y por el campo direccion
        
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
        
        // Filtro select por el campo useralta
            // Primero necesitamos los valores que quiero mostrar en el select
            // codeModel->all($tableName, $fieldCode, $fieldDescription) ... para buscar todos los usuarios
                // $tableName ... el nombre de la tabla
                // $fieldCode ... el nombre del campo de búsqueda/identificador (id)
                // $fieldDescription ... el nombre del campo a mostrar al usuario
        $usuarios = $this->codeModel->all('users', 'nick', 'nick');

            // addFilterSelect($viewName, $key, $label, $field, $values)
            // Filtro por selección
                // $viewName ... nombre del controlador
                // $key ... es el nombre que le ponemos al filtro
                // $label ... la etiqueta a mostrar al usuario
                // $field ... el campo del modelo sobre el que vamos a comprobar
                // $values ... son los valores que presentaremos para seleccionar ... los guardados en $usuarios
        $this->addFilterSelect($viewName, 'usuarios', 'Usuarios', 'useralta', $usuarios);
        
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
        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'Empresa', 'idempresa', 'empresas', 'idempresa', 'nombre');
        
        // Filtro periodo de fechas
        // addFilterPeriod($viewName, $key, $label, $field)
            // $key ... es el nombre que le ponemos al filtro
            // $label ... es la etiqueta a mostrar al cliente
            // $field ... es el campo sobre el que filtraremos
        $this->addFilterPeriod($viewName, 'porFechaAlta', 'Fecha de alta', 'fechaalta');
        
        // Filtro de fecha sin periodo
        // addFilterDatePicker($viewName, $key, $label, $field)
        
    }
}
