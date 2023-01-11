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
class EditServicioTour extends EditController
{
    public function getModelClassName(): string
    {
        return "ServicioTour";
    }

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "service";
        $data["icon"] = "fas fa-concierge-bell";
        return $data;
    }

    protected function createViews()
    {
        parent::createViews();
        $this->createViewsContacto();
        $this->setTabsPosition('bottom');
    }

    protected function createViewsContacto(string $viewName = "EditPasajeroTour")
    {
        $this->addEditListView($viewName, "PasajeroTour", "passengers", "fas fa-users");

        // ocultar columnas
        $this->views[$viewName]->disableColumn('service', true);
    }

    protected function loadData($viewName, $view)
    {
        $mvn = $this->getMainViewName();
        switch ($viewName) {
            case 'EditPasajeroTour':
                $idservicio = $this->getViewModelValue($mvn, 'id');
                $where = [new DatabaseWhere('idservicio', $idservicio)];
                $view->loadData('', $where);
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }
}
