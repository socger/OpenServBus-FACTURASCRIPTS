<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditDocumentationType extends EditController
{
    public function getModelClassName(): string
    {
        return 'DocumentationType';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Doc. - Tipo';
        $pageData['icon'] = 'far fa-address-card';
        return $pageData;
    }
}