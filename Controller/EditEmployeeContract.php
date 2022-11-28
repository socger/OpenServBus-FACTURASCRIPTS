<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditEmployeeContract extends EditController
{
    public function getModelClassName(): string
    {
        return 'EmployeeContract';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Contrato';
        $pageData['icon'] = 'fas fa-id-badge';
        return $pageData;
    }
}