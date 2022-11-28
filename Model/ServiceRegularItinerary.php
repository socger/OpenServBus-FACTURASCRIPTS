<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;

class ServiceRegularItinerary extends Base\ModelClass {
    use Base\ModelTrait;

    public $idservice_regular_itinerary;
        
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
    
    public $idservice_regular;

    public $orden;
    public $idstop;

    public $hora;
    public $inicio_hora;
    
    public $kms;
    public $kms_vacios;
    public $kms_enExtranjero;
    public $pasajeros_entradas;
    public $pasajeros_salidas;
    
    public $observaciones;
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->activo = true; // Por defecto estará activo
        $this->kms = 0;
        $this->kms_vacios = false;
        $this->pasajeros_entradas = 0;
        $this->pasajeros_salidas = 0;
        $this->kms_enExtranjero = false;
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
        new ServiceRegular();
        new Stop();

        return parent::install();
    }

    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idservice_regular_itinerary';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'service_regular_itineraries';
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
        if (empty($this->idservice_regular_itinerary)) {
            $this->idservice_regular_itinerary = $this->newCode();
        }

        $this->rellenarDatosAlta();
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }

        return parent::saveInsert($values);
    }
    
    public function test() {
        $this->crearHora();
        
        if ($this->checkService() == false){return false;}
        if ($this->checkParada() == false){return false;}
        if ($this->checkHora() == false){return false;}
        if ($this->checkPasajeros() == false){return false;}
        
        $this->comprobarOrden();
        
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
    
    private function comprobarOrden()
    {
        if (empty($this->orden) or $this->orden === 0) {
            // Comprobamos si la cuenta existe
            $sql = ' SELECT MAX(service_regular_itineraries.orden) AS orden '
                 . ' FROM service_regular_itineraries '
                 . ' WHERE service_regular_itineraries.idservice_regular = ' . $this->idservice_regular
                 . ' ORDER BY service_regular_itineraries.idservice_regular '
                 .        ' , service_regular_itineraries.orden '
                 ;
            
            $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

            foreach ($registros as $fila) {
                if (empty($fila['orden'])) {
                    $this->orden = 5;
                } else {
                    $this->orden = ($fila['orden'] + 5);
                }
            }

        }
    }
    
    private function checkService()
    {
        $a_devolver = true;
        if (empty($this->idservice_regular)) 
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('Debe de asignar el servicio regular al que pertenece este itinerario.');
        }
        return $a_devolver;
    }
	
    private function checkHora()
    {
        $a_devolver = true;
        if (empty($this->hora)) 
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('Falta la hora en la que debe de estar en la parada.');
        }
        return $a_devolver;
    }
    
    private function checkParada()
    {
        $a_devolver = true;
        if ( empty($this->idstop) )
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('Debe de elegir una parada.');
        }
        return $a_devolver;
    }
    
    private function checkPasajeros()
    {
        $a_devolver = true;
        if ( empty($this->pasajeros_entradas) and
             empty($this->pasajeros_salidas) ) 
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('Debe de asignar la cantidad de pasajeros a recoger/dejar.');
        }
        return $a_devolver;
    }
	
    private function evitarInyeccionSQL()
    {
        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
    }
    
    private function crearHora()
    {
        $fecha = '';
        if (!empty($this->inicio_hora)){
            $fecha = date('d-m-Y') . ' ' . $this->inicio_hora;
        }
        $this->hora = $fecha;
    }
    
    public function getServicioRegular() {
        $servicioRegular = new Service_regular(); // Creamos el modelo
        $servicioRegular->loadFromCode($this->idservice_regular); // Cargamos un modelo en concreto, identificándolo por idservice_regular
        return $servicioRegular; // Devolvemos el modelo servicio regular seleccionado
    }
    
    /*public function url(string $type = 'auto', string $list = 'List'): string {
        // Le estamos diciendo que si el parámetro $type es de tipo 'list', pues debe de redirigirse a lo que devuelva la function getServicioRegular()->url 
        // y pestaña ListService_regular_itinerary
        if ($type == 'list') {
            return $this->getServicioRegular()->url() . "&activetab=ListService_regular_itinerary"; // "&activetab=ListService_regular_itinerary" corresponde a la pestaña a la que quiero que vuelva
        } 
        
        // Le estamos diciendo que si el parámetro $type NO es de tipo 'list', pues debe de redirigirse a la url por defecto devuelta
        // por el modelo parent
        return parent::url($type, $list);
    }	*/
    
}

