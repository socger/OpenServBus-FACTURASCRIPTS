<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;

class Service_regular_period extends Base\ModelClass {
    use Base\ModelTrait;

    public $idservice_regular_period;
        
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
    public $fecha_desde;
    public $fecha_hasta;
    
    public $hora_desde;
    public $hora_hasta;
    
    public $salida_desde_nave_sn;
    
    public $observaciones;
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->activo = true; // Por defecto estará activo
        $this->salida_desde_nave_sn = false;
    }
    
    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idservice_regular_period';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'service_regular_periods';
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
        if (empty($this->idservice_regular_period)) {
            $this->idservice_regular_period = $this->newCode();
        }

        $this->rellenarDatosAlta();
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }

        return parent::saveInsert($values);
    }
    
    public function test() {
        
        if ($this->checkFechasPeriodo() == false){
            return false;
        }
        
        if ($this->checkHorasPeriodo() == false){
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
	
    private function evitarInyeccionSQL()
    {
        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
    }
	
}
