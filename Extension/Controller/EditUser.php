<?php

namespace FacturaScripts\Plugins\OpenServBus\Extension\Controller;

use Closure;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

class EditUser
{
    public function createViews(): Closure
    {
        return function () {
            $this->addListView('ListAdvertismentUser', 'AdvertismentUser', 'Avisos', 'fas fa-exclamation-triangle');
        };
    }

    public function loadData(): Closure
    {
        return function ($viewName, $view) {
            if ($viewName === 'ListAdvertismentUser') {
                $mvn = $this->getMainViewName();
                $where = [new DataBaseWhere('nick', $this->getViewModelValue($mvn, 'nick'))];
                $view->loadData('', $where);
            }
        };
    }
}