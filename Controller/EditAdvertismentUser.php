<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditAdvertismentUser extends EditController
{
    public function getModelClassName(): string
    {
        return 'AdvertismentUser';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Aviso';
        $pageData['icon'] = 'fas fa-exclamation-triangle';
        return $pageData;
    }
}