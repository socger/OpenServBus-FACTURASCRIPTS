<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditHelper extends EditController
{
    public function getModelClassName(): string
    {
        return 'Helper';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'Archivos';
        $pageData['title'] = 'Monitor/a';
        $pageData['icon'] = 'fas fa-user-graduate';
        return $pageData;
    }
}