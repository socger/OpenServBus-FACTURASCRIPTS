<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditTarjetaType extends EditController
{
    public function getModelClassName(): string
    {
        return 'TarjetaType';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Tarjeta - tipo';
        $pageData['icon'] = 'far fa-credit-card';
        return $pageData;
    }
}