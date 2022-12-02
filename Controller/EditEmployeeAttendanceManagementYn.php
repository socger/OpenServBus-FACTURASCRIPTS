<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditEmployeeAttendanceManagementYn extends EditController
{
    public function getModelClassName(): string
    {
        return 'EmployeeAttendanceManagementYn';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'obliged-on-site-control';
        $pageData['icon'] = 'fas fa-business-time';
        return $pageData;
    }
}