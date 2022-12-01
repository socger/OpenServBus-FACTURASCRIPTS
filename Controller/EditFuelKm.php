<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Plugins\OpenServBus\Model\Driver;

class EditFuelKm extends EditController
{
    public function getModelClassName(): string
    {
        return 'FuelKm';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Repostaje - Kms';
        $pageData['icon'] = 'fas fa-gas-pump';
        return $pageData;
    }

    protected function getTipoTarjeta(string $p_viewName)
    {
        if (!empty($this->views[$p_viewName]->model->idtarjeta)) {
            $sql = " SELECT tarjeta_types.nombre "
                . " , tarjeta_types.de_pago "
                . " FROM tarjetas "
                . " LEFT JOIN tarjeta_types ON (tarjeta_types.idtarjeta_type = tarjetas.idtarjeta_type) "
                . " WHERE tarjetas.idtarjeta = " . $this->views[$p_viewName]->model->idtarjeta . " ";

            $registros = $this->dataBase->select($sql);

            foreach ($registros as $fila) {
                $this->views[$p_viewName]->model->tipo_tarjeta = $fila['nombre'];

                if ($fila['de_pago'] == 1) {
                    $this->views[$p_viewName]->model->es_de_pago = 'Si';
                } else {
                    $this->views[$p_viewName]->model->es_de_pago = 'No';
                }
            }
        }
    }

    protected function loadData($viewName, $view)
    {
        $mvn = $this->getMainViewName();
        switch ($viewName) {
            case $mvn:
                parent::loadData($viewName, $view);
                $this->getTipoTarjeta($viewName);
                $this->loadValuesSelectDrivers($mvn);
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }

    protected function loadValuesSelectDrivers(string $mvn)
    {
        $column = $this->views[$mvn]->columnForName('driver');
        if($column && $column->widget->getType() === 'select') {
            // obtenemos los conductores
            $customValues = [];
            $driversModel = new Driver();
            foreach ($driversModel->all([], [], 0, 0) as $driver) {
                $customValues[] = [
                    'value' => $driver->iddriver,
                    'title' => $driver->nombre,
                ];
            }
            $column->widget->setValuesFromArray($customValues, false, true);
        }
    }
}