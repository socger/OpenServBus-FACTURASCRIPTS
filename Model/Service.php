<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Plugins\OpenServBus\Model\Vehicle_type;
use FacturaScripts\Plugins\OpenServBus\Model\Helper;
use FacturaScripts\Plugins\OpenServBus\Model\Service_type;
use FacturaScripts\Plugins\OpenServBus\Model\Driver;
use FacturaScripts\Plugins\OpenServBus\Model\Vehicle;
use FacturaScripts\Plugins\OpenServBus\Model\Service_valuation_type;

class Service extends Base\ModelClass {
    use Base\ModelTrait;

    public $idservice;
        
    public $user_fecha;
    public $user_nick;
    public $fechaalta;
    public $useralta;
    public $fechamodificacion;
    public $usermodificacion;
    public $activo;
    public $fechabaja;
    public $userbaja;
    public $motivobaja;

    public $nombre;
    public $aceptado;
    public $plazas;
    
    public $codcliente;
    public $idvehicle_type;
    public $idhelper;

    public $facturar_SN;
    
    public $importe;
    public $importe_enextranjero;
    public $codimpuesto;
    public $codimpuesto_enextranjero;
    public $total;
            
    public $fuera_del_municipio;
    
    public $hoja_ruta_origen;
    public $hoja_ruta_destino;
    public $hoja_ruta_expediciones;
    public $hoja_ruta_contratante;
    public $hoja_ruta_tipoidfiscal;
    public $hoja_ruta_cifnif;

    public $idservice_type;
    public $idempresa;
    
    public $observaciones;
    public $observaciones_montaje;
    public $observaciones_vehiculo;
    public $observaciones_facturacion;
    public $observaciones_liquidacion;
    public $observaciones_drivers;
    
    public $iddriver_1;
    public $driver_alojamiento_1;
    public $driver_observaciones_1;
    
    public $iddriver_2;
    public $driver_alojamiento_2;
    public $driver_observaciones_2;
    
    public $iddriver_3;
    public $driver_alojamiento_3;
    public $driver_observaciones_3;
    
    public $idvehicle;
    
    public $codsubcuenta_km_nacional;
    public $codsubcuenta_km_extranjero;
    
    public $fecha_desde;
    public $fecha_hasta;

    public $hora_anticipacion;
    public $hora_desde;
    public $hora_hasta;

    public $inicio_horaAnt;
    public $inicio_dia;
    public $inicio_hora;
    public $fin_dia;
    public $fin_hora;
    
    public $salida_desde_nave_sn;
//    public $observaciones_periodo;
    public $idfactura;
    
//    public $combinadoSN;
//    public $combinadoSiNo;
    
    public $llamadoDesdeFuera; // Para comprobar si se usa el metodo save desde otro sitio que no sea el controlador EditService.php
    
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->activo = true; // Por defecto estará activo

        $this->facturar_SN = true;
        $this->importe = 0;
        $this->importe_enextranjero = 0;
        $this->total = 0;
        $this->plazas = 0;
        $this->aceptado = false;

