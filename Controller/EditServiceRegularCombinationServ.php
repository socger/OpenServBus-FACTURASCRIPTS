<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditServiceRegularCombinationServ extends EditController
{
    public function getModelClassName(): string
    {
        return 'ServiceRegularCombinationServ';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Serv. reg. - Combinación - Servicio';
        $pageData['icon'] = 'fas fa-cogs';
        return $pageData;
    }
}