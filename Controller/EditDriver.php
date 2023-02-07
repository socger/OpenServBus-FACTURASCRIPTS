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

use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Plugins\OpenServBus\Model\Collaborator;

class EditDriver extends EditController
{
    public function getModelClassName(): string
    {
        return 'Driver';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'driver';
        $pageData['icon'] = 'fas fa-user-astronaut';
        return $pageData;
    }

    protected function loadData($viewName, $view)
    {
        $mvn = $this->getMainViewName();
        switch ($viewName) {
            case $mvn:
                parent::loadData($viewName, $view);
                $this->loadValuesSelectCollaborators($mvn);
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }

    protected function loadValuesSelectCollaborators(string $mvn)
    {
        $column = $this->views[$mvn]->columnForName('collaborator');
        if($column && $column->widget->getType() === 'select') {
            // obtenemos los colaboradores
            $customValues = [];
            $collaboratorsModel = new Collaborator();
            foreach ($collaboratorsModel->all([], [], 0, 0) as $collaborator) {
                $customValues[] = [
                    'value' => $collaborator->idcollaborator,
                    'title' => $collaborator->getProveedor()->nombre,
                ];
            }
            $column->widget->setValuesFromArray($customValues, false, true);
        }
    }
}