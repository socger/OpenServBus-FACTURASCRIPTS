<?php
namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditSubReservaTourProduct extends EditController
{
    public function getModelClassName(): string
    {
        return "SubReservaTourProduct";
    }

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "SubReservaTourProduct";
        $data["icon"] = "fas fa-search";
        return $data;
    }
}
