<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Base;

class Service_regular_period extends Base\ModelClass {
    use Base\ModelTrait;

    public $idservice_regular_period;
        
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
    public $fecha_desde;
    public $fecha_hasta;

    public $hora_anticipacion;
    public $hora_desde;
    public $hora_hasta;

    public $inicio_horaAnt;
    public $inicio_dia;
    public $inicio_hora;
    public $fin_dia;
    public $fin_hora;
    
    public $salida_desde_nave_sn;
    
    public $observaciones;
    
    // función que inicializa algunos valores antes de la vista del controlador
    public function clear() {
        parent::clear();
        
        $this->activo = true; // Por defecto estará activo
        $this->salida_desde_nave_sn = false;
    }
    
    // función que devuelve el id principal
    public static function primaryColumn(): string {
        return 'idservice_regular_period';
    }
    
    // función que devuelve el nombre de la tabla
    public static function tableName(): string {
        return 'service_regular_periods';
    }

    // Para realizar algo antes o después del borrado ... todo depende de que se ponga antes del parent o después
    public function delete()
    {
        $parent_devuelve = parent::delete();
        
        $this->actualizarPeriodoEnServicioRegular();
                
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

        $returnParent = parent::saveUpdate($values);
        $this->actualizarPeriodoEnServicioRegular();
        return $returnParent;
    }

    // Para realizar cambios en los datos antes de guardar por alta
    protected function saveInsert(array $values = [])
    {
        // Creamos el nuevo id
        if (empty($this->idservice_regular_period)) {
            $this->idservice_regular_period = $this->newCode();
        }

        $this->rellenarDatosAlta();
        $this->rellenarDatosModificacion();
        
        if ($this->comprobarSiActivo() == false){
            return false;
        }

        $returnParent = parent::saveInsert($values);
        $this->actualizarPeriodoEnServicioRegular();
        return $returnParent;
    }
    
    public function test() {
        $this->crearFechaDesde();
        $this->crearFechaHasta();
        
        $this->crearHoraAnticipacion();
        $this->crearHoraDesde();
        $this->crearHoraHasta();

        if ($this->checkFechasPeriodo() == false){
            return false;
        }
        
        if ($this->checkHorasPeriodo() == false){
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

    private function checkFechasPeriodo()
    {
        $a_devolver = true;
        
        // La fecha de inicio es obligatoria
        if (empty($this->fecha_desde)) 
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('La fecha de inicio, debe de introducirla.');
        }

        // Si fecha hasta está introducida y fecha desde no está vacía y además es mayor que fecha hasta ... fallo
        if (!empty($this->fecha_hasta)) 
        {
            if ( !empty($this->fecha_desde) and 
                 $this->fecha_desde > $this->fecha_hasta ) 
            {
                $a_devolver = false;
                $this->toolBox()->i18nLog()->error('La fecha de inicio, no puede ser mayor que la fecha de fin.');
            }
        }
        return $a_devolver;
    }
    
    private function checkHorasPeriodo()
    {
        $a_devolver = true;
        
        // La hora de inicio es obligatoria
        if (empty($this->hora_desde)) 
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('La hora de inicio, debe de introducirla.');
        }

        // La hora de fin es obligatoria
        if (empty($this->hora_hasta)) 
        {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('La hora fin, debe de introducirla.');
        }

        // Si fecha hasta está introducida y fecha desde no está vacía y además es mayor que fecha hasta ... fallo
        if (!empty($this->hora_hasta)) 
        {
            if ( !empty($this->hora_desde) and 
                 $this->hora_desde > $this->hora_hasta ) 
            {
                $a_devolver = false;
                $this->toolBox()->i18nLog()->error('La hora de inicio, no puede ser mayor que la hora de fin.');
            }
        }

        return $a_devolver;
    }
	
    private function evitarInyeccionSQL()
    {
        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
    }

    private function crearFechaDesde()
    {
        $fecha = '';
        if ($this->inicio_dia <> '01-01-1970'){
            $fecha = $fecha . $this->inicio_dia;
        }
        $this->fecha_desde = $fecha;
    }

