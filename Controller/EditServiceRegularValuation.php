<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditServiceRegularValuation extends EditController
{
    public function getModelClassName(): string
    {
        return 'ServiceRegularValuation';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'serv-regular-rating';
        $pageData['icon'] = 'fas fa-dollar-sign';
        return $pageData;
    }
}