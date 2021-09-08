<?php

// NO OLVIDEMOS QUE LOS CAMBIOS QUE HAGAMOS EN ESTE MODELO TENDRÍAMOS QUE HACERLOS TAMBIEN POSIBLEMENTE EN MODELO Employee_contract.php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

//use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Model\Base;
use FacturaScripts\Plugins\OpenServBus\Model\Employee;
use FacturaScripts\Plugins\OpenServBus\Model\Employee_contract_type;

class Employee_contract_2 extends Base\ModelClass {
    use Base\ModelTrait;
    
    public $idemployee_contract;
        
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
    public $idempresa;
    public $idemployee_contract_type;
    
    public $observaciones;
    public $nombre;
    
    public $fecha_inicio;
    public $fecha_fin;
    public $esta_Activo_SI_NO;
    
    
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
        new Employee_contract_type();

        return parent::install();
    }

    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idemployee_contract';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'employee_contracts';
    }

    // Para realizar algo antes o después del borrado ... todo depende de que se ponga antes del parent o después
    public function delete()
    {
        $parent_devuelve = parent::delete();
        
        $this->Actualizar_idempresa_en_employees();
        $this->actualizar_campo_activo_enContratos_del_Empleado();
                
        return $parent_devuelve;
        
        // return parent::delete();
    }

    // Para realizar cambios en los datos antes de guardar por modificación
    protected function saveUpdate(array $values = [])
    {
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }
        
        $parent_devuelve = parent::saveUpdate($values);
        
        $this->Actualizar_idempresa_en_employees();
        $this->actualizar_campo_activo_enContratos_del_Empleado();
        
        return $parent_devuelve;
    }

    // Para realizar cambios en los datos antes de guardar por alta
    protected function saveInsert(array $values = [])
    {
        // Creamos el nuevo id
        if (empty($this->idemployee_contract)) {
            $this->idemployee_contract = $this->newCode();
        }
        
        $this->rellenarDatosAlta();
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }
        
        $parent_devuelve = parent::saveInsert($values);
        
        $this->Actualizar_idempresa_en_employees();
        $this->actualizar_campo_activo_enContratos_del_Empleado();
        
        return $parent_devuelve;
        //return parent::saveInsert($values);
    }
    
    public function test()
    {
        $utils = $this->toolBox()->utils();

        $this->observaciones = $utils->noHtml($this->observaciones);

        // Rellenamos el campo nombre de este modelo pues está ligado con campo nombre de tabla empleados
        // no hace falta actualizarlo siempre, porque la tabla employees es de este mismo pluggin y desde 
        // el test de employee.php actualizo el campo nombre de tabla dirvers
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
    
    protected function Actualizar_idempresa_en_employees()
    {
        // Completamos el campo idempresa de la tabla employee
        $sql = " UPDATE employees "
          // . " SET employees.idempresa = ( SELECT employee_contracts.idempresa "
             . " SET employees.idempresa = ( SELECT IF(employee_contracts.idempresa IS NOT NULL, employee_contracts.idempresa, 0) "
                                         . " FROM employee_contracts "
                                         . " WHERE employee_contracts.idemployee = " . $this->idemployee . " "
                                         .   " AND employee_contracts.activo = 1 "
                                         . " ORDER BY employee_contracts.idemployee "
                                         .        " , employee_contracts.fecha_inicio DESC "
                                         .        " , employee_contracts.fecha_fin DESC "
                                         . " LIMIT 1 ) "
             . " WHERE employees.idemployee = " . $this->idemployee . ";";
        
        self::$dataBase->exec($sql);
    }
    
    private function actualizar_campo_activo_enContratos_del_Empleado()
    {
        // Buscamos el contrato con fecha_inicio + Fecha_fin más alta
        $sql = " SELECT IF(employee_contracts.idemployee_contract IS NOT NULL, employee_contracts.idemployee_contract, 0) AS idemployee_contract "
             . " FROM employee_contracts "
             . " WHERE employee_contracts.idemployee = " . $this->idemployee . " "
             . " ORDER BY employee_contracts.idemployee "
             .        " , employee_contracts.fecha_inicio DESC "
             .        " , employee_contracts.fecha_fin DESC "
             . " LIMIT 1 ";

        $contratos = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

        if (!empty($contratos)){
            // Se ha encontrado algún contrato de ese empleado
            foreach ($contratos as $contrato) {
                // Ponemos como activo el encontrado con mayor fecha_inicio + fecha_fin
                $sql = " UPDATE employee_contracts "
                     . " SET employee_contracts.activo = 1 "
                     .   " , employee_contracts.usermodificacion = '" . $this->user_nick . "' "
                     .   " , employee_contracts.fechamodificacion = '" . $this->user_fecha . "' "
                     .   " , employee_contracts.userbaja = null "
                     .   " , employee_contracts.fechabaja = null "
                     . " WHERE employee_contracts.idemployee_contract = " . $contrato['idemployee_contract'] . " ";

                self::$dataBase->exec($sql);

                // Ponemos como no activos el resto
                $sql = " UPDATE employee_contracts "
                     . " SET employee_contracts.activo = 0 "
                     .   " , employee_contracts.usermodificacion = '" . $this->user_nick . "' "
                     .   " , employee_contracts.fechamodificacion = '" . $this->user_fecha . "' "
                     .   " , employee_contracts.userbaja = '" . $this->user_nick . "' "
                     .   " , employee_contracts.fechabaja = '" . $this->user_fecha . "' "
                        
                     . " WHERE employee_contracts.idemployee_contract <> " . $contrato['idemployee_contract'] . " "
                     .   " AND employee_contracts.idemployee = " . $this->idemployee . " ";

                self::$dataBase->exec($sql);
            }
        }
        
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
            return $this->getEmployee()->url() . "&activetab=ListEmployee_contract"; // "&activetab=ListService_itinerary" corresponde a la pestaña a la que quiero que vuelva
        } 
        
        // Le estamos diciendo que si el parámetro $type NO es de tipo 'list', pues debe de redirigirse a la url por defecto devuelta
        // por el modelo parent
        return parent::url($type, $list);
    }	
    
}
