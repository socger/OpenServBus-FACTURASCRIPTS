<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Plugins\OpenServBus\Model\Collaborator;

class EditHelper extends EditController
{
    public function getModelClassName(): string
    {
        return 'Helper';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'Archivos';
        $pageData['title'] = 'helper';
        $pageData['icon'] = 'fas fa-user-graduate';
        return $pageData;
    }

    protected function loadData($viewName, $view)
    {
        $mvn = $this->getMainViewName();
        switch ($viewName) {
            case $mvn:
                $view->loadData();
                $this->loadValuesSelectCollaborators($mvn);
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }

    protected function loadValuesSelectCollaborators(string $mvn)
    {
        $column = $this->views[$mvn]->columnForName('collaborator');
        if($column && $column->widget->getType() === 'select') {
            // obtenemos los colaboradores
            $customValues = [];
            $collaboratorsModel = new Collaborator();
            foreach ($collaboratorsModel->all([], [], 0, 0) as $collaborator) {
                $customValues[] = [
                    'value' => $collaborator->idcollaborator,
                    'title' => $collaborator->getProveedor()->nombre,
                ];
            }
            $column->widget->setValuesFromArray($customValues, false, true);
        }
    }
}