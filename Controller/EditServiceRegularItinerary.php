<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditServiceRegularItinerary extends EditController
{
    public function getModelClassName(): string
    {
        return 'ServiceRegularItinerary';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'serv-regular-itinerary';
        $pageData['icon'] = 'fas fa-road';
        return $pageData;
    }
}