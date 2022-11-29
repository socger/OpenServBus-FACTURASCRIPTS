<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditStop extends EditController
{
    public function getModelClassName(): string
    {
        return 'Stop';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Parada';
        $pageData['icon'] = 'fas fa-stopwatch';
        return $pageData;
    }
}