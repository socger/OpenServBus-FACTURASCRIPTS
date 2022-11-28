<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditAbsenceReason extends EditController
{
    public function getModelClassName(): string
    {
        return 'AbsenceReason';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Ausencia - motivo';
        $pageData['icon'] = 'fas fa-first-aid';
        return $pageData;
    }
}