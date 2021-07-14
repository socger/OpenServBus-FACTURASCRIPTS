<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

//use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Model\Base;

class Driver extends Base\ModelClass {
    use Base\ModelTrait;

    public $iddriver;
        
    public $user_fecha;
    public $user_nick;
    public $fechaalta;
    public $useralta;
    public $fechamodificacion;
    public $usermodificacion;
    public $activo;
    public $fechabaja;
    public $userbaja;

    public $idemployee;
    public $observaciones;
    public $nombre;
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->activo = true; // Por defecto estará activo
    }
    
    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'iddriver';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'drivers';
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
        if (empty($this->iddriver)) {
            $this->iddriver = $this->newCode();
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

        // Completamos el campo driver_yn de la tabla employee
        $sql = "UPDATE employees SET employees.driver_yn = 1 WHERE employees.idemployee = " . $this->idemployee . ";";
        self::$dataBase->exec($sql);
        
        return parent::test();
    }

    
}
