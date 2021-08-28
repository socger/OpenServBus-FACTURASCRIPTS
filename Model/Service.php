<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;

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
    
    public $codcliente;
    public $idvehicle_type;
    public $idhelper;

    public $facturar_SN;
    
    public $importe;
    public $codimpuesto;
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

    public $iddriver;
    public $idvehicle;
    
    public $codsubcuenta_km_nacional;
    public $codsubcuenta_km_extranjero;
    
    public $fecha_desde;
    public $fecha_hasta;
    public $hora_desde;
    public $hora_hasta;
    public $salida_desde_nave_sn;
    public $anticipacion_horas;
    public $anticipacion_minutos;
    public $observaciones_periodo;
    public $combinadoSN;
    public $combinadoSiNo;
    
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->activo = true; // Por defecto estará activo

        $this->facturar_SN = true;
        $this->importe = 0;
        $this->total = 0;
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
        $this->codsubcuenta_km_nacional = empty($this->codsubcuenta_km_nacional) ? null : $this->codsubcuenta_km_nacional;
        $this->codsubcuenta_km_extranjero = empty($this->codsubcuenta_km_extranjero) ? null : $this->codsubcuenta_km_extranjero;
        
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
        
        $impto_tipo = 0.0;
        $impto_IVA = 0.0;
        $impto_Recargo = 0.0;

        $this->total = $this->importe;
        
        if ($this->importe <> 0) {
            if (!empty($this->codimpuesto)) { 
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

                // Cargar datos del impuesto que nos interesan
                $sql = ' SELECT impuestos.tipo '
                     .      ' , impuestos.iva '
                     .      ' , impuestos.recargo '
                     . ' FROM impuestos '
                     . ' WHERE impuestos.codimpuesto = "' . $this->codimpuesto . '" '
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
                            $this->total = $this->total + (($this->importe * $impto_IVA) / 100);
                        }
                        
                        if ( !empty($impto_Recargo) && trim(strtolower($cliente_RegimenIVA)) === 'recargo' ) {
                            $this->total = $this->total + (($this->importe * $impto_Recargo) / 100);
                        }
                        
                        break;

                    default:
                        // calcularlo como suma
                        if ( !empty($impto_IVA) && trim(strtolower($cliente_RegimenIVA)) <> 'exento' ) {
                            $this->total = $this->total + $impto_IVA;
                        }
                        
                        if ( !empty($impto_Recargo) && trim(strtolower($cliente_RegimenIVA)) === 'recargo' ) {
                            $this->total = $this->total + $impto_Recargo;
                        }
                        
                        break;
                }
                
                // Cálculo de las retenciones (IRPF - profesionales)
                if ( !empty($cliente_CodRetencion) && !empty($cliente_PorcentajeRetencion) ) {
                    if ($cliente_PorcentajeRetencion <> 0) {
                        $this->total = $this->total - (($this->importe * $cliente_PorcentajeRetencion) / 100);
                    }
                }

                $this->total = \round($this->total, (int) \FS_NF0);
            }
        }
    }
    
}
