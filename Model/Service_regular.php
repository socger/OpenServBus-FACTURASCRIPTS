<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;

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

        return parent::saveUpdate($values);
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

        return parent::saveInsert($values);
    }
    
    public function test() {
        // Comprobamos que el código se ha introducido correctamente
        if (!empty($this->cod_servicio) && 1 !== \preg_match('/^[A-Z0-9_\+\.\-]{1,10}$/i', $this->cod_servicio)) {
            $this->toolBox()->i18nLog()->error(
                'invalid-alphanumeric-code',
                ['%value%' => $this->cod_servicio, '%column%' => 'cod_servicio', '%min%' => '1', '%max%' => '10']
            );
            return false;
        }
        
        if ($this->comprobarDiasServicio() == false){
            return false;
        }
        
        if ($this->comprobarFacturacion() == false){
            return false;
        }
        
		evitarInyeccionSQL();
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
        if ( $this->facturar_SN == false and 
             $this->facturar_agrupando = true  ) 
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
        $this->hoja_ruta_origen = $utils->noHtml($this->hoja_ruta_origen);
        $this->hoja_ruta_destino = $utils->noHtml($this->hoja_ruta_destino);
        $this->hoja_ruta_expediciones = $utils->noHtml($this->hoja_ruta_expediciones);
        $this->hoja_ruta_contratante = $utils->noHtml($this->hoja_ruta_contratante);
        $this->hoja_ruta_tipoidfiscal = $utils->noHtml($this->hoja_ruta_tipoidfiscal);
        $this->hoja_ruta_cifnif = $utils->noHtml($this->hoja_ruta_cifnif);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
    }
	
}
