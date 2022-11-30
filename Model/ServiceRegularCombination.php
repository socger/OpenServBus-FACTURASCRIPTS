<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class ServiceRegularCombination extends Base\ModelClass
{
    use Base\ModelTrait;

    /** @var bool */
    public $activo;

    /** @var bool */
    public $domingo;

    /** @var int */
    public $driver_alojamiento_1;

    /** @var int */
    public $driver_alojamiento_2;

    /** @var int */
    public $driver_alojamiento_3;

    /** @var string */
    public $driver_observaciones_1;

    /** @var string */
    public $driver_observaciones_2;

    /** @var string */
    public $driver_observaciones_3;

    /** @var string */
    public $fechaalta;

    /** @var string */
    public $fechabaja;

    /** @var string */
    public $fechamodificacion;

    /** @var int */
    public $iddriver_1;

    /** @var int */
    public $iddriver_2;

    /** @var int */
    public $iddriver_3;

    /** @var int */
    public $idservice_regular_combination;

    /** @var int */
    public $idvehicle;

    /** @var bool */
    public $jueves;

    /** @var bool */
    public $lunes;

    /** @var bool */
    public $martes;

    /** @var bool */
    public $miercoles;

    /** @var string */
    public $motivobaja;

    /** @var string */
    public $nombre;

    /** @var string */
    public $observaciones;

    /** @var bool */
    public $sabado;

    /** @var string */
    public $useralta;

    /** @var string */
    public $userbaja;

    /** @var string */
    public $usermodificacion;

    /** @var bool */
    public $viernes;

    public function clear()
    {
        parent::clear();
        $this->activo = true;
        $this->fechaalta = date(static::DATETIME_STYLE);
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public function install(): string
    {
        new Driver();
        new Vehicle();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idservice_regular_combination';
    }

    public static function tableName(): string
    {
        return 'service_regular_combinations';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        if (empty($this->iddriver_1) || empty($this->idvehicle)) {
            $this->toolBox()->i18nLog()->info('Si no rellena el vehículo o el conductor, este será el orden de prioridades para el Montaje de Servicios:'
                . ' 1º Combinación - Servicio Regular, 2º Combinación y 3º Servicio Regular');
        }

        if ($this->hayServiciosQueNoCoincidenLosDiasDeSemana() === true) {
            return false;
        }

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
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'ListServiceRegular'): string
    {
        return parent::url($type, $list . '?activetab=List');
    }

    protected function comprobarSiActivo()
    {
        $a_devolver = true;
        if ($this->activo === false) {
            $this->fechabaja = $this->fechamodificacion;
            $this->userbaja = $this->usermodificacion;

            if (empty($this->motivobaja)) {
                $a_devolver = false;
                $this->toolBox()->i18nLog()->error('Si el registro no está activo, debe especificar el motivo.');
            }
        } else {
            // Por si se vuelve a poner Activo = true
            $this->fechabaja = null;
            $this->userbaja = null;
            $this->motivobaja = null;
        }
        return $a_devolver;
    }

    protected function hayServiciosQueNoCoincidenLosDiasDeSemana(): bool
    {
        $serviciosConDiasDiferentes = [];

        $sql = ' SELECT service_regulars.lunes '
            . ' , service_regulars.martes '
            . ' , service_regulars.miercoles '
            . ' , service_regulars.jueves '
            . ' , service_regulars.viernes '
            . ' , service_regulars.sabado '
            . ' , service_regulars.domingo '
            . ' , service_regulars.idservice_regular '
            . ' , service_regulars.nombre '
            . ' FROM service_regular_combination_servs '
            . ' LEFT JOIN service_regulars on (service_regulars.idservice_regular = service_regular_combination_servs.idservice_regular) '
            . ' WHERE service_regular_combination_servs.idservice_regular_combination = ' . $this->idservice_regular_combination;

        $registros = self::$dataBase->select($sql);

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
        }

        foreach ($serviciosConDiasDiferentes as $servicio) {
            $this->toolBox()->i18nLog()->error("Los días de la semana del servicio $servicio no coinciden con los días de la semana de esta combinación.");
        }
        return true;
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}