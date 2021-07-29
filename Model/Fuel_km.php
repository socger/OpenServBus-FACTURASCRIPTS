<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;

class Fuel_km extends Base\ModelClass {
    use Base\ModelTrait;

    public $idfuel_km;
        
    public $user_fecha;
    public $user_nick;
    public $fechaalta;
    public $useralta;
    public $fechamodificacion;
    public $usermodificacion;
    public $activo;
    public $fechabaja;
    public $userbaja;

    public $idvehicle;
    public $iddriver;
    public $idemployee;
    public $idfuel_type;
    public $fecha;
    public $km;
    public $litros;
    public $deposito_lleno;
    public $pvp_litro;
    public $idfuel_pump;
    public $codproveedor;
    public $idtarjeta;
    public $nombre;
    
    public $observaciones;
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->activo = true; // Por defecto estará activo
    }
    
    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idfuel_km';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'fuel_kms';
    }

    // Para realizar cambios en los datos antes de guardar por modificación
    protected function saveUpdate(array $values = [])
    {
        // Siendo un alta o una modificación, siempre guardamos los datos de modificación
        $this->usermodificacion = $this->user_nick; 
        $this->fechamodificacion = $this->user_fecha; 
        
        $this->comprobarSiActivo();
        
        return parent::saveUpdate($values);
    }

    // Para realizar cambios en los datos antes de guardar por alta
    protected function saveInsert(array $values = [])
    {
        // Creamos el nuevo id
        if (empty($this->idfuel_km)) {
            $this->idfuel_km = $this->newCode();
        }

        // Rellenamos los datos de alta
        $this->useralta = $this->user_nick; 
        $this->fechaalta = $this->user_fecha; 
        
        // Siendo un alta o una modificación, siempre guardamos los datos de modificación
        $this->usermodificacion = $this->user_nick; 
        $this->fechamodificacion = $this->user_fecha; 
        
        $this->comprobarSiActivo();
        
        return parent::saveInsert($values);
    }
    
    public function test() {
        // Para evitar la inyección de sql
        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->nombre = $utils->noHtml($this->nombre);
        
        if ($this->comprobar_Surtidor_Proveedor() == false) {
            return false;
        }
        
        if ($this->comprobar_Empleado_Conductor() == false) {
            return false;
        }
        
        $this->comprobarEmpresa();
        
        return parent::test();
    }


    // ** ********************************** ** //
    // ** FUNCIONES CREADAS PARA ESTE MODELO ** //
    // ** ********************************** ** //
    private function comprobarSiActivo()
    {
        if ($this->activo == false) {
            $this->fechabaja = $this->fechamodificacion;
            $this->userbaja = $this->usermodificacion;
        } else { // Por si se vuelve a poner Activo = true
            $this->fechabaja = null;
            $this->userbaja = null;
        }
    }

    private function comprobar_Surtidor_Proveedor()
    {
        // Exijimos que se introduzca idempresa o idcollaborator
        if ( (empty($this->idfuel_pump)) 
         and (empty($this->codproveedor))
           ) 
        {
            $this->toolBox()->i18nLog()->error('Debe de confirmar si es un repostaje interno o externo.');
            return false;
        }

        if ( (!empty($this->idfuel_pump)) 
         and (!empty($this->codproveedor))
           ) 
        {
            $this->toolBox()->i18nLog()->error('El repostaje o es interno o externo, pero no de ambos.');
            return false;
        }
        
        return true;
    }        

    private function comprobar_Empleado_Conductor()
    {
        // Exijimos que se introduzca idempresa o idcollaborator
        if ( (empty($this->iddriver)) 
         and (empty($this->idemployee))
           ) 
        {
            $this->toolBox()->i18nLog()->error('Debe de confirmar si el repostaje lo ha hecho un empleado o un conductor.');
            return false;
        }

        if ( (!empty($this->iddriver)) 
         and (!empty($this->idemployee))
           ) 
        {
            $this->toolBox()->i18nLog()->error('El repostaje o lo ha hecho un empleado o lo ha hecho un conductor, pero no de ambos.');
            return false;
        }
        
        return true;
    }        

    private function comprobarEmpresa()
    {
        // Comprobamos la empresa del empleado o del conductor
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
        
        // Ahora comprobamos la empresa del vehículo
        if (!empty($this->idvehicle)){
            $sql = ' SELECT vehicles.idempresa '
                 .      ' , empresas.nombrecorto '
                 . ' FROM vehicles '
                 . ' LEFT JOIN empresas ON (empresas.idempresa = vehicles.idempresa) '
                 . ' WHERE vehicles.idvehicle = ' . $this->idvehicle
                 ;

            $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

            foreach ($registros as $fila) {
                $idempresa = $fila['idempresa'];
                $nombreEmpresa = $fila['nombrecorto'];
            }

            //$this->toolBox()->i18nLog()->info($idempresa . ' ... ' . $this->idempresa );
            if (!empty($this->idempresa)){
                if (!empty($idempresa)){
                    if ($idempresa <> $this->idempresa){
                        $this->toolBox()->i18nLog()->info('Pero para su información ... la empresa del vehículo ("' . $nombreEmpresa . '") no es la misma que la empresa elegida para esta tarjeta.');
                    }
                }

            }
        }
        
    }


}