        $this->llamadoDesdeFuera = false; // Para comprobar si se usa el metodo save desde otro sitio que no sea el controlador EditService.php
    }
    
    /**
     * This function is called when creating the model table. Returns the SQL
     * that will be executed after the creation of the table. Useful to insert values
     * default.
     *
     * @return string
     */
    public function install()
    {
        /// needed dependency proveedores
        new Vehicle_type();
        new Helper();
        new Service_type();
        new Driver();
        new Vehicle();
        new Service_valuation_type();

        return parent::install();
    }

    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idservice';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'services';
    }

    // Para realizar cambios en los datos antes de guardar por modificación
    protected function saveUpdate(array $values = [])
    {
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }
        
        $idService = $this->idservice;
        $respuesta = parent::saveUpdate($values);
        if ($respuesta === true) {
            $this->actualizarServicioEnMontaje($idService);
        }
        return $respuesta;
    }

    // Para realizar cambios en los datos antes de guardar por alta
    protected function saveInsert(array $values = [])
    {
        // Creamos el nuevo id
        if (empty($this->idservice)) {
            $this->idservice = $this->newCode();
        }

        $this->rellenarDatosAlta();
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }

        $respuesta = parent::saveInsert($values);
        return $respuesta;
    }

    public function test() {
        if (true === $this->llamadoDesdeFuera) {
             // Está siendo usado el metodo save desde otro sitio que no es el controlador EditService.php
            return parent::test();
        }

        if ($this->checkFields() == false) {
            return false;
        }

        $this->rellenarTotal();

        $this->evitarInyeccionSQL();
        return parent::test();
    }


    // ** ********************************** ** //
    // ** FUNCIONES CREADAS PARA ESTE MODELO ** //
    // ** ********************************** ** //
    private function comprobarSiActivo()
    {
        $a_devolver = true;
        
        if ($this->activo == false) {
            $this->fechabaja = $this->fechamodificacion;
            $this->userbaja = $this->usermodificacion;
            
            if (empty($this->motivobaja)){
                $a_devolver = false;
                $this->toolBox()->i18nLog()->error('Si el registro no está activo, debe especificar el motivo.');
            }
        } else { // Por si se vuelve a poner Activo = true
            $this->fechabaja = null;
            $this->userbaja = null;
            $this->motivobaja = null;
        }
        return $a_devolver;
    }

    private function rellenarDatosModificacion()
    {
        $this->usermodificacion = $this->user_nick; 
        $this->fechamodificacion = $this->user_fecha; 
    }

    private function rellenarDatosAlta()
    {
        $this->useralta = $this->user_nick; 
        $this->fechaalta = $this->user_fecha; 
    }

    private function evitarInyeccionSQL()
    {
        $utils = $this->toolBox()->utils();
        $this->nombre = $utils->noHtml($this->nombre);

        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->observaciones_montaje = $utils->noHtml($this->observaciones_montaje);
        $this->observaciones_vehiculo = $utils->noHtml($this->observaciones_vehiculo);
        $this->observaciones_facturacion = $utils->noHtml($this->observaciones_facturacion);
        $this->observaciones_liquidacion = $utils->noHtml($this->observaciones_liquidacion);
        $this->observaciones_drivers = $utils->noHtml($this->observaciones_drivers);
        
        $this->hoja_ruta_origen = $utils->noHtml($this->hoja_ruta_origen);
        $this->hoja_ruta_destino = $utils->noHtml($this->hoja_ruta_destino);
        $this->hoja_ruta_expediciones = $utils->noHtml($this->hoja_ruta_expediciones);
        $this->hoja_ruta_contratante = $utils->noHtml($this->hoja_ruta_contratante);
        $this->hoja_ruta_tipoidfiscal = $utils->noHtml($this->hoja_ruta_tipoidfiscal);
        $this->hoja_ruta_cifnif = $utils->noHtml($this->hoja_ruta_cifnif);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        $this->codsubcuenta_km_nacional = $utils->noHtml($this->codsubcuenta_km_nacional);
        $this->codsubcuenta_km_extranjero = $utils->noHtml($this->codsubcuenta_km_extranjero);
        
        $this->driver_alojamiento_1 = $utils->noHtml($this->driver_alojamiento_1);
        $this->driver_observaciones_1 = $utils->noHtml($this->driver_observaciones_1);
        
        $this->driver_alojamiento_2 = $utils->noHtml($this->driver_alojamiento_2);
        $this->driver_observaciones_2 = $utils->noHtml($this->driver_observaciones_2);
        
        $this->driver_alojamiento_3 = $utils->noHtml($this->driver_alojamiento_3);
        $this->driver_observaciones_3 = $utils->noHtml($this->driver_observaciones_3);
    }
    
    public function rellenarTotal()
    {
        $cliente_RegimenIVA = '';
        $cliente_CodRetencion = '';
        $cliente_PorcentajeRetencion = 0.0;
        
        $this->total = $this->importe + $this->importe_enextranjero;
        
        // Traemos los datos del cliente sólo si hay algún importe y si hay algún tipo de impuesto
        if ($this->importe <> 0 || $this->importe_enextranjero <> 0) {
            if (!empty($this->codimpuesto) || !empty($this->codimpuesto_enextranjero)) { 
                // Cargar datos del cliente que nos interesan
                $sql = ' SELECT clientes.regimeniva '
                     .      ' , clientes.codretencion '
                     .      ' , retenciones.porcentaje '
                     . ' FROM clientes '
                     . ' LEFT JOIN retenciones ON (retenciones.codretencion = clientes.codretencion) '                        
                     . ' WHERE clientes.codcliente = "' . $this->codcliente . '" '
                     ;

                $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818
                
                foreach ($registros as $fila) {
                    $cliente_RegimenIVA = $fila['regimeniva'];
                    $cliente_CodRetencion = $fila['codretencion'];
                    $cliente_PorcentajeRetencion = $fila['porcentaje'];
                }
            }
        }

        $this->calcularImpuesto($this->importe, $this->codimpuesto, $cliente_RegimenIVA, $cliente_PorcentajeRetencion, $this->total);
        $this->calcularImpuesto($this->importe_enextranjero, $this->codimpuesto_enextranjero, $cliente_RegimenIVA, $cliente_PorcentajeRetencion, $this->total);
        
        $this->total = \round($this->total, (int) \FS_NF0);
        
    }
    
    private function crearFechaDesde()
    {
        $fecha = '';
        if ($this->inicio_dia <> '01-01-1970'){
            $fecha = $fecha . $this->inicio_dia;
        }
        $this->fecha_desde = $fecha;
    }

    private function crearFechaHasta()
    {
        $fecha = '';
        if ($this->fin_dia <> '01-01-1970'){
            $fecha = $fecha . $this->fin_dia;
        }
        $this->fecha_hasta = $fecha;
    }
    
    private function crearHoraAnticipacion()
    {
        $fecha = '';
        if ($this->inicio_dia <> '01-01-1970'){
            $fecha = $fecha . $this->inicio_dia;
        }
        
        if (!empty($this->inicio_horaAnt)){
            $fecha = $fecha . ' ' . $this->inicio_horaAnt;
        }
        $this->hora_anticipacion = $fecha;
    }

    private function crearHoraDesde()
    {
        $fecha = '';
        if ($this->inicio_dia <> '01-01-1970'){
            $fecha = $fecha . $this->inicio_dia;
        }
        
        if (!empty($this->inicio_hora)){
            $fecha = $fecha . ' ' . $this->inicio_hora;
        }
        $this->hora_desde = $fecha;
    }

    private function crearHoraHasta()
    {
        $fecha = '';
        if ($this->inicio_dia <> '01-01-1970'){
            $fecha = $fecha . $this->inicio_dia;
        }
        
        if (!empty($this->fin_hora)){
            $fecha = $fecha . ' ' . $this->fin_hora;
        }
        $this->hora_hasta = $fecha;
    }

    private function checkFechasPeriodo()
    {
        $a_devolver = true;
        
        // La fecha de inicio es obligatoria
        if (empty($this->fecha_desde)) 
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('La fecha de inicio, debe de introducirla.');
        }

        // Si fecha hasta está introducida y fecha desde no está vacía y además es mayor que fecha hasta ... fallo
        if (!empty($this->fecha_hasta)) 
        {
            if ( !empty($this->fecha_desde) and 
                 $this->fecha_desde > $this->fecha_hasta ) 
            {
                $a_devolver = false;
                $this->toolBox()->i18nLog()->error('La fecha de inicio, no puede ser mayor que la fecha de fin.');
            }
        }
        return $a_devolver;
    }
    
    private function checkHorasPeriodo()
    {
        $a_devolver = true;
        
        // La hora de inicio es obligatoria
        if (empty($this->hora_desde)) 
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('La hora de inicio, debe de introducirla.');
        }

        // La hora de fin es obligatoria
        if (empty($this->hora_hasta)) 
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('La hora fin, debe de introducirla.');
        }

        // Si fecha hasta está introducida y fecha desde no está vacía y además es mayor que fecha hasta ... fallo
        if (!empty($this->hora_hasta)) 
        {
            if ( !empty($this->hora_desde) and 
                 $this->hora_desde > $this->hora_hasta ) 
            {
                $a_devolver = false;
                $this->toolBox()->i18nLog()->error('La hora de inicio, no puede ser mayor que la hora de fin.');
            }
        }

        return $a_devolver;
    }
    
    private function calcularImpuesto($importe, $codimpuesto, $cliente_RegimenIVA, $cliente_PorcentajeRetencion, &$total) 
    {
        $impto_tipo = 0.0;
        $impto_IVA = 0.0;
        $impto_Recargo = 0.0;

        if ($importe <> 0) {
            if (!empty($codimpuesto)) { 
                // Cargar datos del impuesto que nos interesan
                $sql = ' SELECT impuestos.tipo '
                     .      ' , impuestos.iva '
                     .      ' , impuestos.recargo '
                     . ' FROM impuestos '
                     . ' WHERE impuestos.codimpuesto = "' . $codimpuesto . '" '
                     ;

                $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818
                
                foreach ($registros as $fila) {
                    $impto_tipo = $fila['tipo'];
                    $impto_IVA = $fila['iva'];
                    $impto_Recargo = $fila['recargo'];
                }
                
                switch ($impto_tipo) {
                    case 1:
                        // calcularlo como porcentaje
                        if ( !empty($impto_IVA) && trim(strtolower($cliente_RegimenIVA)) <> 'exento' ) {
                            $total = $total + (($importe * $impto_IVA) / 100);
                        }
                        
                        if ( !empty($impto_Recargo) && trim(strtolower($cliente_RegimenIVA)) === 'recargo' ) {
                            $total = $total + (($importe * $impto_Recargo) / 100);
                        }
                        
                        break;

                    default:
                        // calcularlo como suma
                        if ( !empty($impto_IVA) && trim(strtolower($cliente_RegimenIVA)) <> 'exento' ) {
                            $total = $total + $impto_IVA;
                        }
                        
                        if ( !empty($impto_Recargo) && trim(strtolower($cliente_RegimenIVA)) === 'recargo' ) {
                            $total = $total + $impto_Recargo;
                        }
                        
                        break;
                }
                
                // Cálculo de las retenciones (IRPF - profesionales)
                if ( !empty($cliente_CodRetencion) && !empty($cliente_PorcentajeRetencion) ) {
                    if ($cliente_PorcentajeRetencion <> 0) {
                        $total = $total - (($importe * $cliente_PorcentajeRetencion) / 100);
                    }
                }

            }
        }
        
    }
    
    private function checkFields()
    {
        $aDevolver = true;

        $this->crearFechaDesde();
        $this->crearFechaHasta();
        
        $this->crearHoraAnticipacion();
        $this->crearHoraDesde();
        $this->crearHoraHasta();

        if ($this->checkFechasPeriodo() == false){
            $aDevolver = false;
        }
        
        if ($this->checkHorasPeriodo() == false){
            $aDevolver = false;
        }

        if (empty($this->codcliente)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe de asignar el servicio a un cliente.');
        }

        if (empty($this->nombre)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar la descripción del servicio.');
        }

        if (empty($this->hoja_ruta_origen)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar el origen de la Hoja de Ruta.');
        }

        if (empty($this->hoja_ruta_destino)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar el destino de la Hoja de Ruta.');
        }

        if (empty($this->hoja_ruta_expediciones)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar las expediciones de la Hoja de Ruta.');
        }

        if (empty($this->hoja_ruta_contratante)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar el contratante de la Hoja de Ruta.');
        }

        if (empty($this->hoja_ruta_tipoidfiscal)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar Id. Fiscal de la Hoja de Ruta.');
        }

        if (empty($this->hoja_ruta_cifnif)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar el Num. Fiscal de la Hoja de Ruta.');
        }

        if (empty($this->idempresa)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar la empresa que realiza el servicio.');
        }

        if (empty($this->importe)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar el Importe x km nacional.');
        }

        if (empty($this->codimpuesto)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('No ha elegido el tipo de impuesto para "Importe x km nacional".');
        }

        if (empty($this->importe_enextranjero)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar el Importe x km en extranjero.');
        }
        
        if (empty($this->codimpuesto_enextranjero)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('No ha elegido el tipo de impuesto para "Importe x km en extrajero".');
        }
        
        if (empty($this->inicio_dia)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('No ha elegido la fecha de inicio del servicio.');
        }
        
        if (empty($this->fin_dia)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('No ha elegido la fecha de fin del servicio.');
        }
        
        if (empty($this->plazas) or $this->plazas <= 0) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe de completar las plazas.');
        }

        if (!$this->aceptado) {
            $this->toolBox()->i18nLog()->info('Si no acepta el servicio, no podrá montarse.');
        }

        $this->codsubcuenta_km_nacional = empty($this->codsubcuenta_km_nacional) ? null : $this->codsubcuenta_km_nacional;
        $this->codsubcuenta_km_extranjero = empty($this->codsubcuenta_km_extranjero) ? null : $this->codsubcuenta_km_extranjero;
        
        return $aDevolver;
    }
    
    private function actualizarServicioEnMontaje($idservice)
    {
        // Actualizamos el servicio discrecional en montaje de servicios
        $sql = ' UPDATE service_assemblies AS S1, services AS S2 '
             . ' SET S1.nombre = S2.nombre '
             .    ', S1.fechaalta = S2.fechaalta '
             .    ', S1.useralta = S2.useralta '
             .    ', S1.fechamodificacion = S2.fechamodificacion '
             .    ', S1.usermodificacion = S2.usermodificacion '
             .    ', S1.activo = S2.activo '
             .    ', S1.fechabaja = S2.fechabaja '
             .    ', S1.userbaja = S2.userbaja '
             .    ', S1.motivobaja = S2.motivobaja '
             .    ', S1.plazas = S2.plazas '
                
             .    ', S1.codcliente = S2.codcliente '
             .    ', S1.idvehicle_type = S2.idvehicle_type '
             .    ', S1.idhelper = S2.idhelper '
             .    ', S1.facturar_SN = S2.facturar_SN '
             .    ', S1.facturar_agrupando = 0 '
             .    ', S1.importe = S2.importe '
             .    ', S1.importe_enextranjero = S2.importe_enextranjero '
             .    ', S1.codimpuesto = S2.codimpuesto '
             .    ', S1.codimpuesto_enextranjero = S2.codimpuesto_enextranjero '
             .    ', S1.total = S2.total '
             .    ', S1.fuera_del_municipio = S2.fuera_del_municipio '
             .    ', S1.hoja_ruta_origen = S2.hoja_ruta_origen '
             .    ', S1.hoja_ruta_destino = S2.hoja_ruta_destino '
             .    ', S1.hoja_ruta_expediciones = S2.hoja_ruta_expediciones '
             .    ', S1.hoja_ruta_contratante = S2.hoja_ruta_contratante '
             .    ', S1.hoja_ruta_tipoidfiscal = S2.hoja_ruta_tipoidfiscal '
             .    ', S1.hoja_ruta_cifnif = S2.hoja_ruta_cifnif '
             .    ', S1.idservice_type = S2.idservice_type ' 
             .    ', S1.idempresa = S2.idempresa '
             .    ', S1.observaciones = S2.observaciones '
             .    ', S1.observaciones_montaje = S2.observaciones_montaje '
             .    ', S1.observaciones_vehiculo = S2.observaciones_vehiculo '
             .    ', S1.observaciones_facturacion = S2.observaciones_facturacion '
             .    ', S1.observaciones_liquidacion = S2.observaciones_liquidacion '
             .    ', S1.observaciones_drivers = S2.observaciones_drivers '
             .    ', S1.iddriver_1 = S2.iddriver_1 '
             .    ', S1.driver_alojamiento_1 = S2.driver_alojamiento_1 '
             .    ', S1.driver_observaciones_1 = S2.driver_observaciones_1 '
             .    ', S1.iddriver_2 = S2.iddriver_2 '
             .    ', S1.driver_alojamiento_2 = S2.driver_alojamiento_2 '
             .    ', S1.driver_observaciones_2 = S2.driver_observaciones_2 '
             .    ', S1.iddriver_3 = S2.iddriver_3 '
             .    ', S1.driver_alojamiento_3 = S2.driver_alojamiento_3 '
             .    ', S1.driver_observaciones_3 = S2.driver_observaciones_3 '
             .    ', S1.idvehicle = S2.idvehicle '
             .    ', S1.codsubcuenta_km_nacional = S2.codsubcuenta_km_nacional '
             .    ', S1.codsubcuenta_km_extranjero = S2.codsubcuenta_km_extranjero '
             .    ', S1.fecha_desde = S2.fecha_desde '
             .    ', S1.fecha_hasta = S2.fecha_hasta '
             .    ', S1.hora_anticipacion = S2.hora_anticipacion '
             .    ', S1.hora_desde = S2.hora_desde '
             .    ', S1.hora_hasta = S2.hora_hasta '
             .    ', S1.salida_desde_nave_sn = S2.salida_desde_nave_sn '
                
             . ' WHERE S1.idservice = ' . $idservice . ' '
             . ' AND S2.idservice = ' . $idservice . ';'
        ;
        
        self::$dataBase->exec($sql);
    }
    
}
