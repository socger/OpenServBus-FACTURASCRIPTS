<?php


namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListAdvertismentUser extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'warnings';
        $pageData['icon'] = 'fas fa-exclamation-triangle';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createAdvertismentUser();
    }

    protected function createAdvertismentUser($viewName = 'ListAdvertismentUser')
    {
        $this->addView($viewName, 'AdvertismentUser', 'warnings', 'fas fa-exclamation-triangle');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre', 'inicio', 'fin'], 'notice-start-end', 1);
        $this->addOrderBy($viewName, ['nick', 'nombre', 'inicio', 'fin'], 'user-notice-start-end');
        $this->addOrderBy($viewName, ['codrole', 'nombre', 'inicio', 'fin'], 'user-group-notice-start-end');
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $this->addFilterAutocomplete($viewName, 'xNick', 'user', 'nick', 'users', 'nick', 'nick');
        $this->addFilterAutocomplete($viewName, 'xCodRole', 'user-groups', 'codrole', 'roles', 'codrole', 'descripcion');
        $this->addFilterDatePicker($viewName, 'inicio', 'notices-from', 'inicio');
        $this->addFilterDatePicker($viewName, 'fin', 'notices-to', 'fin');

        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'active-all', 'activo', $activo);
    }
}