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

class Tarjeta extends Base\ModelClass
{
    use Base\ModelTrait;
    use OpenServBusModelTrait;

    /** @var bool */
    public $activo;

    /** @var bool */
    public $de_pago;

    /** @var string */
    public $fechaalta;

    /** @var string */
    public $fechabaja;

    /** @var string */
    public $fechamodificacion;

    /** @var int */
    public $idemployee;

    /** @var int */
    public $idempresa;

    /** @var int */
    public $iddriver;

    /** @var int */
    public $idtarjeta;

    /** @var int */
    public $idtarjeta_type;

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

    public function __get(string $name)
    {
        if ($name === 'es_DePago') {
            $type = $this->getTarjetaType();
            return (bool)$type->de_pago;
        }
        return null;
    }

    public function clear()
    {
        parent::clear();
        $this->activo = true;
        $this->de_pago = false;
        $this->fechaalta = date(static::DATETIME_STYLE);
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public function getTarjetaType(): TarjetaType
    {
        $tarjetaType = new TarjetaType();
        $tarjetaType->loadFromCode($this->idtarjeta_type);
        return $tarjetaType;
    }

    public function install(): string
    {
        new Driver();
        new EmployeeOpen();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idtarjeta';
    }

    public static function tableName(): string
    {
        return 'tarjetas';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        if ((empty($this->idemployee)) && (empty($this->iddriver))) {
            $this->toolBox()->i18nLog()->error('confirm-card-is-employee-or-driver');
            return false;
        }

        if ((!empty($this->idemployee)) && (!empty($this->iddriver))) {
            $this->toolBox()->i18nLog()->error('the-card-is-employee-or-driver-bat-not-both');
            return false;
        }

        $this->comprobarEmpresa();

        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->nombre = $utils->noHtml($this->nombre);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        $this->de_pago = $this->getTarjetaType()->de_pago;
        return parent::test();
    }

    protected function comprobarEmpresa()
    {
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
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}