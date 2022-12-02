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

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Controller\EditUser as ParentController;

class EditUser extends ParentController
{
    protected function createViews()
    {
        parent::createViews();
        $this->addListView('ListEmployee', 'Employee', 'employees-with-this-user');
    }

    protected function loadData($viewName, $view)
    {
        switch ($viewName) {
            case 'ListEmployee':
                $nick = $this->getViewModelValue('EditUser', 'nick');
                $where = [new DataBaseWhere('user_facturascripts_nick', $nick)];
                $view->loadData('', $where);

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }
}
