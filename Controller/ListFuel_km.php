<?php
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListFuel_km extends ListController {
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Empleados
    public function getPageData(): array {
        $pageData = parent::getPageData();
        
        $pageData['menu'] = 'OpenServBus';
        $pageData['submenu'] = 'Repostajes / kms';
        $pageData['title'] = 'Repostajes / kms';
        
        $pageData['icon'] = 'fas fa-gas-pump';


        return $pageData;
    }
    
    protected function createViews() {
        $this->createViewFuel_km();
    }
    
    protected function createViewFuel_km($viewName = 'ListFuel_km')
    {
        $this->addView($viewName, 'Fuel_km');
        
        // Opciones de búsqueda rápida
        // $this->addSearchFields($viewName, ['nombre']); // Las búsqueda la hará por el campo nombre
        
        // Tipos de Ordenación
            // Primer parámetro es la pestaña
            // Segundo parámetro es los campos por los que ordena (array)
            // Tercer parámetro es la etiqueta a poner
            // Cuarto parámetro, si se rellena, le está diciendo cual es el order by por defecto, y además las opciones son
               // 1 Orden ascendente
               // 2 Orden descendente
        $this->addOrderBy($viewName, ['fecha'], 'Fecha', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');
        
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
        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'Empresa', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdVehicle', 'Vehículo', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdFuel_Type', 'Combustible', 'idfuel_type', 'fuel_types', 'idfuel_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdFuel_Pumps', 'Surtidor Interno', 'idfuel_pump', 'fuel_pumps', 'idfuel_pump', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdDriver', 'Conductor', 'iddriver', 'drivers', 'iddriver', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'Empleado', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xCodProveedor', 'Proveedor', 'codproveedor', 'proveedores', 'codproveedor', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdTarjeta', 'Tarjeta', 'idtarjeta', 'tarjetas', 'idtarjeta', 'nombre');
        
        // Filtro periodo de fechas
        // addFilterPeriod($viewName, $key, $label, $field)
            // $key ... es el nombre que le ponemos al filtro
            // $label ... es la etiqueta a mostrar al cliente
            // $field ... es el campo sobre el que filtraremos
        $this->addFilterPeriod($viewName, 'porFecha', 'Fecha repostaje', 'fecha');
        
        // Filtro de fecha sin periodo
        // addFilterDatePicker($viewName, $key, $label, $field)

        // Filtro de TIPO SELECT para filtrar por SI ES O NO de pago, O TODOS
        $esDepositoLleno = [
            ['code' => '1', 'description' => 'Depósito lleno = SI'],
            ['code' => '0', 'description' => 'Depósito lleno = NO'],
        ];
        $this->addFilterSelect($viewName, 'esDepositoLleno', 'Depósito lleno = TODO', 'deposito_lleno', $esDepositoLleno);        
        
    }
}
