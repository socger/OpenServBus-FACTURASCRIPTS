<?php
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListDriver extends ListController {
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Empleados
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pageData['menu'] = 'OpenServBus';
        $pageData['submenu'] = 'Empleados';
        $pageData['title'] = 'Conductores';
        
        $pageData['icon'] = 'fas fa-user-astronaut';
        

        return $pageData;
    }
    
    protected function createViews() {
        $this->createViewDriver();
    }
    
    protected function createViewDriver($viewName = 'ListDriver')
    {
        $this->addView($viewName, 'Driver');
        
        // Opciones de búsqueda rápida
        $this->addSearchFields($viewName, ['idemployee', 'nombre']); // Las búsqueda la hará por el campo idemployee y nombre
        
        // Tipos de Ordenación
            // Primer parámetro es la pestaña
            // Segundo parámetro es los campos por los que ordena (array)
            // Tercer parámetro es la etiqueta a poner
            // Cuarto parámetro, si se rellena, le está diciendo cual es el order by por defecto, y además las opciones son
               // 1 Orden ascendente
               // 2 Orden descendente
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        
        // Filtros
        // Filtro checkBox por campo Activo ... addFilterCheckbox($viewName, $key, $label, $field);
            // $viewName ... nombre del controlador
            // $key ... es el nombre que le ponemos al filtro
            // $label ... la etiqueta a mostrar al usuario
            // $field ... el campo del modelo sobre el que vamos a comprobar
        $this->addFilterCheckbox($viewName, 'activo', 'Ver sólo los activos', 'activo');
        
        // Filtro periodo de fechas
        // addFilterPeriod($viewName, $key, $label, $field)
            // $key ... es el nombre que le ponemos al filtro
            // $label ... es la etiqueta a mostrar al cliente
            // $field ... es el campo sobre el que filtraremos
        $this->addFilterPeriod($viewName, 'porFechaAlta', 'Fecha de alta', 'fechaalta');

        $this->addFilterSelectWhere( $viewName
                                   , 'status'
                                   , [ ['label' => 'Colaboradores/Empleados - Todos', 'where' => []]
                                     , ['label' => 'Colaboradores sólo', 'where' => [new DataBaseWhere('idcollaborator', '0', '>')]]
                                     , ['label' => 'Empleados sólo', 'where' => [new DataBaseWhere('idemployee', '0', '>')]]
                                     ]
        );

        $this->addFilterAutocomplete($viewName, 'xIdEmpleado', 'Empleado', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdCollaborator', 'Colaborador', 'idcollaborator', 'collaborators', 'idcollaborator', 'nombre');

        
    }
}
