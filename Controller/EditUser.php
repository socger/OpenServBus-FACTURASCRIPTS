<?php

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
