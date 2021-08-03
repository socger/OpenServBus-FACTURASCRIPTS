<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;

class Advertisment_user extends Base\ModelClass {
    use Base\ModelTrait;

    public $idadvertisment_user;
        
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

    public $inicio;
    public $inicio_dia;
    public $inicio_hora;

    public $fin;
    public $fin_dia;
    public $fin_hora;

    public $nick;
    public $codrole;
    public $observaciones;
    
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->activo = true; // Por defecto estará activo
    }
    
    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idadvertisment_user';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'advertisment_users';
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
        if (empty($this->idadvertisment_user)) {
            $this->idadvertisment_user = $this->newCode();
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
        // Exijimos que se introduzca idempresa o idcollaborator
        if ( (!empty($this->nick)) 
         and (!empty($this->codrole))
           ) 
        {
            $this->toolBox()->i18nLog()->error('Puede rellenar el usuario o el grupo de usuarios. También puede dejar el usuario y el grupo de usuarios vacío (el aviso sería para cualquier usuario. Pero no puede rellenar ambos.');
            return false;
        }

        // $this->inicio = $this->inicio_dia . ' ' . $this->inicio_hora;
        $fecha = '';
        if ($this->inicio_dia <> '01-01-1970'){
            $fecha = $fecha . $this->inicio_dia;
        }
        if (!empty($this->inicio_hora)){
            $fecha = $fecha . ' ' . $this->inicio_hora;
        }
        $this->inicio = $fecha;
        
        // $this->fin = $this->fin_dia . ' ' . $this->fin_hora;
        $fecha = '';
        if ($this->fin_dia <> '01-01-1970'){
        //if (!empty($this->fin_dia)){
            $fecha = $fecha . $this->fin_dia;
        }
        if (!empty($this->fin_hora)){
            $fecha = $fecha . ' ' . $this->fin_hora;
        }
        $this->fin = $fecha;

        
        // Para evitar la inección de sql
        $utils = $this->toolBox()->utils();
        $this->nombre = $utils->noHtml($this->nombre);
        $this->nick = $utils->noHtml($this->nick);
        $this->codrole = $utils->noHtml($this->codrole);
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
