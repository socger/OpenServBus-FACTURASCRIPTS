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

use FacturaScripts\Core\Lib\ExtendedController\ListController;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class ListTourOperador extends ListController
{
    use OpenServBusControllerTrait;

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "tour-operators";
        $data["menu"] = "OpenServBus";
        $data["icon"] = "fas fa-globe-europe";
        return $data;
    }

    protected function createViews()
    {
        $this->createViewsTourOperador();
        $this->createViewsReservaTour();
        $this->createViewsSubReservaTour();
    }

    protected function createViewsReservaTour(string $viewName = "ListReservaTour")
    {
        $this->addView($viewName, "ReservaTour", "bookings", "fas fa-calendar-check");
        $this->addOrderBy($viewName, ["id"], "code", 2);
        $this->addSearchFields($viewName, ["id", "reference"]);

        // Filtros
        $operators = $this->codeModel->all('tour_operadores', 'id', 'name');
        $this->addFilterSelect($viewName, 'idoperador', 'operator', 'idoperador', $operators);

        $status = $this->codeModel->all('tour_reservas_estados', 'id', 'name');
        $this->addFilterSelect($viewName, 'idestado', 'status', 'idestado', $status);

        // asignamos los colores
        $this->addColorStatusBooking($viewName);
    }

    protected function createViewsSubReservaTour(string $viewName = "ListSubReservaTour")
    {
        $this->addView($viewName, "SubReservaTour", "underbooks", "fas fa-calendar-alt");
        $this->addOrderBy($viewName, ["id"], "code", 2);
        $this->addSearchFields($viewName, ["id", "reference"]);

        // Filtros
        $operators = $this->codeModel->all('tour_operadores', 'id', 'name');
        $this->addFilterSelect($viewName, 'idoperador', 'operator', 'idoperador', $operators);

        $bookings = $this->codeModel->all('tour_reservas', 'id', 'id');
        $this->addFilterSelect($viewName, 'idreserva', 'booking', 'idreserva', $bookings);

        $status = $this->codeModel->all('tour_reservas_estados', 'id', 'name');
        $this->addFilterSelect($viewName, 'idestado', 'status', 'idestado', $status);

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