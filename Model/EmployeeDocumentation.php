<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class EmployeeDocumentation extends Base\ModelClass
{
    use Base\ModelTrait;

    public $activo;

    public $fechaalta;

    public $fechabaja;

    public $fechamodificacion;

    public $fecha_caducidad;

    public $iddocumentation_type;

    public $idemployee;

    public $idemployee_documentation;

    public $motivobaja;

    public $nombre;

    public $observaciones;

    public $useralta;

    public $userbaja;

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

    public function install(): string
    {
        new Employee();
        new DocumentationType();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idemployee_documentation';
    }

    public static function tableName(): string
    {
        return 'employee_documentations';
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

    public function url(string $type = 'auto', string $list = 'ListVehicleDocumentation'): string
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