<?php
/**
 * This file is part of OpenServBus plugin for FacturaScripts
 * Copyright (C) 2021-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 * Copyright (C) 2021 Jerónimo Pedro Sánchez Manzano <socger@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class FuelKm extends Base\ModelClass
{
    use Base\ModelTrait;
    use OpenServBusModelTrait;

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
        new EmployeeOpen();
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
            $sql = ' SELECT employees_open.idempresa '
                . ' , empresas.nombrecorto '
                . ' FROM employees_open '
                . ' LEFT JOIN empresas ON (empresas.idempresa = employees_open.idempresa) '
                . ' WHERE employees_open.idemployee = ' . $this->idemployee;
        } else {
            $sql = ' SELECT employees_open.idempresa '
                . ' , empresas.nombrecorto '
                . ' FROM drivers '
                . ' LEFT JOIN employees_open ON (employees_open.idemployee = drivers.idemployee) '
                . ' LEFT JOIN empresas ON (empresas.idempresa = employees_open.idempresa) '
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
                    $this->toolBox()->i18nLog()->info('company-not-equals-company-of-driver', ['%company%' => $nombreEmpresa]);
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

            $registros = self::$dataBase->select($sql);

            foreach ($registros as $fila) {
                $idempresa = $fila['idempresa'];
                $nombreEmpresa = $fila['nombrecorto'];
            }

            if (!empty($this->idempresa)) {
                if (!empty($idempresa)) {
                    if ($idempresa <> $this->idempresa) {
                        $this->toolBox()->i18nLog()->info('company-not-equals-company-of-vehicle', ['%company%' => $nombreEmpresa]);
                    }
                }
            }
        }
    }

    protected function comprobar_Empleado_Conductor(): bool
    {
        // Exigimos que se introduzca iddriver o idemployee
        if ((empty($this->iddriver)) && (empty($this->idemployee))) {
            $this->toolBox()->i18nLog()->error('confirm-refueling-done-employee-or-driver');
            return false;
        }

        if ((!empty($this->iddriver)) && (!empty($this->idemployee))) {
            $this->toolBox()->i18nLog()->error('refueling-has-employee-or-driver-bat-not-both');
            return false;
        }

        return true;
    }

    protected function comprobar_Surtidor_Proveedor(): bool
    {
        // Exigimos que se introduzca idempresa o idcollaborator
        if ((empty($this->idfuel_pump)) && (empty($this->codproveedor))) {
            $this->toolBox()->i18nLog()->error('confirm-internal-or-external-refueling');
            return false;
        }

        if ((!empty($this->idfuel_pump)) && (!empty($this->codproveedor))) {
            $this->toolBox()->i18nLog()->error('internal-or-external-refueling-bat-not-both');
            return false;
        }

        return true;
    }

    private function comprobar_Tarjeta__Identificacion_mean(): bool
    {
        // Exigimos que se introduzca idtarjeta o ididentification_mean
        if ((empty($this->idtarjeta)) && (empty($this->ididentification_mean))) {
            $this->toolBox()->i18nLog()->error('confirm-card-used-this-refueling');
            return false;
        }

        if ((!empty($this->idtarjeta)) && (!empty($this->ididentification_mean))) {
            $this->toolBox()->i18nLog()->error('refueling-use-card-or-identification-bat-not-both');
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