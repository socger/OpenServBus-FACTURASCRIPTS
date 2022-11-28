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
    public $motivobaja;

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
    public $driver_yn;
    public $es_Conductor_SI_NO;
    
    public $tipo_contrato;
    public $fecha_inicio;
    public $fecha_fin;
    
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
        if (empty($this->idemployee)) {
            $this->idemployee = $this->newCode();
        }

        // Rellenamos el cod_employee si no lo introdujo el usuario
        if (empty($this->cod_employee)) {
            $this->cod_employee = (string) $this->newCode();
        }

        $this->rellenarDatosAlta();
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }
        
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
        

        /* Quitamos esta parte porque se rellena automáticamente desde el mantenimiento de contratos
            // Nos rellena la empresa (si no se ha elegido) con la empresa por defecto
            if (empty($this->idempresa)) {
                $this->idempresa = $this->toolBox()->appSettings()->get('default', 'idempresa');
        }
        */
        $this->ComprobarSiEsConductor();
        $this->actualizarNombreEmpleadoEn();

        
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
    
    private function ComprobarSiEsConductor()
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
        
    public function actualizarNombreEmpleadoEn()
    {
        // Rellenamos el nombre del empleado en otras tablas
        $sql = "UPDATE drivers SET drivers.nombre = '" . $this->nombre . "' WHERE drivers.idemployee = " . $this->idemployee . ";";
        self::$dataBase->exec($sql);
        
        $sql = "UPDATE employees_attendance_management_yn SET employees_attendance_management_yn.nombre = '" . $this->nombre . "' WHERE employees_attendance_management_yn.idemployee = " . $this->idemployee . ";";
        self::$dataBase->exec($sql);
        
        $sql = "UPDATE employee_contracts SET employee_contracts.nombre = '" . $this->nombre . "' WHERE employee_contracts.idemployee = " . $this->idemployee . ";";
        self::$dataBase->exec($sql);

        $sql = "UPDATE helpers SET helpers.nombre = '" . $this->nombre . "' WHERE helpers.idemployee = " . $this->idemployee . ";";
        self::$dataBase->exec($sql);
        
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
        $this->motivobaja = $utils->noHtml($this->motivobaja);
    }
	
}
