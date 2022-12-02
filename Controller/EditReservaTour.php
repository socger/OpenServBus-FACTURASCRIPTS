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

use FacturaScripts\Core\Lib\ExtendedController\EditController;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class EditReservaTour extends EditController
{
    public function getModelClassName(): string
    {
        return "ReservaTour";
    }

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "booking";
        $data["icon"] = "fas fa-calendar-check";
        return $data;
    }
}
