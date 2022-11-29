<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class Tarjeta extends Base\ModelClass
{
    use Base\ModelTrait;

    /** @var bool */
    public $activo;

    /** @var bool */
    public $de_pago;

    /** @var string */
    public $fechaalta;

    /** @var string */
    public $fechabaja;

    /** @var string */
    public $fechamodificacion;

    /** @var int */
    public $idemployee;

    /** @var int */
    public $idempresa;

    /** @var int */
    public $iddriver;

    /** @var int */
    public $idtarjeta;

    /** @var int */
    public $idtarjeta_type;

    /** @var string */
    public $motivobaja;

    /** @var string */
    public $nombre;

    /** @var string */
    public $observaciones;

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
        $this->de_pago = false;
        $this->fechaalta = date(static::DATETIME_STYLE);
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public function getTarjetaType(): TarjetaType
    {
        $tarjetaType = new TarjetaType();
        $tarjetaType->loadFromCode($this->idtarjeta_type);
        return $tarjetaType;
    }

    public function install(): string
    {
        new Driver();
        new Employee();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idtarjeta';
    }

    public static function tableName(): string
    {
        return 'tarjetas';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        if ((empty($this->idemployee)) && (empty($this->iddriver))) {
            $this->toolBox()->i18nLog()->error('Debe de confirmar si la tarjeta es de un empleado o de un conductor.');
            return false;
        }

        if ((!empty($this->idemployee)) && (!empty($this->iddriver))) {
            $this->toolBox()->i18nLog()->error('La tarjeta o es de un empleado o es de un conductor, pero no de ambos.');
            return false;
        }

        $this->comprobarEmpresa();

        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->nombre = $utils->noHtml($this->nombre);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        $this->de_pago = $this->getTarjetaType()->de_pago;
        return parent::test();
    }

    protected function comprobarEmpresa()
    {
        if (!empty($this->idemployee)) {
            $sql = ' SELECT employees.idempresa '
                . ' , empresas.nombrecorto '
                . ' FROM employees '
                . ' LEFT JOIN empresas ON (empresas.idempresa = employees.idempresa) '
                . ' WHERE employees.idemployee = ' . $this->idemployee;
        } else {
            $sql = ' SELECT employees.idempresa '
                . ' , empresas.nombrecorto '
                . ' FROM drivers '
                . ' LEFT JOIN employees ON (employees.idemployee = drivers.idemployee) '
                . ' LEFT JOIN empresas ON (empresas.idempresa = employees.idempresa) '
                . ' WHERE drivers.iddriver = ' . $this->iddriver;
        }
        $registros = self::$dataBase->select($sql);

        foreach ($registros as $fila) {
            $idempresa = $fila['idempresa'];
            $nombreEmpresa = $fila['nombrecorto'];
        }

        if (!empty($this->idempresa)) {
            if (!empty($idempresa)) {
                if ($idempresa <> $this->idempresa) {
                    $this->toolBox()->i18nLog()->info('Pero para su información ... la empresa del conductor/empleado ("' . $nombreEmpresa . '") no es la misma que la empresa elegida para esta tarjeta.');
                }
            }
        }
    }

    protected function comprobarSiActivo(): bool
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

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}