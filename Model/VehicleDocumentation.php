<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class VehicleDocumentation extends Base\ModelClass
{
    use Base\ModelTrait;

    /** @var bool */
    public $activo;

    /** @var string */
    public $fechaalta;

    /** @var string */
    public $fechabaja;

    /** @var string */
    public $fechamodificacion;

    /** @var string */
    public $fecha_caducidad;

    /** @var int */
    public $iddocumentation_type;

    /** @var int */
    public $idvehicle;

    /** @var int */
    public $idvehicle_documentation;

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
        $this->fechaalta = date(static::DATETIME_STYLE);
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public function install()
    {
        new Vehicle();
        new DocumentationType();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idvehicle_documentation';
    }

    public static function tableName(): string
    {
        return 'vehicle_documentations';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        if (empty($this->fecha_caducidad)) {
            if ($this->ComprobarSiEsObligadaFechaCaducidad() == 1) {
                $this->toolBox()->i18nLog()->error('Para el tipo de documento elegido, necesitamos rellenar la fecha de caducidad');
                return false;
            }
        }

        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->nombre = $utils->noHtml($this->nombre);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        return parent::test();
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

    protected function ComprobarSiEsObligadaFechaCaducidad()
    {
        $sql = ' SELECT fechacaducidad_obligarla '
            . ' FROM documentation_types '
            . ' WHERE iddocumentation_type = ' . $this->iddocumentation_type . " ";
        $registros = self::$dataBase->select($sql);
        foreach ($registros as $fila) {
            return $fila['fechacaducidad_obligarla'];
        }
        return false;
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}