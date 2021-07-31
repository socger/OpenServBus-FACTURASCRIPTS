<?php
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListEmployee_attendance_management extends ListController {
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Empleados
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pageData['menu'] = 'OpenServBus';
        $pageData['submenu'] = 'Control presencial';
        $pageData['title'] = 'Fichajes / Asistencia';
        
        $pageData['icon'] = 'fas fa-hourglass-half';


        return $pageData;
    }
    
    protected function createViews() {
        $this->createViewEmployee_attendance_management();
    }
    
    protected function createViewEmployee_attendance_management($viewName = 'ListEmployee_attendance_management')
    {
        $this->addView($viewName, 'Employee_attendance_management');
        
        // Tipos de Ordenación
            // Primer parámetro es la pestaña
            // Segundo parámetro es los campos por los que ordena (array)
            // Tercer parámetro es la etiqueta a poner
            // Cuarto parámetro, si se rellena, le está diciendo cual es el order by por defecto, y además las opciones son
               // 1 Orden ascendente
               // 2 Orden descendente
        $this->addOrderBy($viewName, ['fecha'], 'Fecha', 1);
        $this->addOrderBy($viewName, ['idemployee', 'fecha'], 'Empleado + Fecha');
        
        // Filtros
        // Filtro checkBox por campo Activo ... addFilterCheckbox($viewName, $key, $label, $field);
            // $viewName ... nombre del controlador
            // $key ... es el nombre que le ponemos al filtro
            // $label ... la etiqueta a mostrar al usuario
            // $field ... el campo del modelo sobre el que vamos a comprobar
        $this->addFilterCheckbox($viewName, 'activo', 'Ver sólo los activos', 'activo');
        
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
        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'Empleado', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdidentification_mean', 'Identificacion - medio', 'ididentification_mean', 'identification_means', 'ididentification_mean', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdabsence_reason', 'Ausencia - motivo', 'idabsence_reason', 'absence_reasons', 'idabsence_reason', 'nombre');
        
        
        // Filtro periodo de fechas
        // addFilterPeriod($viewName, $key, $label, $field)
            // $key ... es el nombre que le ponemos al filtro
            // $label ... es la etiqueta a mostrar al cliente
            // $field ... es el campo sobre el que filtraremos
        $this->addFilterPeriod($viewName, 'porFecha', 'Fecha', 'fecha');
        
        // Filtro de fecha sin periodo
        // addFilterDatePicker($viewName, $key, $label, $field)

        // Filtro de TIPO SELECT para filtrar por si fué importado desde una aplicación externa o si fué introducido manualmente
        $origen = [
            ['code' => '0', 'description' => 'Origen = EXTERNO'],
            ['code' => '1', 'description' => 'Origen = MANUAL'],
        ];
        $this->addFilterSelect($viewName, 'elOrigen', 'Origen = TODOS', 'origen', $origen);        
     
        // Filtro de TIPO SELECT para filtrar por el tipo de fichaje (ENTRADAS, SALIDAS o TODOS)
        $origen = [
            ['code' => '1', 'description' => 'Tipo fichaje = ENTRADA'],
            ['code' => '0', 'description' => 'Tipo fichaje = SALIDA'],
        ];
        $this->addFilterSelect($viewName, 'elTipoFichaje', 'Tipo fichaje = TODOS', 'tipoFichaje', $origen);        
     
    }
    
}
