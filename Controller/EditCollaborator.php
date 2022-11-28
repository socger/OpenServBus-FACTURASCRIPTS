<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditCollaborator extends EditController
{
    public function getModelClassName(): string
    {
        return 'Collaborator';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Empresa colaboradora';
        $pageData['icon'] = 'fas fa-business-time';
        return $pageData;
    }
}