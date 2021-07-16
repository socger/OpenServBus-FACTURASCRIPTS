<?php
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListVehicle extends ListController {
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Empleados
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pageData['menu'] = 'OpenServBus';
        $pageData['submenu'] = 'Vehículos';
        $pageData['title'] = 'Vehículos';
        
        $pageData['icon'] = 'fas fa-bus-alt';


        return $pageData;
    }
    
    protected function createViews() {
        $this->createViewVehicle();
    }
    
    protected function createViewVehicle($viewName = 'ListVehicle')
    {
        $this->addView($viewName, 'Vehicle');
        
        // Opciones de búsqueda rápida
        $this->addSearchFields($viewName, ['cod_vehicle', 'nombre','matricula']); // Las búsqueda la hará por el campo nombre y por el campo direccion
        
        // Tipos de Ordenación
            // Primer parámetro es la pestaña
            // Segundo parámetro es los campos por los que ordena (array)
            // Tercer parámetro es la etiqueta a poner
            // Cuarto parámetro, si se rellena, le está diciendo cual es el order by por defecto, y además las opciones son
               // 1 Orden ascendente
               // 2 Orden descendente
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['cod_vehicle'], 'Código');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        
        // Filtros
        // Filtro checkBox por campo Activo ... addFilterCheckbox($viewName, $key, $label, $field);
            // $viewName ... nombre del controlador
            // $key ... es el nombre que le ponemos al filtro
            // $label ... la etiqueta a mostrar al usuario
            // $field ... el campo del modelo sobre el que vamos a comprobar
        $this->addFilterCheckbox($viewName, 'activo', 'Ver sólo los activos', 'activo');
        $this->addFilterCheckbox($viewName, 'collaborator', 'Ver sólo colaboradores', 'idcollaborator', 'IS NOT', null);
        
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
        $this->addFilterAutocomplete($viewName, 'xIdGarage', 'Cochera', 'idgarage', 'garages', 'idgarage', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xidfuel_type', 'T.Combustible', 'idfuel_type', 'fuel_types', 'idfuel_type', 'nombre');

        // $this->addFilterAutocomplete($viewName, 'xIdCollaborator', 'Colaborador', 'idcollaborator', 'collaborators', 'idcollaborator', 'codproveedor');
        $this->addFilterAutocomplete($viewName, 'xIdCollaborator', 'Colaborador', 'idcollaborator', 'collaborators', 'idcollaborator', 'nombre');
        
        $this->addFilterAutocomplete($viewName, 'xIdvehicle_type', 'Tipo vehículo', 'idvehicle_type', 'vehicle_types', 'idvehicle_type', 'nombre');
        
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
