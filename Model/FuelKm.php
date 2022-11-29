<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class FuelKm extends Base\ModelClass
{
    use Base\ModelTrait;

    /** @var bool */
    public $activo;

    /** @var string */
    public $codproveedor;

    /** @var bool */
    public $deposito_lleno;

    /** @var string */
    public $fecha;

    /** @var string */
    public $fechaalta;

    /** @var string */
    public $fechabaja;

    /** @var string */
    public $fechamodificacion;

    /** @var int */
    public $ididentification_mean;

    /** @var int */
    public $iddriver;

    /** @var int */
    public $idemployee;

    /** @var int */
    public $idempresa;

    /** @var int */
    public $idfuel_km;

    /** @var int */
    public $idfuel_pump;

    /** @var int */
    public $idfuel_type;

    /** @var int */
    public $idtarjeta;

    /** @var int */
    public $idvehicle;

    /** @var int */
    public $km;

    /** @var int */
    public $litros;

    /** @var string */
    public $motivobaja;

    /** @var string */
    public $observaciones;

    /** @var float */
    public $pvp_litro;

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
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public function install(): string
    {
        new Vehicle();
        new Driver();
        new Employee();
        new FuelType();
        new FuelPump();
        new Tarjeta();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idfuel_km';
    }

    public static function tableName(): string
    {
        return 'fuel_kms';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        if ($this->comprobar_Surtidor_Proveedor() === false) {
            return false;
        }

        if ($this->comprobar_Empleado_Conductor() === false) {
            return false;
        }

        if ($this->comprobar_Tarjeta__Identificacion_mean() === false) {
            return false;
        }

        $this->comprobarEmpresa();

        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        return parent::test();
    }

    protected function comprobarEmpresa()
    {
        // Comprobamos la empresa del empleado o del conductor
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

        // Ahora comprobamos la empresa del vehículo
        if (!empty($this->idvehicle)) {
            $sql = ' SELECT vehicles.idempresa '
                . ' , empresas.nombrecorto '
                . ' FROM vehicles '
                . ' LEFT JOIN empresas ON (empresas.idempresa = vehicles.idempresa) '
                . ' WHERE vehicles.idvehicle = ' . $this->idvehicle;

            $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

            foreach ($registros as $fila) {
                $idempresa = $fila['idempresa'];
                $nombreEmpresa = $fila['nombrecorto'];
            }

            if (!empty($this->idempresa)) {
                if (!empty($idempresa)) {
                    if ($idempresa <> $this->idempresa) {
                        $this->toolBox()->i18nLog()->info('Pero para su información ... la empresa del vehículo ("' . $nombreEmpresa . '") no es la misma que la empresa elegida para esta tarjeta.');
                    }
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
        } else { // Por si se vuelve a poner Activo = true
            $this->fechabaja = null;
            $this->userbaja = null;
            $this->motivobaja = null;
        }
        return $a_devolver;
    }

    protected function comprobar_Empleado_Conductor(): bool
    {
        // Exigimos que se introduzca iddriver o idemployee
        if ((empty($this->iddriver)) && (empty($this->idemployee))) {
            $this->toolBox()->i18nLog()->error('Debe de confirmar si el repostaje lo ha hecho un empleado o un conductor.');
            return false;
        }

        if ((!empty($this->iddriver)) && (!empty($this->idemployee))) {
            $this->toolBox()->i18nLog()->error('El repostaje o lo ha hecho un empleado o lo ha hecho un conductor, pero no de ambos.');
            return false;
        }

        return true;
    }

    protected function comprobar_Surtidor_Proveedor(): bool
    {
        // Exigimos que se introduzca idempresa o idcollaborator
        if ((empty($this->idfuel_pump)) && (empty($this->codproveedor))) {
            $this->toolBox()->i18nLog()->error('Debe de confirmar si es un repostaje interno o externo.');
            return false;
        }

        if ((!empty($this->idfuel_pump)) && (!empty($this->codproveedor))) {
            $this->toolBox()->i18nLog()->error('El repostaje o es interno o externo, pero no de ambos.');
            return false;
        }

        return true;
    }

    private function comprobar_Tarjeta__Identificacion_mean(): bool
    {
        // Exigimos que se introduzca idtarjeta o ididentification_mean
        if ((empty($this->idtarjeta)) && (empty($this->ididentification_mean))) {
            $this->toolBox()->i18nLog()->error('Debe de confirmar que tarjeta ó que Medio de Identificación ha usado para este repostaje.');
            return false;
        }

        if ((!empty($this->idtarjeta)) && (!empty($this->ididentification_mean))) {
            $this->toolBox()->i18nLog()->error('El repostaje o lo ha hecho con el uso de una tarjeta o lo ha hecho con un Medio de Identifiación, pero no de ambos.');
            return false;
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