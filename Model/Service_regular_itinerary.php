<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;

class Service_regular_itinerary extends Base\ModelClass {
    use Base\ModelTrait;

    public $idservice_regular_itinerary;
        
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
    
    public $idservice_regular;

    public $orden;
    public $idstop;
    public $hora;
    public $kms;
    public $kms_vacios;
    public $pasajeros_entradas;
    public $pasajeros_salidas;
    
    public $observaciones;
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->activo = true; // Por defecto estará activo
        $this->kms = 0;
        $this->kms_vacios = false;
        $this->pasajeros_entradas = 0;
        $this->pasajeros_salidas = 0;
    }
    
    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idservice_regular_itinerary';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'service_regular_itineraries';
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
        if (empty($this->idservice_regular_itinerary)) {
            $this->idservice_regular_itinerary = $this->newCode();
        }

        $this->rellenarDatosAlta();
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }

        return parent::saveInsert($values);
    }
    
    public function test() {
        
        if ($this->checkParada() == false){return false;}
        if ($this->checkOrden() == false){return false;}
        if ($this->checkHora() == false){return false;}
        if ($this->checkPasajeros() == false){return false;}
        
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

    private function checkOrden()
    {
        $a_devolver = true;
        if (empty($this->orden)) 
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('Falta el orden de la línea. Es un valor numérico para ordenar los itinerarios.');
        }
        return $a_devolver;
    }
    
    private function checkHora()
    {
        $a_devolver = true;
        if (empty($this->hora)) 
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('Falta el hora en la que debe de estar en la parada.');
        }
        return $a_devolver;
    }
    private function checkParada()
    {
        $a_devolver = true;
        if ( empty($this->idstop) )
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('Debe de elegir una parada.');
        }
        return $a_devolver;
    }
    
    private function checkPasajeros()
    {
        $a_devolver = true;
        if ( empty($this->pasajeros_entradas) and
             empty($this->pasajeros_salidas) ) 
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('Debe de asignar la cantidad de pasajeros a recoger/dejar.');
        }
        return $a_devolver;
    }
	
    private function evitarInyeccionSQL()
    {
        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
    }
	
}
