<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditServiceType extends EditController
{
    public function getModelClassName(): string
    {
        return 'ServiceType';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Servicio - tipo';
        $pageData['icon'] = 'fas fa-dolly';
        return $pageData;
    }
}