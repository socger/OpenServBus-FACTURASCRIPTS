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
    public $idcollaborator;
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
    public $driver_yn;
    
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

        // Rellenamos el cod_employee si no lo introdujo el usuario
        if (empty($this->cod_employee)) {
            $this->cod_employee = (string) $this->newCode();
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
        // Comprobamos que el código de empleado si se ha introducido correctamente
        if (!empty($this->cod_employee) && 1 !== \preg_match('/^[A-Z0-9_\+\.\-]{1,10}$/i', $this->cod_employee)) {
            $this->toolBox()->i18nLog()->error(
                'invalid-alphanumeric-code',
                ['%value%' => $this->cod_employee, '%column%' => 'cod_employee', '%min%' => '1', '%max%' => '10']
            );
            
            return false;
        }
        
        // Exijimos que se introduzca idempresa o idcollaborator
        if ( (empty($this->idempresa)) 
         and (empty($this->idcollaborator))
           ) 
        {
            $this->toolBox()->i18nLog()->error('Debe de confirmar si es un empleado nuestro o de una empresa colaboradora');
            return false;
        }

        if ( (!empty($this->idempresa)) 
         and (!empty($this->idcollaborator))
           ) 
        {
            $this->toolBox()->i18nLog()->error('O es un empleado nuestro o de una empresa colaboradora, pero ambos no');
            return false;
        }
        
        /* Quitamos esta parte porque si el usuario rellenaba idControllator y idempresa estaba vacío, lo rellenaba automáticamente con la empresa por defecto
            // Nos rellena la empresa (si no se ha elegido) con la empresa por defecto
            if (empty($this->idempresa)) {
                $this->idempresa = $this->toolBox()->appSettings()->get('default', 'idempresa');
        }
        */

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
        
        $this->ComprobarSiEsConductor();
        $this->actualizarNombreEmpleadoEn();

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
    
    protected function ComprobarSiEsConductor()
    {
        // Comprobar si está creado como conductor
        // Esto lo hacemos porque en EditEmployee.xml hemos creado el widget checkbox para driver_yn como readonly, pero permite modificarlo
        $sql = ' SELECT COUNT(*) AS cuantos '
             . ' FROM drivers '
             . ' WHERE drivers.idemployee = ' . $this->idemployee
             ;

        $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

        foreach ($registros as $fila) {
            $this->driver_yn = 0;
            if ($fila['cuantos'] > 0) {
                $this->driver_yn = 1;
            }
        }
    }
        
    protected function actualizarNombreEmpleadoEn()
    {
        // Rellenamos el nombre del empleado en otras tablas
        $sql = "UPDATE drivers SET drivers.nombre = '" . $this->nombre . "' WHERE drivers.idemployee = " . $this->idemployee . ";";
        self::$dataBase->exec($sql);
        
        $sql = "UPDATE employees_attendance_management_yn SET employees_attendance_management_yn.nombre = '" . $this->nombre . "' WHERE employees_attendance_management_yn.idemployee = " . $this->idemployee . ";";
        self::$dataBase->exec($sql);
        
        $sql = "UPDATE employee_contracts SET employee_contracts.nombre = '" . $this->nombre . "' WHERE employee_contracts.idemployee = " . $this->idemployee . ";";
        self::$dataBase->exec($sql);
    }

}
