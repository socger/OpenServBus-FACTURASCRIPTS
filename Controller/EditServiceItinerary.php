<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditServiceItinerary extends EditController
{
    public function getModelClassName(): string
    {
        return 'ServiceItinerary';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Serv. discrecional - Itinerario';
        $pageData['icon'] = 'fas fa-road';
        return $pageData;
    }
}