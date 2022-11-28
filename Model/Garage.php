<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class Garage extends Base\ModelClass
{
    use Base\ModelTrait;

    /** @var bool */
    public $activo;

    /** @var string */
    public $apartado;

    /** @var string */
    public $ciudad;

    /** @var string */
    public $codpais;

    /** @var string */
    public $codpostal;

    /** @var string */
    public $direccion;

    /** @var string */
    public $email;

    /** @var string */
    public $fax;

    /** @var string */
    public $fechaalta;

    /** @var string */
    public $fechabaja;

    /** @var string */
    public $fechamodificacion;

    /** @var int */
    public $idempresa;

    /** @var int */
    public $idgarage;

    /** @var string */
    public $motivobaja;

    /** @var string */
    public $nombre;

    /** @var string */
    public $observaciones;

    /** @var string */
    public $provincia;

    /** @var string */
    public $telefono1;

    /** @var string */
    public $telefono2;

    /** @var string */
    public $useralta;

    /** @var string */
    public $userbaja;

    /** @var string */
    public $usermodificacion;

    /** @var string */
    public $web;

    public function clear()
    {
        parent::clear();
        $this->activo = true;
        $this->codpais = $this->toolBox()->appSettings()->get('default', 'codpais');
        $this->fechaalta = date(static::DATETIME_STYLE);
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public static function primaryColumn(): string
    {
        return 'idgarage';
    }

    public static function tableName(): string
    {
        return 'garages';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        if (empty($this->idempresa)) {
            $this->idempresa = $this->toolBox()->appSettings()->get('default', 'idempresa');
        }

        $utils = $this->toolBox()->utils();
        $this->nombre = $utils->noHtml($this->nombre);
        $this->ciudad = $utils->noHtml($this->ciudad);
        $this->provincia = $utils->noHtml($this->provincia);
        $this->codpais = $utils->noHtml($this->codpais);
        $this->codpostal = $utils->noHtml($this->codpostal);
        $this->apartado = $utils->noHtml($this->apartado);
        $this->direccion = $utils->noHtml($this->direccion);
        $this->telefono1 = $utils->noHtml($this->telefono1);
        $this->telefono2 = $utils->noHtml($this->telefono2);
        $this->fax = $utils->noHtml($this->fax);
        $this->email = $utils->noHtml($this->email);
        $this->web = $utils->noHtml($this->web);
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'ListHelper'): string
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
        } else { // Por si se vuelve a poner Activo = true
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