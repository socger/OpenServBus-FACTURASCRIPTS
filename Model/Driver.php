<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class Driver extends Base\ModelClass
{
    use Base\ModelTrait;
    use OpenServBusModelTrait;

    public $activo;

    public $fechaalta;

    public $fechabaja;

    public $fechamodificacion;

    public $idcollaborator;

    public $iddriver;

    public $idemployee;

    public $motivobaja;

    public $observaciones;

    public $useralta;

    public $userbaja;

    public $usermodificacion;

    public function __get($name)
    {
        if ($name === 'nombre') {
            if (false === empty($this->idcollaborator)) {
                $collaborator = $this->getCollaborator();
                return $collaborator->getProveedor()->nombre;
            } elseif (false === empty($this->idemployee)) {
                $employee = $this->getEmployee();
                return $employee->nombre;
            }
        }
        return null;
    }

    public function clear()
    {
        parent::clear();
        $this->activo = true;
        $this->fechaalta = date(static::DATETIME_STYLE);
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public function delete(): bool
    {
        if (false === parent::delete()) {
            return false;
        }

        $empleado = $this->getEmployee();
        if ($empleado->exists()) {
            $empleado->driver_yn = 0;
            $empleado->save();
        }

        return true;
    }

    public function getCollaborator(): Collaborator
    {
        $collaborator = new Collaborator();
        $collaborator->loadFromCode($this->idcollaborator);
        return $collaborator;
    }

    public function getEmployee(): Employee
    {
        $employee = new Employee();
        $employee->loadFromCode($this->idemployee);
        return $employee;
    }

    public function install(): string
    {
        new Employee();
        new Collaborator();
        return parent::install();
    }

    public function save(): bool
    {
        if (false === parent::save()) {
            return false;
        }

        $empleado = $this->getEmployee();
        if ($empleado->exists()) {
            $empleado->driver_yn = 1;
            $empleado->save();
        }

        return true;
    }

    public static function primaryColumn(): string
    {
        return 'iddriver';
    }

    public static function tableName(): string
    {
        return 'drivers';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        // Exigimos que se introduzca idempresa o idcollaborator
        if ((empty($this->idemployee)) && (empty($this->idcollaborator))) {
            $this->toolBox()->i18nLog()->error('confirm-employee-or-collaborating');
            return false;
        }

        // No debe de elegir empleado y colaborador a la vez
        if ((!empty($this->idemployee)) and (!empty($this->idcollaborator))) {
            $this->toolBox()->i18nLog()->error('employee-or-collaborating-bat-not-both');
            return false;
        }

        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
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