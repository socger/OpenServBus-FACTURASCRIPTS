<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditVehicleEquipamentType extends EditController
{
    public function getModelClassName(): string
    {
        return 'VehicleEquipamentType';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Equipamiento - Tipo';
        $pageData['icon'] = 'fas fa-wheelchair';
        return $pageData;
    }
}