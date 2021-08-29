<?php
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListService_regular extends ListController {
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Empleados
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pageData['menu'] = 'OpenServBus';
        $pageData['submenu'] = 'Serv. regulares';
        $pageData['title'] = 'Servicios regulares';
        
        $pageData['icon'] = 'fas fa-book-open';

        return $pageData;
    }
    
    protected function createViews() {
        $this->createViewService_regular();
    }
    
    protected function createViewService_regular($viewName = 'ListService_regular')
    {
        $this->addView($viewName, 'Service_regular');
        
        // Opciones de búsqueda rápida
        $this->addSearchFields($viewName, ['cod_servicio', 'nombre']);
        
        // Tipos de Ordenación
            // Primer parámetro es la pestaña
            // Segundo parámetro es los campos por los que ordena (array)
            // Tercer parámetro es la etiqueta a poner
            // Cuarto parámetro, si se rellena, le está diciendo cual es el order by por defecto, y además las opciones son
               // 1 Orden ascendente
               // 2 Orden descendente
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['cod_servicio'], 'Código');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        
        // Filtros
        // Filtro checkBox por campo Activo ... addFilterCheckbox($viewName, $key, $label, $field);
            // $viewName ... nombre del controlador
            // $key ... es el nombre que le ponemos al filtro
            // $label ... la etiqueta a mostrar al usuario
            // $field ... el campo del modelo sobre el que vamos a comprobar
        // $this->addFilterCheckbox($viewName, 'conductor', 'Ver sólo conductores', 'driver_yn');
        // $this->addFilterCheckbox($viewName, 'activo', 'Ver sólo los activos', 'activo');

         $this->addFilterCheckbox($viewName, 'lunes', 'Lunes', 'lunes');
         $this->addFilterCheckbox($viewName, 'martes', 'Martes', 'martes');
         $this->addFilterCheckbox($viewName, 'miercoles', 'Miercoles', 'miercoles');
         $this->addFilterCheckbox($viewName, 'jueves', 'Jueves', 'jueves');
         $this->addFilterCheckbox($viewName, 'viernes', 'Viernes', 'viernes');
         $this->addFilterCheckbox($viewName, 'sabado', 'Sábado', 'sabado');
         $this->addFilterCheckbox($viewName, 'domingo', 'Domingo', 'domingo');

        // Filtro de TIPO SELECT para filtrar por registros activos (SI, NO, o TODOS)
        $aceptados = [
            ['code' => '1', 'description' => 'Aceptados = SI'],
            ['code' => '0', 'description' => 'Aceptados = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloAceptados', 'Aceptados = TODOS', 'aceptado', $aceptados);        

        // Filtro de TIPO SELECT para filtrar por registros activos (SI, NO, o TODOS)
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);        

        // Filtro de TIPO SELECT para filtrar por SERVICIOS REGULARES FACTURABLES (SI, NO, o TODOS)
        $crearFtraSN = [
            ['code' => '1', 'description' => 'Crear ftra. = SI'],
            ['code' => '0', 'description' => 'Crear ftra. = NO'],
        ];
        $this->addFilterSelect($viewName, 'crearFtra', 'Crear ftra. = TODOS', 'facturar_SN', $crearFtraSN);        

        // Filtro de TIPO SELECT para filtrar por SERVICIOS REGULARES facturar agrupando (SI, NO, o TODOS)
        $facturarAgrupandoSN = [
            ['code' => '1', 'description' => 'Ftra.agrupando = SI'],
            ['code' => '0', 'description' => 'Ftra.agrupando = NO'],
        ];
        $this->addFilterSelect($viewName, 'facturarAgrupando', 'Ftra.agrupando = TODOS', 'facturar_agrupando', $facturarAgrupandoSN);        

        // Filtro de TIPO SELECT para filtrar por SERVICIOS REGULARES facturar agrupando (SI, NO, o TODOS)
        $combinadosSN = [
            ['code' => '1', 'description' => 'Combinado = SI'],
            ['code' => '0', 'description' => 'Combinado = NO'],
        ];
        $this->addFilterSelect($viewName, 'xCombinadoSN', 'Combinado = TODOS', 'combinadoSN', $combinadosSN);        

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
        $this->addFilterAutocomplete($viewName, 'xCodCliente', 'Cliente', 'codcliente', 'clientes', 'codcliente', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdvehicle_type', 'Vehículo - tipo', 'idvehicle_type', 'vehiculos', 'idvehicle_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdhelper', 'Monitor/a', 'idhelper', 'helpers', 'idhelper', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdservice_type', 'Servicio - tipo', 'idservice_type', 'service_types', 'idservice_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdempresa', 'Empresa', 'idempresa', 'empresas', 'idempresa', 'nombre');
        
        
        
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
