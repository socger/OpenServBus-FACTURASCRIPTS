<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;

class Vehicle extends Base\ModelClass {
    use Base\ModelTrait;

    public $idvehicle;
        
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

    public $cod_vehicle;
    public $nombre;
    public $matricula;
    public $motor_chasis;
    public $numero_bastidor;
    public $carroceria;
    public $numero_obra;
    public $fecha_matriculacion_primera;
    public $fecha_matriculacion_actual;
    public $plazas_segun_permiso;
    public $plazas_segun_ficha_tecnica;
    public $plazas_ofertables;
    public $configuraciones_especiales;
    public $idempresa;
    public $idcollaborator;
    public $idgarage;
    public $observaciones;
    public $idfuel_type;
    public $km_actuales;
    public $fecha_km_actuales;
    public $idvehicle_type;
    public $iddriver_usual;
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->activo = true; // Por defecto estará activo
        $this->km_actuales = 0;
    }
    
    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idvehicle';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'vehicles';
    }

    // Para realizar cambios en los datos antes de guardar por modificación
    protected function saveUpdate(array $values = [])
    {
        // Siendo un alta o una modificación, siempre guardamos los datos de modificación
        $this->usermodificacion = $this->user_nick; 
        $this->fechamodificacion = $this->user_fecha; 
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }
        
        return parent::saveUpdate($values);
    }

    // Para realizar cambios en los datos antes de guardar por alta
    protected function saveInsert(array $values = [])
    {
        // Creamos el nuevo id
        if (empty($this->idvehicle)) {
            $this->idvehicle = $this->newCode();
        }

        // Rellenamos el cod_vehicle si no lo introdujo el usuario
        if (empty($this->cod_vehicle)) {
            $this->cod_vehicle = (string) $this->newCode();
        }

        // Rellenamos los datos de alta
        $this->useralta = $this->user_nick; 
        $this->fechaalta = $this->user_fecha; 
        
        // Siendo un alta o una modificación, siempre guardamos los datos de modificación
        $this->usermodificacion = $this->user_nick; 
        $this->fechamodificacion = $this->user_fecha; 
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }
        
        return parent::saveInsert($values);
    }
    
    public function test()
    {
        /* Lo quitamos de momento para probar lo último comentado por neorazorx
           Mirar como rellenamos en editVehicle.php el controlador Colaborador
         
        // Desde que rellenamos los valores del widget Colaborador, he tenido que poner un valor 0
        // Por lo tanto no me viene como empty, así que lo pongo a pelo yo si es = 0
        if ( $this->idcollaborator == 0) 
        {
            $this->toolBox()->i18nLog()->info('Ponemos a null al colaborador');
            $this->idcollaborator = null;
        }
        */
      
        // Comprobamos que el código de empleado si se ha introducido correctamente
        if (!empty($this->cod_vehicle) && 1 !== \preg_match('/^[A-Z0-9_\+\.\-]{1,10}$/i', $this->cod_vehicle)) {
            $this->toolBox()->i18nLog()->error(
                'invalid-alphanumeric-code',
                ['%value%' => $this->cod_vehicle, '%column%' => 'cod_vehicle', '%min%' => '1', '%max%' => '10']
            );
            
            return false;
        }
        
        // Exijimos que se introduzca idempresa o idcollaborator
        if ( (empty($this->idempresa)) 
         and (empty($this->idcollaborator))
           ) 
        {
            $this->toolBox()->i18nLog()->error('Debe de confirmar si es un vehículo nuestro o de una empresa colaboradora');
            return false;
        }

        if ( (!empty($this->idempresa)) 
         and (!empty($this->idcollaborator))
           ) 
        {
            $this->toolBox()->i18nLog()->error('O es un vehículo nuestro o de una empresa colaboradora, pero ambos no');
            return false;
        }
        
        /* Quitamos esta parte porque si el usuario rellenaba idControllator y idempresa estaba vacío, lo rellenaba automáticamente con la empresa por defecto
            // Nos rellena la empresa (si no se ha elegido) con la empresa por defecto
            if (empty($this->idempresa)) {
                $this->idempresa = $this->toolBox()->appSettings()->get('default', 'idempresa');
            }
        */
        
        // Si Fecha Matriculación Actual está vacía, pero Fecha Matriculación Primera está rellena, pues
        // Fecha Matriculacion Actual = Fecha Matriculación Primera
        if (empty($this->fecha_matriculacion_actual)) {
            if (!empty($this->fecha_matriculacion_primera)) {
                $this->toolBox()->i18nLog()->info('La Fecha Matriculación Actual se ha rellenado con el valor de la Fecha de Matriculación Actual, por estar vacía');
                $this->fecha_matriculacion_actual = $this->fecha_matriculacion_primera;
            }
        }
        

        $utils = $this->toolBox()->utils();
        
        $this->cod_vehicle = $utils->noHtml($this->cod_vehicle);
        $this->nombre = $utils->noHtml($this->nombre);
        $this->matricula = $utils->noHtml($this->matricula);
        $this->motor_chasis = $utils->noHtml($this->motor_chasis);
        $this->numero_bastidor = $utils->noHtml($this->numero_bastidor);
        $this->carroceria = $utils->noHtml($this->carroceria);
        $this->numero_obra = $utils->noHtml($this->numero_obra);
        $this->plazas_segun_ficha_tecnica = $utils->noHtml($this->plazas_segun_ficha_tecnica);
        $this->configuraciones_especiales = $utils->noHtml($this->configuraciones_especiales);
        $this->observaciones = $utils->noHtml($this->observaciones);

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
    
}
