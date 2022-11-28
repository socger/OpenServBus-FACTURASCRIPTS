<?php

// Lo que modifiquemos en este modelo, tendríamos que ver si lo modificamos en el modelo Employee_documentation_2.php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;


class EmployeeDocumentation extends Base\ModelClass {
    use Base\ModelTrait;
    
    public $idemployee_documentation;
        
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
    public $idemployee;
    public $iddocumentation_type;
    public $fecha_caducidad;
    
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
        new DocumentationType();

        return parent::install();
    }

    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idemployee_documentation';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'employee_documentations';
    }

    // Para realizar algo antes o después del borrado ... todo depende de que se ponga antes del parent o después
    public function delete()
    {
        $parent_devuelve = parent::delete();
        
        // $this->Actualizar_idempresa_en_employees();
                
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
        
        // $this->Actualizar_idempresa_en_employees();
        
        return $parent_devuelve;
    }

    // Para realizar cambios en los datos antes de guardar por alta
    protected function saveInsert(array $values = [])
    {
        // Creamos el nuevo id
        if (empty($this->idemployee_documentation)) {
            $this->idemployee_documentation = $this->newCode();
        }
        
        $this->rellenarDatosAlta();
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }
        
        $parent_devuelve = parent::saveInsert($values);
        
        // $this->Actualizar_idempresa_en_employees();
        
        return $parent_devuelve;
        //return parent::saveInsert($values);
    }
    
    public function test()
    {
        if (empty($this->fecha_caducidad)) {
            if ($this->ComprobarSiEsObligadaFechaCaducidad() == 1) {
                $this->toolBox()->i18nLog()->error('Para el tipo de documento elegido, necesitamos rellenar la fecha de caducidad');
                return false;
            }
        }
        
        $this->evitarInyeccionSQL();
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'ListVehicleDocumentation'): string
    {
        return parent::url($type, $list . '?activetab=List');
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
    
    private function ComprobarSiEsObligadaFechaCaducidad()
    {
        $sql = ' SELECT documentation_types.fechacaducidad_obligarla '
             . ' FROM documentation_types '
             . ' WHERE documentation_types.iddocumentation_type = ' . $this->iddocumentation_type . " "
             ;

        $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

        foreach ($registros as $fila) {
            return $fila['fechacaducidad_obligarla'];
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
        $this->nombre = $utils->noHtml($this->nombre);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
    }
	
}
