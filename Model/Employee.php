<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;

class Employee extends Base\ModelClass {
    use Base\ModelTrait;

    public $idemployee;
        
    public $user_fecha;
    public $user_nick;
    public $fechaalta;
    public $useralta;
    public $fechamodificacion;
    public $usermodificacion;
    public $activo;
    public $fechabaja;
    public $userbaja;

    public $cod_employee;
    public $nombre;
    public $user_facturascripts_nick;
    public $tipoidfiscal;
    public $cifnif;
            
    public $idempresa;
    public $ciudad;
    public $provincia;
    public $codpais;
    public $codpostal;
    public $apartado;
    public $direccion;
    public $telefono1;
    public $telefono2;
    public $email;
    public $web;
    public $observaciones;

    public $fecha_nacimiento;
    public $num_seg_social;
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->codpais = $this->toolBox()->appSettings()->get('default', 'codpais');
        $this->activo = true; // Por defecto estará activo
    }
    
    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idemployee';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'employees';
    }
    
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
        if (empty($this->idemployee)) {
            $this->idemployee = $this->newCode();
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
    
    public function test()
    {
        // Nos rellena la empresa (si no se ha elegido) con la empresa por defecto
        if (empty($this->idempresa)) {
            $this->idempresa = $this->toolBox()->appSettings()->get('default', 'idempresa');
        }

        $utils = $this->toolBox()->utils();

        $this->cod_employee = $utils->noHtml($this->cod_employee);
        $this->user_facturascripts_nick = $utils->noHtml($this->user_facturascripts_nick);
        $this->tipoidfiscal = $utils->noHtml($this->tipoidfiscal);
        $this->cifnif = $utils->noHtml($this->cifnif);
        $this->nombre = $utils->noHtml($this->nombre);
        $this->ciudad = $utils->noHtml($this->ciudad);
        $this->provincia = $utils->noHtml($this->provincia);
        $this->codpais = $utils->noHtml($this->codpais);
        $this->codpostal = $utils->noHtml($this->codpostal);
        $this->apartado = $utils->noHtml($this->apartado);
        $this->direccion = $utils->noHtml($this->direccion);
        $this->telefono1 = $utils->noHtml($this->telefono1);
        $this->telefono2 = $utils->noHtml($this->telefono2);
        $this->email = $utils->noHtml($this->email);
        $this->web = $utils->noHtml($this->web);
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->num_seg_social = $utils->noHtml($this->num_seg_social);

        return parent::test();
    }

    
}
