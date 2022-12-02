<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditVehicleEquipament extends EditController
{
    public function getModelClassName(): string
    {
        return 'VehicleEquipament';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'vehicle-equipment';
        $pageData['icon'] = 'fab fa-accessible-icon';
        return $pageData;
    }
}