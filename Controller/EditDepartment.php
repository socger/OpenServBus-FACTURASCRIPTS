<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditDepartment extends EditController
{
    public function getModelClassName(): string
    {
        return 'Department';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'department';
        $pageData['icon'] = 'fas fa-book-reader';
        return $pageData;
    }
}