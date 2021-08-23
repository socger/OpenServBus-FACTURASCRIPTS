<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

//use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Model\Base;

class Service_regular_combination_serv extends Base\ModelClass {
    use Base\ModelTrait;

    public $idservice_regular_combination_serv;
        
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
    
    
    public $idservice_regular_combination;
    public $idservice_regular;
    public $iddriver;
    public $idvehicle;
    
    public $observaciones;

    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        $this->activo = true; // Por defecto estará activo
    }
    
    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idservice_regular_combination_serv';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'service_regular_combination_servs';
    }



    // Para realizar algo antes o después del borrado ... todo depende de que se ponga antes del parent o después
    public function delete()
    {
        $parent_devuelve = parent::delete();
        $this->actualizarCombinadoSNEnServicioRegular();
        return $parent_devuelve;
    }

    
    // Para realizar cambios en los datos antes de guardar por modificación
    protected function saveUpdate(array $values = [])
    {
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }
        
        $parent_devuelve = parent::saveUpdate($values);
        $this->actualizarCombinadoSNEnServicioRegular();
        return $parent_devuelve;
    }

    // Para realizar cambios en los datos antes de guardar por alta
    protected function saveInsert(array $values = [])
    {
        // Creamos el nuevo id
        if (empty($this->idservice_regular_combination_serv)) {
            $this->idservice_regular_combination_serv = $this->newCode();
        }
        
        $this->rellenarDatosAlta();
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }
        
        $parent_devuelve = parent::saveInsert($values);
        $this->actualizarCombinadoSNEnServicioRegular();
        return $parent_devuelve;
    }
    
    public function test()
    {
        if ($this->rellenarConductorVehiculoSiVacios() === false) {
            return false;
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
        
    private function actualizarCombinadoSNEnServicioRegular()
    {
        $sql = ' SELECT COUNT(*) AS cantidad '
             . ' FROM service_regular_combination_servs '
             . ' WHERE service_regular_combination_servs.idservice_regular = ' . $this->idservice_regular . ' '
             . ' AND service_regular_combination_servs.activo = 1 '
             . ' ORDER BY service_regular_combination_servs.idservice_regular '
             ;

        $combinadoSN = 0;

        $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

        foreach ($registros as $fila) {
            if ($fila['cantidad'] > 0){
                $combinadoSN = 1;
            }
        }
        
        // Rellenamos el nombre del empleado en otras tablas
        $sql = "UPDATE service_regulars "
             . "SET service_regulars.combinadoSN = " . $combinadoSN . " "
             . "WHERE service_regulars.idservice_regular = " . $this->idservice_regular . ";";

        self::$dataBase->exec($sql);
    }
    
    private function rellenarConductorVehiculoSiVacios() : bool
    {
        $aDevolver = true;

        // Comprobar si falta vehículo o conductor
        if (empty($this->iddriver) or empty($this->idvehicle)) {
            // Cargamos el conductor y vehículo de la combinación
            $sql = ' SELECT service_regular_combinations.iddriver '
                 .      ' , service_regular_combinations.idvehicle '
                 . ' FROM service_regular_combinations '
                 . ' WHERE service_regular_combinations.idservice_regular_combination = ' . $this->idservice_regular_combination
                 ;

            $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

            foreach ($registros as $fila) {
                if (empty($this->iddriver)) {
                    $this->iddriver = $fila['iddriver'];
                    if (!empty($this->iddriver)) {
                        $this->toolBox()->i18nLog()->info( "Conductor rellenado automáticamente desde la Combinación." );
                    }
                }

                if (empty($this->idvehicle)) {
                    $this->idvehicle = $fila['idvehicle'];
                    if (!empty($this->idvehicle)) {
                        $this->toolBox()->i18nLog()->info( "Vehículo rellenado automáticamente desde la Combinación." );
                    }
                }
            }

        
            // Si tras cargar de la combinación todavía hay falta de vehículo o conductor,
            // intentamos cargar conductor o vehículo del servicio regular
            if (empty($this->iddriver) or empty($this->idvehicle)) {
                $sql = ' SELECT service_regulars.iddriver '
                     .      ' , service_regulars.idvehicle '
                     . ' FROM service_regulars '
                     . ' WHERE service_regulars.idservice_regular = ' . $this->idservice_regular
                     ;

                $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

                foreach ($registros as $fila) {
                    if (empty($this->iddriver)) {
                        $this->iddriver = $fila['iddriver'];
                        if (!empty($this->iddriver)) {
                            $this->toolBox()->i18nLog()->info( "Conductor rellenado automáticamente desde el Servicio Regular." );
                        }
                    }

                    if (empty($this->idvehicle)) {
                        $this->idvehicle = $fila['idvehicle'];
                        if (!empty($this->idvehicle)) {
                            $this->toolBox()->i18nLog()->info( "Vehículo rellenado automáticamente desde el Servicio Regular." );
                        }
                    }
                }
            }
            
            // Si todavía sigue faltando el vehículo o el conductor
            // Saltará la restricción de campo obligatorio de la tabla
            if (empty($this->iddriver) or empty($this->idvehicle)) {
                $aRellenar = '';
                $tampoco = 'Además tampoco estaba rellenado';
                $noPude = 'No lo pude completar';
                
                if (empty($this->iddriver)) {
                    if ($aRellenar === '') {
                        $aRellenar .= ' y ';
                        $tampoco = 'Además tampoco estaban rellenados';
                        $noPude = 'No los pude completar';
                    }
                    $aRellenar .= 'el conductor';
                }
                
                if (empty($this->idvehicle)) {
                    if ($aRellenar === '') {
                        $aRellenar .= ' y ';
                        $tampoco = 'Además tampoco estaban rellenados';
                    }
                    $aRellenar .= 'el vehículo';
                }
                
                $this->toolBox()->i18nLog()->error( "Debe completar $aRellenar. $tampoco ni en la Combinación de servicios elegida, ni en el Servicio Regular elegido ... $noPude." );
                $aDevolver = false;
            }
        }
        
        return $aDevolver;
    }
    
}
