<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditTarjeta extends EditController
{
    public function getModelClassName(): string
    {
        return 'Tarjeta';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Tarjeta';
        $pageData['icon'] = 'fab fa-cc-mastercard';
        return $pageData;
    }
}