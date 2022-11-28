<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditEmployeeAttendanceManagement extends EditController
{
    public function getModelClassName(): string
    {
        return 'EmployeeAttendanceManagement';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Fichaje / Asistencia';
        $pageData['icon'] = 'fas fa-hourglass-half';
        return $pageData;
    }
}