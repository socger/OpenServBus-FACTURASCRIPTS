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
use FacturaScripts\Core\Lib\ExtendedController\EditController;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class EditSubReservaTour extends EditController
{
    use OpenServBusControllerTrait;

    public function getModelClassName(): string
    {
        return "SubReservaTour";
    }

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "underbook";
        $data["icon"] = "fas fa-calendar-alt";
        return $data;
    }

    protected function createViews()
    {
        parent::createViews();
        $this->createViewsServicioTour();
        $this->setTabsPosition('bottom');
    }

    protected function createViewsServicioTour(string $viewName = "ListServicioTour")
    {
        $this->addListView($viewName, "ServicioTour", "services", "fas fa-concierge-bell");
        $this->views[$viewName]->addOrderBy(["id"], "code", 2);
        $this->views[$viewName]->addOrderBy(["pickupdate", "pickuptime"], "pick-up-date");
        $this->views[$viewName]->addOrderBy(["destinationdate", "destinationtime"], "destination-date");
        $this->views[$viewName]->addSearchFields(["id", "routecode", "routename", "pickupflightid", "destinationflightid", "pickuplocation", "pickuppoint", "destinationpoint"]);

        // ocultar columnas
        $this->views[$viewName]->disableColumn('underbook', true);

        // Filtros
        $serviceTypes = $this->codeModel->all('service_types', 'idservice_type', 'nombre');
        $this->views[$viewName]->addFilterSelect('idtiposervicio', 'service-type', 'idtiposervicio', $serviceTypes);

        $status = $this->codeModel->all('tour_servicios_estados', 'id', 'name');
        $this->views[$viewName]->addFilterSelect('idestado', 'status', 'idestado', $status);

        $serviceDiscrecional = $this->codeModel->all('services', 'idservice', 'nombre');
        $this->views[$viewName]->addFilterSelect('idserviciodiscrecional', 'service-discretionary', 'idserviciodiscrecional', $serviceDiscrecional);

        $serviceRegular = $this->codeModel->all('service_regulars', 'idservice_regular', 'nombre');
        $this->views[$viewName]->addFilterSelect('idservicioregular', 'service-regular', 'idservicioregular', $serviceRegular);

        // asignamos los colores
        $this->addColorStatusService($viewName);
    }

    protected function loadData($viewName, $view)
    {
        $mvn = $this->getMainViewName();
        switch ($viewName) {
            case 'ListServicioTour':
                $idsubreserva = $this->getViewModelValue($mvn, 'id');
                $where = [new DatabaseWhere('idsubreserva', $idsubreserva)];
                $view->loadData('', $where);
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }
}
