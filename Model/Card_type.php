<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;

class Card_type extends Base\ModelClass {
    use Base\ModelTrait;

    public $idcard_type;
        
    public $user_fecha;
    public $user_nick;
    public $fechaalta;
    public $useralta;
    public $fechamodificacion;
    public $usermodificacion;
    public $activo;
    public $fechabaja;
    public $userbaja;

    public $nombre;
    public $de_pago;
    
    public $observaciones;
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->activo = true; // Por defecto estará activo
        $this->de_pago = false; // Por defecto el tipo de tarjeta no será de pago
    }
    
    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idcard_type';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'card_types';
    }

    // Para realizar cambios en los datos antes de guardar por modificación
    protected function saveUpdate(array $values = [])
    {
        // Siendo un alta o una modificación, siempre guardamos los datos de modificación
        $this->usermodificacion = $this->user_nick; 
        $this->fechamodificacion = $this->user_fecha; 
        
        $this->comprobarSiActivo();
        
        $a_Devolver = parent::saveInsert($values);
        
        $this->actualizarEnTarjetas_DePago();
        
        return $a_Devolver;
    }

    // Para realizar cambios en los datos antes de guardar por alta
    protected function saveInsert(array $values = [])
    {
        // Creamos el nuevo id
        if (empty($this->idcard_type)) {
            $this->idcard_type = $this->newCode();
        }

        // Rellenamos los datos de alta
        $this->useralta = $this->user_nick; 
        $this->fechaalta = $this->user_fecha; 
        
        // Siendo un alta o una modificación, siempre guardamos los datos de modificación
        $this->usermodificacion = $this->user_nick; 
        $this->fechamodificacion = $this->user_fecha; 
        
        $this->comprobarSiActivo();
        
        $a_Devolver = parent::saveInsert($values);
        
        $this->actualizarEnTarjetas_DePago();
        
        return $a_Devolver;
    }
    
    public function test() {
        // Para evitar la inyección de sql
        $utils = $this->toolBox()->utils();
        $this->nombre = $utils->noHtml($this->nombre);
        $this->observaciones = $utils->noHtml($this->observaciones);

        return parent::test();
    }


    // ** ********************************** ** //
    // ** FUNCIONES CREADAS PARA ESTE MODELO ** //
    // ** ********************************** ** //
    protected function comprobarSiActivo()
    {
        if ($this->activo == false) {
            $this->fechabaja = $this->fechamodificacion;
            $this->userbaja = $this->usermodificacion;
        } else { // Por si se vuelve a poner Activo = true
            $this->fechabaja = null;
            $this->userbaja = null;
        }
    }
    
    private function actualizarEnTarjetas_DePago()
    {
        // Rellenamos el nombre del empleado en otras tablas
        $sql = "UPDATE tarjetas SET tarjetas.de_pago = " . $this->de_pago . "' WHERE tarjetas.idcard_type = " . $this->idcard_type . ";";
        self::$dataBase->exec($sql);
    }
      
}
