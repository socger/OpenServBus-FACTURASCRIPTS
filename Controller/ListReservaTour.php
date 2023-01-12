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
class ListReservaTour extends ListController
{
    use OpenServBusControllerTrait;

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "bookings";
        $data["menu"] = "OpenServBus";
        $data["icon"] = "fas fa-calendar-check";
        return $data;
    }

    protected function createViews()
    {
        $this->createViewsReservaTour();
        $this->createViewsSubReservaTour();
        $this->createViewsServicioTour();
        $this->createViewsPasajeroTour();
    }

    protected function createViewsPasajeroTour(string $viewName = "ListPasajeroTour")
    {
        $this->addView($viewName, "PasajeroTour", "passengers", "fas fa-users");
        $this->addOrderBy($viewName, ["id"], "id", 2);
        $this->addOrderBy($viewName, ["idservicio"], "service");
        $this->addOrderBy($viewName, ["idcontacto"], "contact");

        // Filtros
        $services = $this->codeModel->all('tour_servicios', 'id', 'routename');
        $this->addFilterSelect($viewName, 'idservicio', 'service', 'idservicio', $services);

        $this->addFilterAutocomplete($viewName, 'idcontacto', 'contact', 'idcontacto', 'contactos', 'idcontacto', 'nombre');

        // ocultamos botones
        $this->setSettings($viewName, 'btnNew', false);
    }

    protected function createViewsReservaTour(string $viewName = "ListReservaTour")
    {
        $this->addView($viewName, "ReservaTour", "bookings", "fas fa-calendar-check");
        $this->addOrderBy($viewName, ["id"], "code", 2);
        $this->addSearchFields($viewName, ["id", "reference"]);

        // Filtros
        $this->addFilterAutocomplete($viewName, 'codcliente', 'tour-operator', 'codcliente', 'clientes', 'codcliente', 'nombre');

        $status = $this->codeModel->all('tour_reservas_estados', 'id', 'name');
        $this->addFilterSelect($viewName, 'idestado', 'status', 'idestado', $status);

        // asignamos los colores
        $this->addColorStatusBooking($viewName);
    }

    protected function createViewsServicioTour(string $viewName = "ListServicioTour")
    {
        $this->addView($viewName, "ServicioTour", "services", "fas fa-concierge-bell");
        $this->addOrderBy($viewName, ["id"], "code", 2);
        $this->addOrderBy($viewName, ["pickupdate", "pickuptime"], "pick-up-date");
        $this->addOrderBy($viewName, ["destinationdate", "destinationtime"], "destination-date");
        $this->addOrderBy($viewName, ["seatingtotal"], "seating-total");
        $this->addSearchFields($viewName, ["id", "routecode", "routename", "pickupflightid", "destinationflightid", "pickuplocation", "pickuppoint", "destinationpoint"]);

        // Filtros
        $this->addFilterCheckbox($viewName, 'vip', 'vip', 'vip');

        $subReservas = $this->codeModel->all('tour_subreservas', 'id', 'id');
        $this->addFilterSelect($viewName, 'idsubreserva', 'underbook', 'idsubreserva', $subReservas);

        $serviceTypes = $this->codeModel->all('service_types', 'idservice_type', 'nombre');
        $this->addFilterSelect($viewName, 'idtiposervicio', 'service-type', 'idtiposervicio', $serviceTypes);

        $status = $this->codeModel->all('tour_servicios_estados', 'id', 'name');
        $this->addFilterSelect($viewName, 'idestado', 'status', 'idestado', $status);

        $serviceDiscrecional = $this->codeModel->all('services', 'idservice', 'nombre');
        $this->addFilterSelect($viewName, 'idserviciodiscrecional', 'service-discretionary', 'idserviciodiscrecional', $serviceDiscrecional);

        $serviceRegular = $this->codeModel->all('service_regulars', 'idservice_regular', 'nombre');
        $this->addFilterSelect($viewName, 'idservicioregular', 'service-regular', 'idservicioregular', $serviceRegular);

        // asignamos los colores
        $this->addColorStatusService($viewName);

        // ocultamos botones
        $this->setSettings($viewName, 'btnNew', false);
    }

    protected function createViewsSubReservaTour(string $viewName = "ListSubReservaTour")
    {
        $this->addView($viewName, "SubReservaTour", "underbooks", "fas fa-calendar-alt");
        $this->addOrderBy($viewName, ["id"], "code", 2);
        $this->addSearchFields($viewName, ["id", "reference"]);

        // Filtros
        $this->addFilterAutocomplete($viewName, 'codcliente', 'agency', 'codcliente', 'clientes', 'codcliente', 'nombre');

        $bookings = $this->codeModel->all('tour_reservas', 'id', 'id');
        $this->addFilterSelect($viewName, 'idreserva', 'booking', 'idreserva', $bookings);

        $status = $this->codeModel->all('tour_reservas_estados', 'id', 'name');
        $this->addFilterSelect($viewName, 'idestado', 'status', 'idestado', $status);

        // asignamos los colores
        $this->addColorStatusBooking($viewName);

        // ocultamos botones
        $this->setSettings($viewName, 'btnNew', false);
    }
}