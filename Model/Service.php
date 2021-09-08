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

    public $iddriver;
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
    public $observaciones_periodo;
    
    public $combinadoSN;
    public $combinadoSiNo;
    
    
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

        $respuesta = parent::saveUpdate($values);
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
        $this->crearFechaDesde();
        $this->crearFechaHasta();
        
        $this->crearHoraAnticipacion();
        $this->crearHoraDesde();
        $this->crearHoraHasta();

        if ($this->checkFechasPeriodo() == false){
            return false;
        }
        
        if ($this->checkHorasPeriodo() == false){
            return false;
        }

        $this->codsubcuenta_km_nacional = empty($this->codsubcuenta_km_nacional) ? null : $this->codsubcuenta_km_nacional;
        $this->codsubcuenta_km_extranjero = empty($this->codsubcuenta_km_extranjero) ? null : $this->codsubcuenta_km_extranjero;
        
        if ($this->comprobarImpuestos() == false) {
            return false;
        }
        
        $this->rellenarTotal();
        
        if (empty($this->plazas) or $this->plazas <= 0) {
            $this->toolBox()->i18nLog()->error('Debe de completar las plazas.');
            return false;
        }

        if (!$this->aceptado) {
            $this->toolBox()->i18nLog()->info('Si no acepta el servicio, no podrá montarse.');
        }
        
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
        
        $this->hoja_ruta_origen = $utils->noHtml($this->hoja_ruta_origen);
        $this->hoja_ruta_destino = $utils->noHtml($this->hoja_ruta_destino);
        $this->hoja_ruta_expediciones = $utils->noHtml($this->hoja_ruta_expediciones);
        $this->hoja_ruta_contratante = $utils->noHtml($this->hoja_ruta_contratante);
        $this->hoja_ruta_tipoidfiscal = $utils->noHtml($this->hoja_ruta_tipoidfiscal);
        $this->hoja_ruta_cifnif = $utils->noHtml($this->hoja_ruta_cifnif);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        $this->codsubcuenta_km_nacional = $utils->noHtml($this->codsubcuenta_km_nacional);
        $this->codsubcuenta_km_extranjero = $utils->noHtml($this->codsubcuenta_km_extranjero);
    }
    
    private function rellenarTotal()
    {
        $cliente_RegimenIVA = '';
        $cliente_CodRetencion = '';
        $cliente_PorcentajeRetencion = 0.0;
        
        $this->total = $this->importe + $this->importe_enextranjero;
        
        // Traemos los datos del cliente sólo si hay algún importe y si hay algún tipo de impuesto
        if ($this->importe <> 0 or $this->importe_enextranjero <> 0) {
            if (!empty($this->codimpuesto) or !empty($this->codimpuesto)) { 
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
    
    private function comprobarImpuestos()
    {
        $aDevolver = true;

        if (empty($this->codimpuesto)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('No ha elegido el tipo de impuesto para "Importe x km nacional".');
        }

        if (empty($this->codimpuesto_enextranjero)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('No ha elegido el tipo de impuesto para "Importe x km en extrajero".');
        }
        
        return $aDevolver;
    }
    
}
