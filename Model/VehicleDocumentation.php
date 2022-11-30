<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class VehicleDocumentation extends Base\ModelClass
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

    public function getDocumentarioType(): DocumentationType
    {
        $documentation_type = new DocumentationType();
        $documentation_type->loadFromCode($this->iddocumentation_type);
        return $documentation_type;
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
            if ($this->getDocumentarioType()->fechacaducidad_obligarla) {
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

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}