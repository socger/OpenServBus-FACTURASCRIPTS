<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Plugins\OpenServBus\Model\Service_regular;
use FacturaScripts\Plugins\OpenServBus\Model\Service_valuation_type;
use FacturaScripts\Core\Model\Impuesto;

class Service_regular_valuation extends Base\ModelClass {
    use Base\ModelTrait;

    public $idservice_regular_valuation;
        
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
    
    public $idservice_valuation_type;
    public $nombre;
    
    public $importe;
    public $importe_enextranjero;
    
    public $observaciones;
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->activo = true; // Por defecto estará activo
        $this->improte = 0;
        $this->total = 0;
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
        // needed dependency
        new Service_regular();
        new Service_valuation_type();
        new Impuesto();

        return parent::install();
    }

    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idservice_regular_valuation';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'service_regular_valuations';
    }

    private function actualizar_Importes(int $idServiceRegular)
    {
        $sql = " UPDATE service_regulars "
             . " SET service_regulars.importe = ( SELECT SUM(service_regular_valuations.importe) "
                                              . " FROM service_regular_valuations "
                                              . " WHERE service_regular_valuations.idservice_regular = " . $idServiceRegular . " "
                                              . " AND service_regular_valuations.activo = 1 ) "
               . " , service_regulars.importe_enextranjero = ( SELECT SUM(service_regular_valuations.importe_enextranjero)  "
                                                           . " FROM service_regular_valuations "
                                                           . " WHERE service_regular_valuations.idservice_regular = " . $idServiceRegular . " "
                                                           . " AND service_regular_valuations.activo = 1 ) "
             . " WHERE service_regulars.idservice_regular = " . $idServiceRegular . ";";

        self::$dataBase->exec($sql);
        
        $servicioRegular = new Service_regular(); // Creamos el modelo
        $servicioRegular->loadFromCode($idServiceRegular);
        $servicioRegular->rellenarTotal();
        $servicioRegular->save();
    }

    private function guardar(string $type, array $values = []): bool
    {
        $idServiceRegular = $this->idservice_regular;
        
        if ($type === 'U') {
            $aDevolver = parent::saveUpdate($values);
        } else {
            $aDevolver = parent::saveInsert($values);
        }
        
        if (true === $aDevolver) {
            $this->actualizar_Importes($idServiceRegular);
        }

        return $aDevolver;
    }

    // Para realizar algo antes o después del borrado ... todo depende de que se ponga antes del parent o después
    public function delete()
    {
        $idServiceRegular = $this->idservice_regular;
        
        $aDevolver = parent::delete();
        
        if (true === $aDevolver) {
            $this->actualizar_Importes($idServiceRegular);
        }

        return $aDevolver;
    }

    // Para realizar cambios en los datos antes de guardar por modificación
    protected function saveUpdate(array $values = [])
    {
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }

        return $this->guardar('U', $values);
    }

    // Para realizar cambios en los datos antes de guardar por alta
    protected function saveInsert(array $values = [])
    {
        // Creamos el nuevo id
        if (empty($this->idservice_regular_valuation)) {
            $this->idservice_regular_valuation = $this->newCode();
        }

        $this->rellenarDatosAlta();
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }

        return $this->guardar('I', $values);
    }
    
    public function test() {
        if ($this->checkService() == false){return false;}
        if ($this->checkDescripcion() == false){return false;}
        
        $this->comprobarOrden();
        
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
    
    private function comprobarOrden()
    {
        if (empty($this->orden) || $this->orden === 0) {
            // Comprobamos si la cuenta existe
            $sql = ' SELECT MAX(service_regular_valuations.orden) AS orden '
                 . ' FROM service_regular_valuations '
                 . ' WHERE service_regular_valuations.idservice_regular = ' . $this->idservice_regular
                 . ' ORDER BY service_regular_valuations.idservice_regular '
                 .        ' , service_regular_valuations.orden '
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
    
    private function evitarInyeccionSQL()
    {
        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        $this->nombre = $utils->noHtml($this->nombre);
    }
    
    public function getServicioRegular() {
        $servicioRegular = new Service_regular();
        $servicioRegular->loadFromCode($this->idservice_regular);
        return $servicioRegular;
    }
    
    public function url(string $type = 'auto', string $list = 'List'): string {
        if ($type == 'list') {
            return $this->getServicioRegular()->url() . "&activetab=ListService_regular_valuation";
        } 
        
        return parent::url($type, $list);
    }	
    
    private function checkDescripcion()
    {
        if (empty($this->nombre)) 
        {
            if (empty($this->idservice_valuation_type)) {
                $this->toolBox()->i18nLog()->error('Debe de completar la descripción.');
                return false;
            } else {
                $sql = ' SELECT nombre '
                     . ' FROM service_valuation_types '
                     . ' WHERE idservice_valuation_type = ' . $this->idservice_valuation_type
                     . ' ORDER BY idservice_valuation_type '
                     ;

                $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

                foreach ($registros as $fila) {
                        $this->nombre = $fila['nombre'];
                }
                return true;
            }
            
        }
        return true;
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
	
}

