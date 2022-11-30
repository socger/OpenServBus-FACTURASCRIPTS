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

    public function delete(): bool
    {
        if (false === parent::delete()) {
            return false;
        }

        // Se pasa valor 1, en parámetro, porque se está borrando el registro
        $this->actualizar_driverYN_en_employees(true);
        return true;
    }

    public function install(): string
    {
        new Employee();
        new Collaborator();
        return parent::install();
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
        // Exigimos que se introduzca idempresa o idcollaborator
        if ((empty($this->idemployee)) && (empty($this->idcollaborator))) {
            $this->toolBox()->i18nLog()->error('Debe de confirmar si es un empleado nuestro o de una empresa colaboradora');
            return false;
        }

        // No debe de elegir empleado y colaborador a la vez
        if ((!empty($this->idemployee)) and (!empty($this->idcollaborator))) {
            $this->toolBox()->i18nLog()->error('O es un empleado nuestro o de una empresa colaboradora, pero ambos no');
            return false;
        }

        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        // Se pasa como parámetro 0 para decir que no se está borrando el empleado
        $this->actualizar_driverYN_en_employees(false);

        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        return parent::test();
    }

    protected function actualizar_driverYN_en_employees(bool $p_borrando)
    {
        // Completamos el campo driver_yn de la tabla employee
        if ($p_borrando) {
            // Se está borrando el registro
            if (!empty($this->idemployee)) {
                $sql = "UPDATE employees SET employees.driver_yn = 0 WHERE employees.idemployee = " . $this->idemployee . ";";
            }
        } else {
            // Se está creando/editando el registro
            if (!empty($this->idemployee)) {
                // Si al crear/modificar el registro es un empleado
                $sql = "UPDATE employees SET employees.driver_yn = 1 WHERE employees.idemployee = " . $this->idemployee . ";";
            }
        }

        self::$dataBase->exec($sql);
    }

    protected function getCollaborator(): Collaborator
    {
        $collaborator = new Collaborator();
        $collaborator->loadFromCode($this->idcollaborator);
        return $collaborator;
    }

    protected function getEmployee(): Employee
    {
        $employee = new Employee();
        $employee->loadFromCode($this->idemployee);
        return $employee;
    }

    protected function saveInsert(array $values = []): bool
    {
        if (false === parent::saveInsert($values)) {
            return false;
        }

        $this->getEmployee()->actualizarNombreEmpleadoEn();
        $this->getCollaborator()->actualizarNombreColaboradorEn();
        return true;
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}
