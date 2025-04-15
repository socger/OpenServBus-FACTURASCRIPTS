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

class ServiceRegularCombinationServ extends Base\ModelClass
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
    public $iddriver;

    /** @var int */
    public $idservice_regular;

    /** @var int */
    public $idservice_regular_combination;

    /** @var int */
    public $idservice_regular_combination_serv;

    /** @var int */
    public $idvehicle;

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

        $this->actualizarCombinadoSNEnServicioRegular();
        return true;
    }

    public function getCombination(): ServiceRegularCombination
    {
        $combination = new ServiceRegularCombination();
        $combination->loadFromCode($this->idservice_regular_combination);
        return $combination;
    }

    public function install(): string
    {
        new Driver();
        new Vehicle();
        new ServiceRegularCombination();
        new ServiceRegular();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idservice_regular_combination_serv';
    }

    public function save(): bool
    {
        if (false === parent::save()) {
            return false;
        }

        $this->actualizarCombinadoSNEnServicioRegular();
        return true;
    }

    public static function tableName(): string
    {
        return 'service_regular_combination_servs';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        if ($this->rellenarConductorVehiculoSiVacios() === false) {
            return false;
        }

        if ($this->comprobarDiasSemana() === false) {
            return false;
        }

        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'ListServiceRegular'): string
    {
        return parent::url($type, $list . '?activetab=List');
    }

    protected function actualizarCombinadoSNEnServicioRegular()
    {
        $sql = ' SELECT COUNT(*) AS cantidad '
            . ' FROM service_regular_combination_servs '
            . ' WHERE service_regular_combination_servs.idservice_regular = ' . $this->idservice_regular . ' '
            . ' AND service_regular_combination_servs.activo = 1 '
            . ' ORDER BY service_regular_combination_servs.idservice_regular ';

        $combinadoSN = 0;

        $registros = self::$dataBase->select($sql);

        foreach ($registros as $fila) {
            if ($fila['cantidad'] > 0) {
                $combinadoSN = 1;
            }
        }

        // Rellenamos el nombre del empleado en otras tablas
        $sql = "UPDATE service_regulars "
            . "SET service_regulars.combinadoSN = " . $combinadoSN . " "
            . "WHERE service_regulars.idservice_regular = " . $this->idservice_regular . ";";

        self::$dataBase->exec($sql);
    }

    protected function comprobarDiasSemana(): bool
    {
        $coincideAlgunDiaDeLaSemana = false;

        $combinacionLunes = false;
        $combinacionMartes = false;
        $combinacionMiercoles = false;
        $combinacionJueves = false;
        $combinacionViernes = false;
        $combinacionSabado = false;
        $combinacionDomingo = false;

        $servRegularLunes = false;
        $servRegularMartes = false;
        $servRegularMiercoles = false;
        $servRegularJueves = false;
        $servRegularViernes = false;
        $servRegularSabado = false;
        $servRegularDomingo = false;

        // Traemos los días de la semana de la combinación
        $sql = ' SELECT service_regular_combinations.lunes '
            . ' , service_regular_combinations.martes '
            . ' , service_regular_combinations.miercoles '
            . ' , service_regular_combinations.jueves '
            . ' , service_regular_combinations.viernes '
            . ' , service_regular_combinations.sabado '
            . ' , service_regular_combinations.domingo '
            . ' FROM service_regular_combinations '
            . ' WHERE service_regular_combinations.idservice_regular_combination = ' . $this->idservice_regular_combination;

        $registros = self::$dataBase->select($sql);

        foreach ($registros as $fila) {
            $combinacionLunes = $fila['lunes'];
            $combinacionMartes = $fila['martes'];
            $combinacionMiercoles = $fila['miercoles'];
            $combinacionJueves = $fila['jueves'];
            $combinacionViernes = $fila['viernes'];
            $combinacionSabado = $fila['sabado'];
            $combinacionDomingo = $fila['domingo'];
        }

        // Traemos los días de la semana del servicio regular
        $sql = ' SELECT service_regulars.lunes '
            . ' , service_regulars.martes '
            . ' , service_regulars.miercoles '
            . ' , service_regulars.jueves '
            . ' , service_regulars.viernes '
            . ' , service_regulars.sabado '
            . ' , service_regulars.domingo '
            . ' FROM service_regulars '
            . ' WHERE service_regulars.idservice_regular = ' . $this->idservice_regular;

        $registros = self::$dataBase->select($sql);

        foreach ($registros as $fila) {
            $servRegularLunes = $fila['lunes'];
            $servRegularMartes = $fila['martes'];
            $servRegularMiercoles = $fila['miercoles'];
            $servRegularJueves = $fila['jueves'];
            $servRegularViernes = $fila['viernes'];
            $servRegularSabado = $fila['sabado'];
            $servRegularDomingo = $fila['domingo'];
        }

        // Un mismo servicio regular, puede estar en varias combinaciones, pero
        // nunca varias veces en la misma combinación.
        // Por lo que debo de comprobar si alguno de los días de la semana
        // elegidos para el servicio regular, corresponde con alguno de los días
        // de la semana de la combinación
        if ($combinacionLunes == 1) {
            if ($combinacionLunes == $servRegularLunes) {
                $coincideAlgunDiaDeLaSemana = true;
            }
        }

        if ($combinacionMartes == 1) {
            if ($combinacionMartes == $servRegularMartes) {
                $coincideAlgunDiaDeLaSemana = true;
            }
        }

        if ($combinacionMiercoles == 1) {
            if ($combinacionMiercoles == $servRegularMiercoles) {
                $coincideAlgunDiaDeLaSemana = true;
            }
        }

        if ($combinacionJueves === 1) {
            if ($combinacionJueves == $servRegularJueves) {
                $coincideAlgunDiaDeLaSemana = true;
            }
        }

        if ($combinacionViernes === 1) {
            if ($combinacionViernes == $servRegularViernes) {
                $coincideAlgunDiaDeLaSemana = true;
            }
        }

        if ($combinacionSabado === 1) {
            if ($combinacionSabado == $servRegularSabado) {
                $coincideAlgunDiaDeLaSemana = true;
            }
        }

        if ($combinacionDomingo === 1) {
            if ($combinacionDomingo == $servRegularDomingo) {
                $coincideAlgunDiaDeLaSemana = true;
            }
        }

        if ($coincideAlgunDiaDeLaSemana === false) {
            $this->toolBox()->i18nLog()->error("none-weekdays-service-match-weekdays-combination");
        }


        return $coincideAlgunDiaDeLaSemana;
    }

    protected function rellenarConductorVehiculoSiVacios(): bool
    {
        // Comprobar si falta vehículo o conductor
        if (empty($this->iddriver) or empty($this->idvehicle)) {
            // Cargamos el conductor y vehículo de la combinación
            $sql = ' SELECT service_regular_combinations.iddriver '
                . ' , service_regular_combinations.idvehicle '
                . ' FROM service_regular_combinations '
                . ' WHERE service_regular_combinations.idservice_regular_combination = ' . $this->idservice_regular_combination;

            $registros = self::$dataBase->select($sql);

            foreach ($registros as $fila) {
                if (empty($this->iddriver)) {
                    $this->iddriver = $fila['iddriver'];
                    if (!empty($this->iddriver)) {
                        $this->toolBox()->i18nLog()->info("driver-auto-filled-from-combination");
                    }
                }

                if (empty($this->idvehicle)) {
                    $this->idvehicle = $fila['idvehicle'];
                    if (!empty($this->idvehicle)) {
                        $this->toolBox()->i18nLog()->info("vehicle-auto-filled-from-combination.");
                    }
                }
            }


            // Si tras cargar de la combinación todavía hay falta de vehículo o conductor,
            // intentamos cargar conductor o vehículo del servicio regular
            if (empty($this->iddriver) or empty($this->idvehicle)) {
                $sql = ' SELECT service_regulars.iddriver '
                    . ' , service_regulars.idvehicle '
                    . ' FROM service_regulars '
                    . ' WHERE service_regulars.idservice_regular = ' . $this->idservice_regular;

                $registros = self::$dataBase->select($sql);

                foreach ($registros as $fila) {
                    if (empty($this->iddriver)) {
                        $this->iddriver = $fila['iddriver'];
                        if (!empty($this->iddriver)) {
                            $this->toolBox()->i18nLog()->info("driver-auto-filled-from-regular-service");
                        }
                    }

                    if (empty($this->idvehicle)) {
                        $this->idvehicle = $fila['idvehicle'];
                        if (!empty($this->idvehicle)) {
                            $this->toolBox()->i18nLog()->info("vehicle-auto-filled-from-regular-service");
                        }
                    }
                }
            }

            // Si todavía sigue faltando el vehículo o el conductor
            // Saltará la restricción de campo obligatorio de la tabla
            if (empty($this->iddriver) or empty($this->idvehicle)) {
                $aRellenar = '';
                $tampoco = $this->toolBox()->i18n()->trans('also-it-was-not-filled');
                $noPude = $this->toolBox()->i18n()->trans('i-couldnt-complete');

                if (empty($this->iddriver)) {
                    if ($aRellenar === '') {
                        $aRellenar .= ' ' . $this->toolBox()->i18n()->trans('and') . ' ';
                        $tampoco = $this->toolBox()->i18n()->trans('also-they-were-not-refilled');
                        $noPude = $this->toolBox()->i18n()->trans('i-couldnt-complete-them');
                    }
                    $aRellenar .= $this->toolBox()->i18n()->trans('the-driver');
                }

                if (empty($this->idvehicle)) {
                    if ($aRellenar === '') {
                        $aRellenar .= ' y ';
                        $tampoco = $this->toolBox()->i18n()->trans('also-they-were-not-refilled');
                    }
                    $aRellenar .= $this->toolBox()->i18n()->trans('the-vehicle');
                }

                $this->toolBox()->i18nLog()->error("complete-combination-service-or-regular-service", ['%aRellenar%' => $aRellenar, '%tampoco%' => $tampoco, '%noPude%' => $noPude]);
                return false;
            }
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