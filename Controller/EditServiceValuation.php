<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditServiceValuation extends EditController
{
    public function getModelClassName(): string
    {
        return 'ServiceValuation';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Serv. discrecional - Valoración';
        $pageData['icon'] = 'fas fa-dollar-sign';
        return $pageData;
    }
}