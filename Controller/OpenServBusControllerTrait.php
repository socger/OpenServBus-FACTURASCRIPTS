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

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Plugins\OpenServBus\Model\EstadoReservaTour;
use FacturaScripts\Plugins\OpenServBus\Model\EstadoServicioTour;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
trait OpenServBusControllerTrait
{
    protected function addColorStatusBooking(string $viewName): void
    {
        $statusBooking = new EstadoReservaTour();
        foreach ($statusBooking->all([], [], 0, 0) as $status) {
            if ($status->color) {
                $this->views[$viewName]->addColor('idestado', $status->id, $status->color, $status->name);
            }
        }
    }

    protected function addColorStatusService(string $viewName): void
    {
        $statusService = new EstadoServicioTour();
        foreach ($statusService->all([], [], 0, 0) as $status) {
            if ($status->color) {
                $this->views[$viewName]->addColor('idestado', $status->id, $status->color, $status->name);
            }
        }
    }
}