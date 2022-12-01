<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class ServiceRegularPeriod extends Base\ModelClass
{
    use Base\ModelTrait;
    use OpenServBusModelTrait;

    /** @var bool */
    public $activo;

    /** @var string */
    public $fechaalta;

    /** @var string */
    public $fechabaja;

    /** @var string */
    public $fechamodificacion;

    /** @var string */
    public $fecha_desde;

    /** @var string */
    public $fecha_hasta;

    /** @var string */
    public $hora_anticipacion;

    /** @var string */
    public $hora_desde;

    /** @var string */
    public $hora_hasta;

    /** @var int */
    public $idservice_regular;

    /** @var int */
    public $idservice_regular_period;

    /** @var string */
    public $motivobaja;

    /** @var string */
    public $observaciones;

    /** @var bool */
    public $salida_desde_nave_sn;

    /** @var string */
    public $useralta;

    /** @var string */
    public $userbaja;

    /** @var string */
    public $usermodificacion;

    public function clear()
    {
        parent::clear();
        $this->activo = true;
        $this->fechaalta = date(static::DATETIME_STYLE);
        $this->salida_desde_nave_sn = false;
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public function delete(): bool
    {
        if (false === parent::delete()) {
            return false;
        }

        $this->actualizarPeriodoEnServicioRegular();
        return true;
    }

    public function getServicioRegular(): ServiceRegular
    {
        $servicioRegular = new ServiceRegular();
        $servicioRegular->loadFromCode($this->idservice_regular);
        return $servicioRegular;
    }

    public function install(): string
    {
        new ServiceRegular();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idservice_regular_period';
    }

    public function save(): bool
    {
        if (false === parent::save()) {
            return false;
        }

        $this->actualizarPeriodoEnServicioRegular();
        return true;
    }

    public static function tableName(): string
    {
        return 'service_regular_periods';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        // La fecha de inicio es obligatoria
        if (empty($this->fecha_desde)) {
            $this->toolBox()->i18nLog()->error('date-start-is-required');
            return false;
        }

        // Si fecha hasta está introducida y fecha desde no está vacía y además es mayor que fecha hasta ... fallo
        if (!empty($this->fecha_hasta)) {
            if (!empty($this->fecha_desde) and
                $this->fecha_desde > $this->fecha_hasta) {
                $this->toolBox()->i18nLog()->error('date-start-not-greater-date-end');
                return false;
            }
        }

        // La hora de inicio es obligatoria
        if (empty($this->hora_desde)) {
            $this->toolBox()->i18nLog()->error('hour-start-is-required');
            return false;
        }

        // La hora de fin es obligatoria
        if (empty($this->hora_hasta)) {
            $this->toolBox()->i18nLog()->error('hour-end-is-required');
            return false;
        }

        // Si fecha hasta está introducida y fecha desde no está vacía y además es mayor que fecha hasta ... fallo
        if (!empty($this->hora_hasta)) {
            if (!empty($this->hora_desde) &&
                $this->hora_desde > $this->hora_hasta) {
                $this->toolBox()->i18nLog()->error('hour-start-not-greater-hour-end.');
                return false;
            }
        }

        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'ListServiceRegular'): string
    {
        return parent::url($type, $list . '?activetab=List');
    }

    protected function actualizarPeriodoEnServicioRegular()
    {
        $sql = ' SELECT service_regular_periods.idservice_regular_period '
            . ' , service_regular_periods.fecha_desde '
            . ' , service_regular_periods.fecha_hasta '
            . ' , service_regular_periods.hora_anticipacion '
            . ' , service_regular_periods.hora_desde '
            . ' , service_regular_periods.hora_hasta '
            . ' , service_regular_periods.salida_desde_nave_sn '
            . ' , service_regular_periods.observaciones '
            . ' FROM service_regular_periods '
            . ' WHERE service_regular_periods.idservice_regular = ' . $this->idservice_regular . ' '
            . ' AND service_regular_periods.activo = 1 '
            . ' ORDER BY service_regular_periods.fecha_desde DESC '
            . ' , service_regular_periods.fecha_hasta DESC '
            . ' , service_regular_periods.hora_desde DESC '
            . ' , service_regular_periods.hora_hasta DESC '
            . ' , idservice_regular '
            . ' LIMIT 1 ';

        $idservice_regular_period = null;
        $fecha_desde = null;
        $fecha_hasta = null;
        $hora_anticipacion = null;
        $hora_desde = null;
        $hora_hasta = null;
        $salida_desde_nave_sn = null;
        $observaciones_periodo = null;

        $registros = self::$dataBase->select($sql);

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
            . ", service_regulars.fecha_desde = '" . $fecha_desde . "' "
            . ", service_regulars.fecha_hasta = '" . $fecha_hasta . "' "
            . ", service_regulars.hora_anticipacion = '" . $hora_anticipacion . "' "
            . ", service_regulars.hora_desde = '" . $hora_desde . "' "
            . ", service_regulars.hora_hasta = '" . $hora_hasta . "' "
            . ", service_regulars.salida_desde_nave_sn = " . $salida_desde_nave_sn . " "
            . ", service_regulars.idservice_regular_period = " . $idservice_regular_period . " "
            . "WHERE service_regulars.idservice_regular = " . $this->idservice_regular . ";";

        self::$dataBase->exec($sql);
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}