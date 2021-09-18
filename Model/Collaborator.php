<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

// use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
// use FacturaScripts\Dinamic\Model\Proveedor;
use FacturaScripts\Core\Model\Base;
use FacturaScripts\Plugins\OpenServBus\Model\Proveedor; // Proveedor es una extensión de Proveedor de FS

class Collaborator extends Base\ModelClass {
    use Base\ModelTrait;

    public $idcollaborator;
        
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

    public $codproveedor;
    public $observaciones;
    public $nombre;
    
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
        new Proveedor();

        return parent::install();
    }

    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idcollaborator';
    }

    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'collaborators';
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
        if (empty($this->idcollaborator)) {
            $this->idcollaborator = $this->newCode();
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
        $this->actualizarCampoNombre();
        $this->actualizarNombreColaboradorEn();
        
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
    
    private function actualizarNombreColaboradorEn()
    {
        // Rellenamos el nombre del empleado en otras tablas
        $sql = "UPDATE drivers SET drivers.nombre = '" . $this->nombre . "' WHERE drivers.idcollaborator = " . $this->idcollaborator . ";";
        self::$dataBase->exec($sql);

        $sql = "UPDATE helpers SET helpers.nombre = '" . $this->nombre . "' WHERE helpers.idcollaborator = " . $this->idcollaborator . ";";
        self::$dataBase->exec($sql);
    }
      
    private function actualizarCampoNombre()
    {
        // Rellenamos el campo nombre de este modelo pues está ligado con campo nombre de tabla proveedores
        // pero siempre lo actualizamos porque pueden cambiar el nombre del proveedor
        if (!empty($this->codproveedor)) {
            /* Esta podría ser una manera, pero implica hacer un uses al principio contra el modelo proveedor

            $proveedorModel = new Proveedor(); // Tengo que poner en el uses la clase modelo Proveedor
            $where = [new DataBaseWhere('codproveedor', $this->codproveedor)]; // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/databasewhere-478
            $prov_Buscar = $proveedorModel->all($where);

            foreach ($prov_Buscar as $prov) {
               $this->nombre = $prov->nombre; 
            }
            */

            $sql = ' SELECT PROVEEDORES.NOMBRE AS title '
                 . ' FROM PROVEEDORES '
                 . ' WHERE PROVEEDORES.CODPROVEEDOR = ' . $this->codproveedor
                 ;
            $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

            foreach ($registros as $fila) {
                $this->nombre = $fila['title'];
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
        $this->codproveedor = $utils->noHtml($this->codproveedor);
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
    }
	
}
