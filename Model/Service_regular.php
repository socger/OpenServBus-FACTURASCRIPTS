<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Plugins\OpenServBus\Model\Vehicle_type;
use FacturaScripts\Plugins\OpenServBus\Model\Helper;
use FacturaScripts\Plugins\OpenServBus\Model\Service_type;
use FacturaScripts\Plugins\OpenServBus\Model\Driver;
use FacturaScripts\Plugins\OpenServBus\Model\Vehicle;
use FacturaScripts\Plugins\OpenServBus\Model\Service_valuation_type;

class Service_regular extends Base\ModelClass {
    use Base\ModelTrait;

    public $idservice_regular;
        
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
    
    public $cod_servicio;
    public $codcliente;
    public $idvehicle_type;
    public $idhelper;

    public $lunes;
    public $martes;
    public $miercoles;
    public $jueves;
    public $viernes;
    public $sabado;
    public $domingo;
    
    public $facturar_SN;
    public $facturar_agrupando;
    
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
    
    public $idservice_regular_period;
    public $fecha_desde;
    public $fecha_hasta;

    public $hora_anticipacion;
    public $hora_desde;
    public $hora_hasta;
    
    public $inicio_horaAnt;

    public $salida_desde_nave_sn;
    public $salida_desde_nave_text;
    
    public $observaciones_periodo;
    public $combinadoSN;
    public $combinadoSiNo;
    
    public $llamadoDesdeFuera; // Para comprobar si se usa el metodo save desde otro sitio que no sea el controlador EditService.php
    
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->activo = true; // Por defecto estará activo

        $this->lunes = false;
        $this->martes = false;
        $this->miercoles = false;
        $this->jueves = false;
        $this->viernes = false;
        $this->sabado = false;
        $this->domingo = false;
    