    private function crearFechaHasta()
    {
        $fecha = '';
        if ($this->fin_dia <> '01-01-1970'){
            $fecha = $fecha . $this->fin_dia;
        }
        $this->fecha_hasta = $fecha;
    }
    
    private function crearHoraAnticipacion()
    {
        $fecha = '';
        if ($this->inicio_dia <> '01-01-1970'){
            $fecha = $fecha . $this->inicio_dia;
        }
        
        if (!empty($this->inicio_horaAnt)){
            $fecha = $fecha . ' ' . $this->inicio_horaAnt;
        }
        $this->hora_anticipacion = $fecha;
    }

    private function crearHoraDesde()
    {
        $fecha = '';
        if ($this->inicio_dia <> '01-01-1970'){
            $fecha = $fecha . $this->inicio_dia;
        }
        
        if (!empty($this->inicio_hora)){
            $fecha = $fecha . ' ' . $this->inicio_hora;
        }
        $this->hora_desde = $fecha;
    }

    private function crearHoraHasta()
    {
        $fecha = '';
        if ($this->inicio_dia <> '01-01-1970'){
            $fecha = $fecha . $this->inicio_dia;
        }
        
        if (!empty($this->fin_hora)){
            $fecha = $fecha . ' ' . $this->fin_hora;
        }
        $this->hora_hasta = $fecha;
    }
        
    private function actualizarPeriodoEnServicioRegular()
    {
        $sql = ' SELECT service_regular_periods.idservice_regular_period '
             .      ' , service_regular_periods.fecha_desde '
             .      ' , service_regular_periods.fecha_hasta '
             .      ' , service_regular_periods.hora_anticipacion '
             .      ' , service_regular_periods.hora_desde '
             .      ' , service_regular_periods.hora_hasta '
             .      ' , service_regular_periods.salida_desde_nave_sn '
             .      ' , service_regular_periods.observaciones '
             . ' FROM service_regular_periods '
             . ' WHERE service_regular_periods.idservice_regular = ' . $this->idservice_regular . ' '
             .   ' AND service_regular_periods.activo = 1 '
             . ' ORDER BY service_regular_periods.fecha_desde DESC '
             .        ' , service_regular_periods.fecha_hasta DESC '
             .        ' , service_regular_periods.hora_desde DESC '
             .        ' , service_regular_periods.hora_hasta DESC '
             .        ' , idservice_regular '
             . ' LIMIT 1 '
             ;

        $idservice_regular_period = null;
        $fecha_desde = null;
        $fecha_hasta = null;
        $hora_anticipacion = null;
        $hora_desde = null;
        $hora_hasta = null;
        $salida_desde_nave_sn = null;
        $observaciones_periodo = null;
        
        $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

        foreach ($registros as $fila) {
            $idservice_regular_period = $fila['idservice_regular_period'];
            $fecha_desde = $fila['fecha_desde'];
            $fecha_hasta = $fila['fecha_hasta'];
            $hora_anticipacion = $fila['hora_anticipacion'];
            $hora_desde = $fila['hora_desde'];
            $hora_hasta = $fila['hora_hasta'];
            $salida_desde_nave_sn = $fila['salida_desde_nave_sn'];
            $observaciones_periodo = $fila['observaciones'];
        }
        
        // Rellenamos el nombre del empleado en otras tablas
        $sql = "UPDATE service_regulars "
             . "SET service_regulars.observaciones_periodo = '" . $observaciones_periodo . "' "
             .   ", service_regulars.fecha_desde = '" . $fecha_desde . "' "
             .   ", service_regulars.fecha_hasta = '" . $fecha_hasta . "' "
             .   ", service_regulars.hora_anticipacion = '" . $hora_anticipacion . "' "
             .   ", service_regulars.hora_desde = '" . $hora_desde . "' "
             .   ", service_regulars.hora_hasta = '" . $hora_hasta . "' "
             .   ", service_regulars.salida_desde_nave_sn = " . $salida_desde_nave_sn . " "
             .   ", service_regulars.idservice_regular_period = " . $idservice_regular_period . " "
             . "WHERE service_regulars.idservice_regular = " . $this->idservice_regular . ";";

        self::$dataBase->exec($sql);
    }

}

