<?php
/**
 * This file is part of OpenServBus plugin for FacturaScripts
 * Copyright (C) 2022 Carlos Garcia Gomez <carlos@facturascripts.com>
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

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Core\Session;
use FacturaScripts\Dinamic\Model\Cliente;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class SubReservaTour extends ModelClass
{
    use ModelTrait;

    const TYPE_PLAZA = 'PLAZA';
    const TYPE_VEHICLE = 'VEHICULO';

    /** @var bool */
    public $closed;

    /** @var string */
    public $codcliente;

    /** @var string */
    public $creationdate;

    /** @var string */
    public $date;

    /** @var int */
    public $id;

    /** @var int */
    public $idalbaran;

    /** @var int */
    public $idestado;

    /** @var int */
    public $idreserva;

    /** @var string */
    public $lastnick;

    /** @var string */
    public $lastupdate;

    /** @var string */
    public $nick;

    /** @var string */
    public $notes;

    /** @var string */
    public $reference;

    /** @var string */
    public $type;

    public function clear() 
    {
        parent::clear();
        $this->closed = false;
        $this->creationdate = date(self::DATETIME_STYLE);
        $this->nick = Session::get('user')->nick ?? null;
    }

    public function delete(): bool
    {
        if (false === parent::delete()) {
            return false;
        }

        $this->checkEstadoReserva();
        return true;
    }

    public function getCliente(): Cliente
    {
        $cliente = new Cliente();
        $cliente->loadFromCode($this->codcliente);
        return $cliente;
    }

    public function getEstado(): EstadoReservaTour
    {
        $estado = new EstadoReservaTour();
        $estado->loadFromCode($this->idestado);
        return $estado;
    }

    public function getProductos(): array
    {
        $producto = new SubReservaTourProduct();
        $where = [new DataBaseWhere('idsubreserva', $this->id)];
        return $producto->all($where, [], 0, 0);
    }

    public function getReserva(): ReservaTour
    {
        $reserva = new ReservaTour();
        $reserva->loadFromCode($this->idreserva);
        return $reserva;
    }

    public function getServices(): array
    {
        $serviceModel = new ServicioTour();
        $where = [new DataBaseWhere('idsubreserva', $this->id)];
        return $serviceModel->all($where, ['id' => 'ASC'], 0, 0);
    }

    public function install(): string
    {
        new ReservaTour();
        new EstadoReservaTour();
        return parent::install();
    }

    public function isClosed(): bool
    {
        // comprobamos si todos los servicios están cerrados
        foreach ($this->getServices() as $service) {
            if (false === $service->closed) {
                return false;
            }
        }

        return true;
    }

    public static function primaryColumn(): string
    {
        return "id";
    }

    public function save(): bool
    {
        if (false === parent::save()) {
            return false;
        }

        $this->checkEstadoReserva();
        return true;
    }

    public static function tableName(): string
    {
        return "tour_subreservas";
    }

    public function test(): bool
    {
        $this->closed = $this->isClosed();
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'ListReservaTour'): string
    {
        return parent::url($type, $list . '?activetab=List');
    }

    protected function checkEstadoReserva()
    {
        // Obtenemos todas las reservas y las guardamos para que actualice si está completa o no
        $reservaModel = new ReservaTour();
        $where = [new DataBaseWhere('id', $this->idreserva)];
        foreach ($reservaModel->all($where, ['id' => 'ASC']) as $reserva) {
            $reserva->save();
        }
    }

    protected function saveInsert(array $values = []): bool
    {
        $this->lastnick = null;
        $this->lastupdate = null;
        return parent::saveInsert($values);
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->lastnick = Session::get('user')->nick ?? null;
        $this->lastupdate = date(self::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}
