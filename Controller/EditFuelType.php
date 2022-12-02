<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditFuelType extends EditController
{
    public function getModelClassName(): string
    {
        return 'FuelType';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'fuel-type';
        $pageData['icon'] = 'fas fa-charging-station';
        return $pageData;
    }
}