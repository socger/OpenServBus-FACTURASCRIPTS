<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditFuelPump extends EditController
{
    public function getModelClassName(): string
    {
        return 'FuelPump';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Surtidor interno';
        $pageData['icon'] = 'fas fa-thumbtack';
        return $pageData;
    }
}