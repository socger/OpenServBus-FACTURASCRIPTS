<?php

namespace FacturaScripts\Plugins\OpenServBus\Extension\Controller;

use Closure;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

class EditRole
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
                $where = [new DataBaseWhere('codrole', $this->getViewModelValue($mvn, 'codrole'))];
                $view->loadData('', $where);
            }
        };
    }
}