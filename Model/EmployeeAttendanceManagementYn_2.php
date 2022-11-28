<?php

// NO OLVIDEMOS QUE LOS CAMBIOS QUE HAGAMOS EN ESTE MODELO TENDRÍAMOS QUE HACERLOS TAMBIEN POSIBLEMENTE EN MODELO Employee_attendance_management_yn.php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;

class EmployeeAttendanceManagementYn_2 extends Base\ModelClass {
    use Base\ModelTrait;

    public $idemployee_attendance_management_yn;
        
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

    public $idemployee;
    public $observaciones;
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->activo = true; // Por defecto estará activo
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
        new Employee();

        return parent::install();
    }

    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idemployee_attendance_management_yn';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'employees_attendance_management_yn';
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
        if (empty($this->idemployee_attendance_management_yn)) {
            $this->idemployee_attendance_management_yn = $this->newCode();
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
        $utils = $this->toolBox()->utils();

        $this->observaciones = $utils->noHtml($this->observaciones);

        // Rellenamos el campo nombre de este modelo pues está ligado con campo nombre de tabla empleados
        // no hace falta actualizarlo siempre. porque la tabla employees es de este mismo pluggin y desde el test de employee.php actualizo el campo nombre de tabla dirvers
        if (!empty($this->idemployee)) {
            $sql = ' SELECT EMPLOYEES.NOMBRE AS title '
                 . ' FROM EMPLOYEES '
                 . ' WHERE EMPLOYEES.IDEMPLOYEE = ' . $this->idemployee
                 ;

            $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

            foreach ($registros as $fila) {
                $this->nombre = $fila['title'];
            }
        }

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
	
    private function evitarInyeccionSQL()
    {
        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
    }
    
    public function getEmployee() {
        $employee = new Employee(); // Creamos el modelo
        $employee->loadFromCode($this->idemployee); // Cargamos un modelo en concreto, identificándolo por idemployee
        return $employee; // Devolvemos el modelo servicio seleccionado
    }
    
    public function url(string $type = 'auto', string $list = 'List'): string {
        // Le estamos diciendo que si el parámetro $type es de tipo 'list', pues debe de redirigirse a lo que devuelva la function getServicio()->url 
        // y pestaña ListService_itinerary
        if ($type == 'list') {
            return $this->getEmployee()->url() . "&activetab=ListEmployee_attendance_management_yn"; // "&activetab=ListService_itinerary" corresponde a la pestaña a la que quiero que vuelva
        } 
        
        // Le estamos diciendo que si el parámetro $type NO es de tipo 'list', pues debe de redirigirse a la url por defecto devuelta
        // por el modelo parent
        return parent::url($type, $list);
    }	
    
}
