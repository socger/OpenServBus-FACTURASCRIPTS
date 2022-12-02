<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditVehicleType extends EditController
{
    public function getModelClassName(): string
    {
        return 'VehicleType';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'vehicle-type';
        $pageData['icon'] = 'fas fa-tractor';
        return $pageData;
    }
}