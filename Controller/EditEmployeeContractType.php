<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditEmployeeContractType extends EditController
{
    public function getModelClassName(): string
    {
        return 'EmployeeContractType';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'contract-type';
        $pageData['icon'] = 'fas fa-file-signature';
        return $pageData;
    }
}