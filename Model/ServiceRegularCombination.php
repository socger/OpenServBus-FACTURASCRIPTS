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

class ServiceRegularCombination extends Base\ModelClass
{
    use Base\ModelTrait;
    use OpenServBusModelTrait;

    /** @var bool */
    public $activo;

    /** @var bool */
    public $domingo;

    /** @var int */
    public $driver_alojamiento_1;

    /** @var int */
    public $driver_alojamiento_2;

    /** @var int */
    public $driver_alojamiento_3;

    /** @var string */
    public $driver_observaciones_1;

    /** @var string */
    public $driver_observaciones_2;

    /** @var string */
    public $driver_observaciones_3;

    /** @var string */
    public $fechaalta;

    /** @var string */
    public $fechabaja;

    /** @var string */
    public $fechamodificacion;

    /** @var int */
    public $iddriver_1;

    /** @var int */
    public $iddriver_2;

    /** @var int */
    public $iddriver_3;

    /** @var int */
    public $idservice_regular_combination;

    /** @var int */
    public $idvehicle;

    /** @var bool */
    public $jueves;

    /** @var bool */
    public $lunes;

    /** @var bool */
    public $martes;

    /** @var bool */
    public $miercoles;

    /** @var string */
    public $motivobaja;

    /** @var string */
    public $nombre;

    /** @var string */
    public $observaciones;

    /** @var bool */
    public $sabado;

    /** @var string */
    public $useralta;

    /** @var string */
    public $userbaja;

    /** @var string */
    public $usermodificacion;

    /** @var bool */
    public $viernes;

    public function clear()
    {
        parent::clear();
        $this->activo = true;
        $this->fechaalta = date(static::DATETIME_STYLE);
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public function install(): string
    {
        new Driver();
        new Vehicle();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idservice_regular_combination';
    }

    public static function tableName(): string
    {
        return 'service_regular_combinations';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        if (empty($this->iddriver_1) || empty($this->idvehicle)) {
            $this->toolBox()->i18nLog()->info('service-default-priority');
        }

        if ($this->hayServiciosQueNoCoincidenLosDiasDeSemana() === true) {
            return false;
        }

        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->nombre = $utils->noHtml($this->nombre);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        $this->driver_alojamiento_1 = $utils->noHtml($this->driver_alojamiento_1);
        $this->driver_observaciones_1 = $utils->noHtml($this->driver_observaciones_1);
        $this->driver_alojamiento_2 = $utils->noHtml($this->driver_alojamiento_2);
        $this->driver_observaciones_2 = $utils->noHtml($this->driver_observaciones_2);
        $this->driver_alojamiento_3 = $utils->noHtml($this->driver_alojamiento_3);
        $this->driver_observaciones_3 = $utils->noHtml($this->driver_observaciones_3);
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'ListServiceRegular'): string
    {
        return parent::url($type, $list . '?activetab=List');
    }

    protected function hayServiciosQueNoCoincidenLosDiasDeSemana(): bool
    {
        $serviciosConDiasDiferentes = [];

        $sql = ' SELECT service_regulars.lunes '
            . ' , service_regulars.martes '
            . ' , service_regulars.miercoles '
            . ' , service_regulars.jueves '
            . ' , service_regulars.viernes '
            . ' , service_regulars.sabado '
            . ' , service_regulars.domingo '
            . ' , service_regulars.idservice_regular '
            . ' , service_regulars.nombre '
            . ' FROM service_regular_combination_servs '
            . ' LEFT JOIN service_regulars on (service_regulars.idservice_regular = service_regular_combination_servs.idservice_regular) '
            . ' WHERE service_regular_combination_servs.idservice_regular_combination = ' . $this->idservice_regular_combination;

        $registros = self::$dataBase->select($sql);

        foreach ($registros as $fila) {
            $coincideAlgunDia = false;

            // Una combinación puede tener varios servicios regulares, por lo
            // que tengo que comprobar todos sus servicios
            if ($this->lunes == 1) {
                if ($this->lunes == $fila['lunes']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->martes == 1) {
                if ($this->martes == $fila['martes']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->miercoles == 1) {
                if ($this->miercoles == $fila['miercoles']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->jueves == 1) {
                if ($this->jueves == $fila['jueves']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->viernes == 1) {
                if ($this->viernes == $fila['viernes']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->sabado == 1) {
                if ($this->sabado == $fila['sabado']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->domingo == 1) {
                if ($this->domingo == $fila['domingo']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($coincideAlgunDia === false) {
                $serviciosConDiasDiferentes[] = $fila['nombre'];
            }
        }

        if (empty($serviciosConDiasDiferentes)) {
            return false;
        }

        foreach ($serviciosConDiasDiferentes as $servicio) {
            $this->toolBox()->i18nLog()->error("days-week-service-not-coincide-with-combination", ['%service%' => $servicio]);
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