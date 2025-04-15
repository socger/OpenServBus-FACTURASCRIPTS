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

class EmployeeContract extends Base\ModelClass
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
    public $fecha_fin;

    /** @var string */
    public $fecha_inicio;

    /** @var int */
    public $idemployee;

    /** @var int */
    public $idemployee_contract;

    /** @var int */
    public $idemployee_contract_type;

    /** @var int */
    public $idempresa;

    /** @var string */
    public $motivobaja;

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

    public function delete(): bool
    {
        if (false === parent::delete()) {
            return false;
        }

        $this->Actualizar_idempresa_en_employees();
        $this->actualizar_campo_activo_enContratos_del_Empleado();
        return true;
    }

    public function getEmployee(): EmployeeOpen
    {
        $employee = new EmployeeOpen();
        $employee->loadFromCode($this->idemployee);
        return $employee;
    }

    public function install(): string
    {
        new EmployeeOpen();
        new EmployeeContractType();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idemployee_contract';
    }

    public function save(): bool
    {
        if (false === parent::save()) {
            return false;
        }
        $this->Actualizar_idempresa_en_employees();
        $this->actualizar_campo_activo_enContratos_del_Empleado();
        return true;
    }

    public static function tableName(): string
    {
        return 'employee_contracts';
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

    public function url(string $type = 'auto', string $list = 'ListEmployeeOpen'): string
    {
        return parent::url($type, $list . '?activetab=List');
    }

    protected function Actualizar_idempresa_en_employees()
    {
        // Completamos el campo idempresa de la tabla employee
        $sql = " UPDATE employees_open "
            . " SET employees_open.idempresa = ( SELECT IF(employee_contracts.idempresa IS NOT NULL, employee_contracts.idempresa, 0) "
            . " FROM employee_contracts "
            . " WHERE employee_contracts.idemployee = " . $this->idemployee . " "
            . " AND employee_contracts.activo = 1 "
            . " ORDER BY employee_contracts.idemployee "
            . " , employee_contracts.fecha_inicio DESC "
            . " , employee_contracts.fecha_fin DESC "
            . " LIMIT 1 ) "
            . " WHERE employees_open.idemployee = " . $this->idemployee . ";";

        self::$dataBase->exec($sql);
    }

    protected function actualizar_campo_activo_enContratos_del_Empleado()
    {
        // Buscamos el contrato con fecha_inicio + Fecha_fin más alta
        $sql = " SELECT IF(employee_contracts.idemployee_contract IS NOT NULL, employee_contracts.idemployee_contract, 0) AS idemployee_contract "
            . " FROM employee_contracts "
            . " WHERE employee_contracts.idemployee = " . $this->idemployee . " "
            . " ORDER BY employee_contracts.idemployee "
            . " , employee_contracts.fecha_inicio DESC "
            . " , employee_contracts.fecha_fin DESC "
            . " LIMIT 1 ";

        $contratos = self::$dataBase->select($sql);

        if (!empty($contratos)) {
            // Se ha encontrado algún contrato de ese empleado
            foreach ($contratos as $contrato) {
                // Ponemos como activo el encontrado con mayor fecha_inicio + fecha_fin
                $sql = " UPDATE employee_contracts "
                    . " SET employee_contracts.activo = 1 "
                    . " , employee_contracts.usermodificacion = '" . $this->usermodificacion . "' "
                    . " , employee_contracts.fechamodificacion = '" . $this->fechamodificacion . "' "
                    . " , employee_contracts.userbaja = null "
                    . " , employee_contracts.fechabaja = null "
                    . " WHERE employee_contracts.idemployee_contract = " . $contrato['idemployee_contract'] . " ";

                self::$dataBase->exec($sql);

                // Ponemos como no activos el resto
                $sql = " UPDATE employee_contracts "
                    . " SET employee_contracts.activo = 0 "
                    . " , employee_contracts.usermodificacion = '" . $this->usermodificacion . "' "
                    . " , employee_contracts.fechamodificacion = '" . $this->fechamodificacion . "' "
                    . " , employee_contracts.userbaja = '" . $this->userbaja . "' "
                    . " , employee_contracts.fechabaja = '" . $this->fechabaja . "' "

                    . " WHERE employee_contracts.idemployee_contract <> " . $contrato['idemployee_contract'] . " "
                    . " AND employee_contracts.idemployee = " . $this->idemployee . " ";

                self::$dataBase->exec($sql);
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