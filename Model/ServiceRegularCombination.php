<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;

class ServiceRegularCombination extends Base\ModelClass {
    use Base\ModelTrait;

    public $idservice_regular_combination;
        
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
    
    public $iddriver_1;
    public $driver_alojamiento_1;
    public $driver_observaciones_1;
    
    public $iddriver_2;
    public $driver_alojamiento_2;
    public $driver_observaciones_2;
    
    public $iddriver_3;
    public $driver_alojamiento_3;
    public $driver_observaciones_3;
    
    public $idvehicle;
    
    public $lunes;
    public $martes;
    public $miercoles;
    public $jueves;
    public $viernes;
    public $sabado;
    public $domingo;
    
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
        new Driver();
        new Vehicle();

        return parent::install();
    }

    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idservice_regular_combination';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'service_regular_combinations';
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
        if (empty($this->idservice_regular_combination)) {
            $this->idservice_regular_combination = $this->newCode();
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
        $this->rellenarConductorVehiculoSiVacios();
        
        if ($this->hayServiciosQueNoCoincidenLosDiasDeSemana() == true) {
            return false;
        }
        
        $this->evitarInyeccionSQL();
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'ListServiceRegular'): string
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
        
        $this->driver_alojamiento_1 = $utils->noHtml($this->driver_alojamiento_1);
        $this->driver_observaciones_1 = $utils->noHtml($this->driver_observaciones_1);
        
        $this->driver_alojamiento_2 = $utils->noHtml($this->driver_alojamiento_2);
        $this->driver_observaciones_2 = $utils->noHtml($this->driver_observaciones_2);
        
        $this->driver_alojamiento_3 = $utils->noHtml($this->driver_alojamiento_3);
        $this->driver_observaciones_3 = $utils->noHtml($this->driver_observaciones_3);
    }
    
    private function rellenarConductorVehiculoSiVacios()
    {
        if (empty($this->iddriver_1) || empty($this->idvehicle)) {
            $this->toolBox()->i18nLog()->info( 'Si no rellena el vehículo o el conductor, este será el orden de prioridades para el Montaje de Servicios:'
                                             . ' 1º Combinación - Servicio Regular, 2º Combinación y 3º Servicio Regular' );
        }
    }
    
    private function hayServiciosQueNoCoincidenLosDiasDeSemana() : bool
    {
        $serviciosConDiasDiferentes = [];
        
        $sql = ' SELECT service_regulars.lunes '
             .      ' , service_regulars.martes '
             .      ' , service_regulars.miercoles '
             .      ' , service_regulars.jueves '
             .      ' , service_regulars.viernes '
             .      ' , service_regulars.sabado '
             .      ' , service_regulars.domingo '
             .      ' , service_regulars.idservice_regular '
             .      ' , service_regulars.nombre '
             . ' FROM service_regular_combination_servs '
             . ' LEFT JOIN service_regulars on (service_regulars.idservice_regular = service_regular_combination_servs.idservice_regular) '
             . ' WHERE service_regular_combination_servs.idservice_regular_combination = ' . $this->idservice_regular_combination
             ;

        $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

        foreach ($registros as $fila) {
            $coincideAlgunDia = false;
            
            // Una combinación puede tener varios servicios regulares, por lo 
            // que tengo que comprobar todos sus servicios
            if ($this->lunes == 1) {
                if ($this->lunes == $fila['lunes']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->martes == 1) {
                if ($this->martes == $fila['martes']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->miercoles == 1) {
                if ($this->miercoles == $fila['miercoles']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->jueves == 1) {
                if ($this->jueves == $fila['jueves']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->viernes == 1) {
                if ($this->viernes == $fila['viernes']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->sabado == 1) {
                if ($this->sabado == $fila['sabado']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->domingo == 1) {
                if ($this->domingo == $fila['domingo']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($coincideAlgunDia === false) {
                $serviciosConDiasDiferentes[] = $fila['nombre'];
            }
        }
        
        if (empty($serviciosConDiasDiferentes)) {
            return false;
        } else {
            foreach ($serviciosConDiasDiferentes as $servicio) {
                $this->toolBox()->i18nLog()->error( "Los días de la semana del servicio $servicio no coinciden con los días de la semana de esta combinación." );
            }
            
            return true;
        }
    }
    
}
