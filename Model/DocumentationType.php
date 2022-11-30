<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class DocumentationType extends Base\ModelClass
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
    public $fechacaducidad_obligarla;

    /** @var int */
    public $iddocumentation_type;

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
        $this->fechacaducidad_obligarla = true;
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public static function primaryColumn(): string
    {
        return 'iddocumentation_type';
    }

    public static function tableName(): string
    {
        return 'documentation_types';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        $utils = $this->toolBox()->utils();
        $this->nombre = $utils->noHtml($this->nombre);
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'ConfigOpenServBus'): string
    {
        return parent::url($type, $list . '?activetab=List');
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