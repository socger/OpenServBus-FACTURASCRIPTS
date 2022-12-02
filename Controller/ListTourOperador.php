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

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\ListController;
use FacturaScripts\Dinamic\Model\EstadoDocumento;
use FacturaScripts\Plugins\OpenServBus\Model\EstadoReservaTour;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class ListTourOperador extends ListController
{
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "tour-operators";
        $data["menu"] = "OpenServBus";
        $data["icon"] = "fas fa-globe-europe";
        return $data;
    }

    protected function addColorStatusBooking(string $viewName): void
    {
        $statusBooking = new EstadoReservaTour();
        foreach ($statusBooking->all([], [], 0, 0) as $status) {
            if ($status->color) {
                $this->addColor($viewName, 'idestado', $status->id, $status->color, $status->name);
            }
        }
    }

    protected function createViews()
    {
        $this->createViewsTourOperador();
        $this->createViewsReservaTour();
    }

    protected function createViewsReservaTour(string $viewName = "ListReservaTour")
    {
        $this->addView($viewName, "ReservaTour", "bookings", "fas fa-calendar-check");
        $this->addOrderBy($viewName, ["id"], "code", 2);
        $this->addSearchFields($viewName, ["id"]);

        // Filtros
        $operators = $this->codeModel->all('tour_operadores', 'id', 'name');
        $this->addFilterSelect($viewName, 'idoperador', 'operator', 'idoperador', $operators);

        // asignamos los colores
        $this->addColorStatusBooking($viewName);
    }

    protected function createViewsTourOperador(string $viewName = "ListTourOperador")
    {
        $this->addView($viewName, "TourOperador", "tour-operators", "fas fa-globe-europe");
        $this->addOrderBy($viewName, ["name"], "name");
        $this->addSearchFields($viewName, ["name"]);
    }
}