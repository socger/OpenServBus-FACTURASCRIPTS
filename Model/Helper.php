<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class Helper extends Base\ModelClass
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

    /** @var int */
    public $idcollaborator;

    /** @var int */
    public $idemployee;

    /** @var int */
    public $idhelper;

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

    public function install(): string
    {
        new Employee();
        new Collaborator();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idhelper';
    }

    public static function tableName(): string
    {
        return 'helpers';
    }

    public function test(): bool
    {
        // Exigimos que se introduzca idempresa o idcollaborator
        if ((empty($this->idemployee)) && (empty($this->idcollaborator))) {
            $this->toolBox()->i18nLog()->error('Debe de confirmar si es un empleado nuestro o de una empresa colaboradora');
            return false;
        }

        // No debe de elegir empleado y colaborador a la vez
        if ((!empty($this->idemployee)) && (!empty($this->idcollaborator))) {
            $this->toolBox()->i18nLog()->error('O es un empleado nuestro o de una empresa colaboradora, pero ambos no');
            return false;
        }

        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        return parent::test();
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
