<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditDriver extends EditController
{

    public function getModelClassName(): string
    {
        return 'Driver';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Conductor/a';
        $pageData['icon'] = 'fas fa-user-astronaut';
        return $pageData;
    }
}