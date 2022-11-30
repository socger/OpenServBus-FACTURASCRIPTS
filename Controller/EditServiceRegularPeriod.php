<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditServiceRegularPeriod extends EditController
{
    public function getModelClassName(): string
    {
        return 'ServiceRegularPeriod';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Serv. regular - Periodo';
        $pageData['icon'] = 'fas fa-calendar-day';
        return $pageData;
    }
}