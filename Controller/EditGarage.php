<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditGarage extends EditController
{
    public function getModelClassName(): string
    {
        return 'Garage';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Cochera';
        $pageData['icon'] = 'fas fa-warehouse';
        return $pageData;
    }
}