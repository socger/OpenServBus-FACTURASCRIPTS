<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\DocFilesTrait;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditVehicleDocumentation extends EditController
{
    use DocFilesTrait;

    public function getModelClassName(): string
    {
        return 'VehicleDocumentation';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Doc. Vehículo';
        $pageData['icon'] = 'far fa-file-pdf';
        return $pageData;
    }

    protected function createViews()
    {
        parent::createViews();
        $this->setTabsPosition('top');
        $this->createViewDocFiles();
    }

    protected function loadData($viewName, $view)
    {
        switch ($viewName) {
            case 'docfiles':
                $this->loadDataDocFiles($view, $this->getModelClassName(), $this->getModel()->primaryColumnValue());
                break;

            default:
                parent::loadData($viewName, $view);
                break;

        }
    }

    protected function execPreviousAction($action): bool
    {
        switch ($action) {
            case 'add-file':
                return $this->addFileAction();

            case 'delete-file':
                return $this->deleteFileAction();

            case 'edit-file':
                return $this->editFileAction();

            case 'unlink-file':
                return $this->unlinkFileAction();
        }

        return parent::execPreviousAction($action);
    }
}