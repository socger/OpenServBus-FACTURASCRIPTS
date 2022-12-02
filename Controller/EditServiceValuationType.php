<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditServiceValuationType extends EditController
{
    public function getModelClassName(): string
    {
        return 'ServiceValuationType';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'concept-valuation';
        $pageData['icon'] = 'fas fa-hand-holding-usd';
        return $pageData;
    }
}