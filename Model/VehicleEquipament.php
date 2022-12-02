<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class VehicleEquipament extends Base\ModelClass
{
    use Base\ModelTrait;
    use OpenServBusModelTrait;

    public $activo;

    public $fechaalta;

    public $fechabaja;

    public $fechamodificacion;

    public $idvehicle;

    public $idvehicle_equipament;

    public $idvehicle_equipament_type;

    public $motivobaja;

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

    public function getVehicle(): Vehicle
    {
        $vehicle = new Vehicle();
        $vehicle->loadFromCode($this->idvehicle);
        return $vehicle;
    }

    public function install(): string
    {
        new Vehicle();
        new VehicleEquipamentType();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idvehicle_equipament';
    }

    public static function tableName(): string
    {
        return 'vehicle_equipaments';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'ListVehicle'): string
    {
        return parent::url($type, $list . '?activetab=List');
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}