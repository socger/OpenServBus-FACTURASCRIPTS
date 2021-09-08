<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Plugins\OpenServBus\Model\Driver;
use FacturaScripts\Plugins\OpenServBus\Model\Employee;

class Tarjeta extends Base\ModelClass {
    use Base\ModelTrait;

    public $idtarjeta;
        
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
    public $idtarjeta_type;
    public $de_pago;
    public $es_DePago;
    public $idemployee;
    public $iddriver;
    public $idempresa;
    
    public $observaciones;
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->activo = true; // Por defecto estará activo
        $this->de_pago = false; // Por defecto el tipo de tarjeta no será de pago
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
        new Driver();
        new Employee();

        return parent::install();
    }

    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idtarjeta';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'tarjetas';
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
        if (empty($this->idtarjeta)) {
            $this->idtarjeta = $this->newCode();
        }

        $this->rellenarDatosAlta();
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }
        
        return parent::saveInsert($values);
    }
    
    public function test() {
        
        $this->actualizar_dePago();
        
        if ($this->comprobar_Empleado_Conductor() == false) {
            return false;
        }
        
        $this->comprobarEmpresa();

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
    
    private function actualizar_dePago()
    {
        // Rellenamos el campo de_pago de este modelo pues está ligado con campo de_pago de tabla tarjeta_types
        // pero siempre lo actualizamos porque pueden cambiar el valor de de_pago en tabla tarjeta_types
        if (!empty($this->idtarjeta_type)) {
            $sql = ' SELECT tarjeta_types.de_pago '
                 . ' FROM tarjeta_types '
                 . ' WHERE tarjeta_types.idtarjeta_type = ' . $this->idtarjeta_type
                 ;
            $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

            foreach ($registros as $fila) {
                $this->de_pago = $fila['de_pago'];
            }
        }
    }
      
    private function comprobarEmpresa()
    {
        if (!empty($this->idemployee)){
            $sql = ' SELECT employees.idempresa '
                 .      ' , empresas.nombrecorto '
                 . ' FROM employees '
                 . ' LEFT JOIN empresas ON (empresas.idempresa = employees.idempresa) '
                 . ' WHERE employees.idemployee = ' . $this->idemployee
                 ;
        } else {
            $sql = ' SELECT employees.idempresa '
                 .      ' , empresas.nombrecorto '
                 . ' FROM drivers '
                 . ' LEFT JOIN employees ON (employees.idemployee = drivers.idemployee) '
                 . ' LEFT JOIN empresas ON (empresas.idempresa = employees.idempresa) '
                 . ' WHERE drivers.iddriver = ' . $this->iddriver
                 ;
        }
        // Rellenamos el campo de_pago de este modelo pues está ligado con campo de_pago de tabla tarjeta_types
        // pero siempre lo actualizamos porque pueden cambiar el valor de de_pago en tabla tarjeta_types
        $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

        foreach ($registros as $fila) {
            $idempresa = $fila['idempresa'];
            $nombreEmpresa = $fila['nombrecorto'];
        }
        
        //$this->toolBox()->i18nLog()->info($idempresa . ' ... ' . $this->idempresa );
        if (!empty($this->idempresa)){
            if (!empty($idempresa)){
                if ($idempresa <> $this->idempresa){
                    $this->toolBox()->i18nLog()->info('Pero para su información ... la empresa del conductor/empleado ("' . $nombreEmpresa . '") no es la misma que la empresa elegida para esta tarjeta.');
                }
            }
        }
        
    }

    private function comprobar_Empleado_Conductor()
    {
        // Exijimos que se introduzca idempresa o idcollaborator
        if ( (empty($this->idemployee)) 
         and (empty($this->iddriver))
           ) 
        {
            $this->toolBox()->i18nLog()->error('Debe de confirmar si la tarjeta es de un empleado o de un conductor.');
            return false;
        }

        if ( (!empty($this->idemployee)) 
         and (!empty($this->iddriver))
           ) 
        {
            $this->toolBox()->i18nLog()->error('La tarjeta o es de un empleado o es de un conductor, pero no de ambos.');
            return false;
        }
        
        return true;
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
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->nombre = $utils->noHtml($this->nombre);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
    }
	
}
