<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

//use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Model\Base;
use FacturaScripts\Plugins\OpenServBus\Model\Driver;
use FacturaScripts\Plugins\OpenServBus\Model\Vehicle;
use FacturaScripts\Plugins\OpenServBus\Model\Service_regular_combination;
use FacturaScripts\Plugins\OpenServBus\Model\Service_regular;

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
        new Service_regular_combination();
        new Service_regular();

        return parent::install();
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
        
        if ($this->comprobarDiasSemana() === false) {
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
    
    private function comprobarDiasSemana() : bool
    {
        $coincideAlgunDíaDeLaSemana = false;
        
        $combinacionLunes = false;
        $combinacionMartes = false;
        $combinacionMiercoles = false;
        $combinacionJueves = false;
        $combinacionViernes = false;
        $combinacionSabado = false;
        $combinacionDomingo = false;
        
        $servRegularLunes = false;
        $servRegularMartes = false;
        $servRegularMiercoles = false;
        $servRegularJueves = false;
        $servRegularViernes = false;
        $servRegularSabado = false;
        $servRegularDomingo = false;

        // Traemos los días de la semana de la combinación
        $sql = ' SELECT service_regular_combinations.lunes '
             .      ' , service_regular_combinations.martes '
             .      ' , service_regular_combinations.miercoles '
             .      ' , service_regular_combinations.jueves '
             .      ' , service_regular_combinations.viernes '
             .      ' , service_regular_combinations.sabado '
             .      ' , service_regular_combinations.domingo '
             . ' FROM service_regular_combinations '
             . ' WHERE service_regular_combinations.idservice_regular_combination = ' . $this->idservice_regular_combination
             ;

        $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

        foreach ($registros as $fila) {
            $combinacionLunes = $fila['lunes'];
            $combinacionMartes = $fila['martes'];
            $combinacionMiercoles = $fila['miercoles'];
            $combinacionJueves = $fila['jueves'];
            $combinacionViernes = $fila['viernes'];
            $combinacionSabado = $fila['sabado'];
            $combinacionDomingo = $fila['domingo'];
        }
        
        // Traemos los días de la semana del servicio regular
        $sql = ' SELECT service_regulars.lunes '
             .      ' , service_regulars.martes '
             .      ' , service_regulars.miercoles '
             .      ' , service_regulars.jueves '
             .      ' , service_regulars.viernes '
             .      ' , service_regulars.sabado '
             .      ' , service_regulars.domingo '
             . ' FROM service_regulars '
             . ' WHERE service_regulars.idservice_regular = ' . $this->idservice_regular
             ;

        $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

        foreach ($registros as $fila) {
            $servRegularLunes = $fila['lunes'];
            $servRegularMartes = $fila['martes'];
            $servRegularMiercoles = $fila['miercoles'];
            $servRegularJueves = $fila['jueves'];
            $servRegularViernes = $fila['viernes'];
            $servRegularSabado = $fila['sabado'];
            $servRegularDomingo = $fila['domingo'];
        }
        
        // Un mismo servicio regular, puede estar en varias combinaciones, pero 
        // nunca varias veces en la misma combinación.
        // Por lo que debo de comprobar si alguno de los días de la semana 
        // elegidos para el servicio regular, corresponde con alguno de los días 
        // de la semana de la combinación
        if ($combinacionLunes == 1) {
            if ($combinacionLunes == $servRegularLunes) {
                $coincideAlgunDíaDeLaSemana = true;
            }
        }
        
        if ($combinacionMartes == 1) {
            if ($combinacionMartes == $servRegularMartes) {
                $coincideAlgunDíaDeLaSemana = true;
            }
        }
        
        if ($combinacionMiercoles == 1) {
            if ($combinacionMiercoles == $servRegularMiercoles) {
                $coincideAlgunDíaDeLaSemana = true;
            }
        }
        
        if ($combinacionJueves == 1) {
            if ($combinacionJueves == $servRegularJueves) {
                $coincideAlgunDíaDeLaSemana = true;
            }
        }
        
        if ($combinacionViernes == 1) {
            if ($combinacionViernes == $servRegularViernes) {
                $coincideAlgunDíaDeLaSemana = true;
            }
        }
        
        if ($combinacionSabado == 1) {
            if ($combinacionSabado == $servRegularSabado) {
                $coincideAlgunDíaDeLaSemana = true;
            }
        }
        
        if ($combinacionDomingo === 1) {
            if ($combinacionDomingo == $servRegularDomingo) {
                $coincideAlgunDíaDeLaSemana = true;
            }
        }
        
        if ($coincideAlgunDíaDeLaSemana === false) {
            $this->toolBox()->i18nLog()->error( "Ninguno de los días de la semana del servicio coincide con los días de la semana de la combinación." );
        }
        
        
        return $coincideAlgunDíaDeLaSemana;
    }
    
    public function getCombination() {
        $combination = new Service_regular_combination(); // Creamos el modelo
        $combination->loadFromCode($this->idservice_regular_combination); // Cargamos un modelo en concreto, identificándolo por idservice_regular
        return $combination; // Devolvemos el modelo
    }
    
    public function url(string $type = 'auto', string $list = 'List'): string {
        // Le estamos diciendo que si el parámetro $type es de tipo 'list', pues debe de redirigirse a lo que devuelva la function getServicioRegular()->url 
        // y pestaña ListService_regular_itinerary
        if ($type == 'list') {
            return $this->getCombination()->url() . "&activetab=ListService_regular_combination_serv"; // "&activetab=ListService_regular_combination_serv" corresponde a la pestaña a la que quiero que vuelva
        } 
        
        // Le estamos diciendo que si el parámetro $type NO es de tipo 'list', pues debe de redirigirse a la url por defecto devuelta
        // por el modelo parent
        return parent::url($type, $list);
    }	

}