        $this->facturar_SN = true;
        $this->facturar_agrupando = true;

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
        return 'idservice_regular';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'service_regulars';
    }

    // Para realizar cambios en los datos antes de guardar por modificación
    protected function saveUpdate(array $values = [])
    {
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }

        $this->completarCombinadoSN();
        $respuesta = parent::saveUpdate($values);
        return $respuesta;
    }

    // Para realizar cambios en los datos antes de guardar por alta
    protected function saveInsert(array $values = [])
    {
        // Creamos el nuevo id
        if (empty($this->idservice_regular)) {
            $this->idservice_regular = $this->newCode();
        }

        // Rellenamos cod_servicio si no lo introdujo el usuario
        if (empty($this->cod_servicio)) {
            $this->cod_servicio = (string) $this->newCode();
        }

        $this->rellenarDatosAlta();
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }

        $this->completarCombinadoSN();
        $respuesta = parent::saveInsert($values);
        return $respuesta;
    }
    
    public function test() {
        if (true === $this->llamadoDesdeFuera) {
             // Está siendo usado el metodo save desde otro sitio que no es el controlador EditService.php
            return parent::test();
        }
        
        // Comprobamos que el código se ha introducido correctamente
        if (!empty($this->cod_servicio) && 1 !== \preg_match('/^[A-Z0-9_\+\.\-]{1,10}$/i', $this->cod_servicio)) {
            $this->toolBox()->i18nLog()->error(
                'invalid-alphanumeric-code',
                ['%value%' => $this->cod_servicio, '%column%' => 'cod_servicio', '%min%' => '1', '%max%' => '10']
            );
            return false;
        }
        
        
        if (empty($this->codcliente)) {
            $this->toolBox()->i18nLog()->error('Debe de asignar el servicio a un cliente.');
            return false;
        }

        if ($this->comprobarDiasServicio() == false){
            return false;
        }
        
        if ($this->comprobarFacturacion() == false){
            return false;
        }
        
        $this->completarDatosUltimoPeriodo();
        
        $this->rellenarConductorVehiculoSiVacios();
        
        $this->codsubcuenta_km_nacional = empty($this->codsubcuenta_km_nacional) ? null : $this->codsubcuenta_km_nacional;
        $this->codsubcuenta_km_extranjero = empty($this->codsubcuenta_km_extranjero) ? null : $this->codsubcuenta_km_extranjero;
        
        if ($this->hayCombinacionesDondeEsteElServicioQueNoCoincidenLosDiasDeSemana() == true) {
            return false;
        }
        
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

    private function comprobarFacturacion()
    {
        $a_devolver = true;
        if ( $this->facturar_SN === false and 
             $this->facturar_agrupando === true  ) 
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('Si elige FACTURAR = NO, no puede elegir AGRUPANDO = SI.');
        }
        return $a_devolver;
    }
    
    private function comprobarDiasServicio()
    {
        $a_devolver = true;
        if ( $this->lunes == false and 
             $this->martes == false and 
             $this->miercoles == false and 
             $this->jueves == false and 
             $this->viernes == false and 
             $this->sabado == false and 
             $this->domingo == false ) 
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('Ya que es un servicio regular/fijo, debe de elegirme que días de la semana se va a realizar.');
        }
        return $a_devolver;
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
    
    private function completarDatosUltimoPeriodo()
    {
        if (empty($this->idservice_regular)) {
            return;
        }
        
        $sql = ' SELECT idservice_regular_period '
             .      ' , fecha_desde '
             .      ' , fecha_hasta '
             .      ' , hora_anticipacion '
             .      ' , hora_desde '
             .      ' , hora_hasta '
             .      ' , salida_desde_nave_sn '
             .      ' , observaciones '
             . ' FROM service_regular_periods '
             . ' WHERE idservice_regular = ' . $this->idservice_regular . ' '
             .   ' AND activo = 1 '
             . ' ORDER BY fecha_desde DESC '
             .        ' , fecha_hasta DESC '
             .        ' , hora_desde DESC '
             .        ' , hora_hasta DESC '
             .        ' , idservice_regular '
             . ' LIMIT 1 '
             ;

        $this->idservice_regular_period = null;
        $this->fecha_desde = null;
        $this->fecha_hasta = null;
        $this->hora_anticipacion = null;
        $this->hora_desde = null;
        $this->hora_hasta = null;
        $this->salida_desde_nave_sn = null;
        $this->observaciones_periodo = null;
        
        $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

        foreach ($registros as $fila) {
            $this->idservice_regular_period = $fila['idservice_regular_period'];
            $this->fecha_desde = $fila['fecha_desde'];
            $this->fecha_hasta = $fila['fecha_hasta'];
            $this->hora_anticipacion = $fila['hora_anticipacion'];
            $this->hora_desde = $fila['hora_desde'];
            $this->hora_hasta = $fila['hora_hasta'];
            $this->salida_desde_nave_sn = $fila['salida_desde_nave_sn'];
            $this->observaciones_periodo = $fila['observaciones'];
        }
    }

    private function completarCombinadoSN()
    {
        if (empty($this->idservice_regular)) {
            return;
        }
        
        $sql = ' SELECT COUNT(*) AS cantidad '
             . ' FROM service_regular_combination_servs '
             . ' WHERE service_regular_combination_servs.idservice_regular = ' . $this->idservice_regular . ' '
             . ' AND service_regular_combination_servs.activo = 1 '
             . ' ORDER BY service_regular_combination_servs.idservice_regular '
             ;

        $this->combinadoSN = false;
        
        $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

        foreach ($registros as $fila) {
            if ($fila['cantidad'] > 0){
                $this->combinadoSN = true;
            }
        }
    }
    
    private function rellenarConductorVehiculoSiVacios()
    {
        if (empty($this->iddriver_1) || empty($this->idvehicle)) {
            $this->toolBox()->i18nLog()->info( 'Si no rellena el vehículo o el conductor, este será el orden de prioridades para el Montaje de Servicios:'
                                             . ' 1º Combinación - Servicio Regular, 2º Combinación y 3º Servicio Regular' );
        }
    }
    
    private function hayCombinacionesDondeEsteElServicioQueNoCoincidenLosDiasDeSemana() : bool
    {
        if (empty($this->idservice_regular)) {
            return false;
        }
        
        $combinacionesConDiasDiferentes = [];
        
        $sql = ' SELECT service_regular_combinations.lunes '
             .      ' , service_regular_combinations.martes '
             .      ' , service_regular_combinations.miercoles '
             .      ' , service_regular_combinations.jueves '
             .      ' , service_regular_combinations.viernes '
             .      ' , service_regular_combinations.sabado '
             .      ' , service_regular_combinations.domingo '
             .      ' , service_regular_combinations.idservice_regular_combination '
             .      ' , service_regular_combinations.nombre '
             . ' FROM service_regular_combination_servs '
             . ' LEFT JOIN service_regular_combinations on (service_regular_combinations.idservice_regular_combination = service_regular_combination_servs.idservice_regular_combination) '
             . ' WHERE service_regular_combination_servs.idservice_regular = ' . $this->idservice_regular
             ;

        $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

        foreach ($registros as $fila) {
            $coincideAlgunDia = false;
            
            // Una combinación puede tener varios servicios regulares, por lo 
            // que tengo que comprobar todos sus servicios
            if ($this->lunes == 1) {
                if ($this->lunes == $fila['lunes']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->martes == 1) {
                if ($this->martes == $fila['martes']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->miercoles == 1) {
                if ($this->miercoles == $fila['miercoles']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->jueves == 1) {
                if ($this->jueves == $fila['jueves']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->viernes == 1) {
                if ($this->viernes == $fila['viernes']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->sabado == 1) {
                if ($this->sabado == $fila['sabado']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->domingo == 1) {
                if ($this->domingo == $fila['domingo']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($coincideAlgunDia === false) {
                $combinacionesConDiasDiferentes[] = $fila['nombre'];
            }
        }
        
        if (empty($combinacionesConDiasDiferentes)) {
            return false;
        } else {
            foreach ($combinacionesConDiasDiferentes as $combinacion) {
                $this->toolBox()->i18nLog()->error( "Los días de la semana de la combinación $combinacion no coinciden con los días de la semana de este servicio regular." );
            }
            
            return true;
        }
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
    
}
