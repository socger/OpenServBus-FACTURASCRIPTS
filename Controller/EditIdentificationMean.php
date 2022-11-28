<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditIdentificationMean extends EditController
{

    public function getModelClassName(): string
    {
        return 'IdentificationMean';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Identificación - medio';
        $pageData['icon'] = 'far fa-hand-point-right';
        return $pageData;
    }
}