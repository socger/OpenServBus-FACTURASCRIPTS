<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Plugins\OpenServBus\Model\Driver;
use FacturaScripts\Plugins\OpenServBus\Model\Helper;

class EditService extends EditController
{
    public function getModelClassName(): string
    {
        return 'Service';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'service-discretionary';
        $pageData['icon'] = 'fas fa-book-reader';
        return $pageData;
    }

    protected function createViews()
    {
        parent::createViews();
        $this->createViewContacts();
        $this->createViewItineraries();
        $this->createViewValuations();
        $this->setTabsPosition('top');
    }

    protected function createViewContacts(string $viewName = 'EditDireccionContacto')
    {
        $this->addEditListView($viewName, 'Contacto', 'addresses-and-contacts', 'fas fa-address-book');
        $this->views[$viewName]->setInLine(true);
    }

    protected function createViewItineraries($viewName = 'ListServiceItinerary')
    {
        $this->addListView($viewName, 'ServiceItinerary', 'itineraries', 'fas fa-road');
        $this->views[$viewName]->addSearchFields(['nombre']);
        $this->views[$viewName]->addOrderBy(['idservice', 'orden'], 'by-itinerary', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);

        $this->views[$viewName]->addFilterAutocomplete('xIdservice', 'service-discretionary', 'idservice', 'services', 'idservice', 'nombre');
    }

    protected function createViewValuations($viewName = 'ListServiceValuation')
    {
        $this->addListView($viewName, 'ServiceValuation', 'ratings', 'fas fa-dollar-sign');
        $this->views[$viewName]->addOrderBy(['idservice', 'orden'], 'by-rating', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);

        $this->views[$viewName]->addFilterAutocomplete('xIdservice', 'service-discretionary', 'idservice', 'services', 'idservice', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdservice_valuation_type', 'concepts-valuation', 'idservice_valuation_type', 'service_valuation_types', 'idservice_valuation_type', 'nombre');
    }

    protected function displayNoneField($viewName, $fieldName)
    {
        $column = $this->views[$viewName]->columnForField($fieldName);
        $column->display = 'none';
    }

    protected function loadData($viewName, $view)
    {
        $mvn = $this->getMainViewName();
        switch ($viewName) {
            case 'EditDireccionContacto':
                $codcliente = $this->getViewModelValue($mvn, 'codcliente');
                $where = [new DatabaseWhere('codcliente', $codcliente)];
                $view->loadData('', $where);
                break;

            case 'ListServiceItinerary':
            case 'ListServiceValuation':
                $idservice = $this->getViewModelValue($mvn, 'idservice');
                $where = [new DatabaseWhere('idservice', $idservice)];
                $view->loadData('', $where);
                break;

            case $mvn:
                parent::loadData($viewName, $view);
                $this->readOnlyFields($viewName);
                $this->loadValuesSelectHelpers($mvn);
                $this->loadValuesSelectDrivers($mvn, 'driver-1');
                $this->loadValuesSelectDrivers($mvn, 'driver-2');
                $this->loadValuesSelectDrivers($mvn, 'driver-3');
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }

    protected function loadValuesSelectDrivers(string $mvn, string $columnName)
    {
        $column = $this->views[$mvn]->columnForName($columnName);
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

    protected function loadValuesSelectHelpers(string $mvn)
    {
        $column = $this->views[$mvn]->columnForName('helper');
        if($column && $column->widget->getType() === 'select') {
            // obtenemos los monitores
            $customValues = [];
            $helpersModel = new Helper();
            foreach ($helpersModel->all([], [], 0, 0) as $helper) {
                $customValues[] = [
                    'value' => $helper->idhelper,
                    'title' => $helper->nombre,
                ];
            }
            $column->widget->setValuesFromArray($customValues, false, true);
        }
    }

    protected function readOnlyField($viewName, $fieldName)
    {
        $column = $this->views[$viewName]->columnForField($fieldName);
        $column->widget->readonly = 'true';
    }

    protected function readOnlyFields($viewName)
    {
        if (!empty($this->views[$viewName]->model->idfactura)) {
            $this->readOnlyField($viewName, 'idservice');
            $this->readOnlyField($viewName, 'nombre');
            $this->readOnlyField($viewName, 'plazas');
            $this->readOnlyField($viewName, 'codcliente');
            $this->readOnlyField($viewName, 'idvehicle_type');
            $this->readOnlyField($viewName, 'idhelper');
            $this->readOnlyField($viewName, 'hoja_ruta_origen');
            $this->readOnlyField($viewName, 'hoja_ruta_destino');
            $this->readOnlyField($viewName, 'hoja_ruta_expediciones');
            $this->readOnlyField($viewName, 'hoja_ruta_contratante');
            $this->readOnlyField($viewName, 'hoja_ruta_tipoidfiscal');
            $this->readOnlyField($viewName, 'hoja_ruta_cifnif');
            $this->readOnlyField($viewName, 'idservice_type');
            $this->readOnlyField($viewName, 'idempresa');
            $this->readOnlyField($viewName, 'idfactura');
            $this->readOnlyField($viewName, 'importe');
            $this->readOnlyField($viewName, 'codimpuesto');
            $this->readOnlyField($viewName, 'importe_enextranjero');
            $this->readOnlyField($viewName, 'codimpuesto_enextranjero');
            $this->readOnlyField($viewName, 'total');
            $this->readOnlyField($viewName, 'codsubcuenta_km_nacional');
            $this->readOnlyField($viewName, 'codsubcuenta_km_extranjero');
            $this->readOnlyField($viewName, 'inicio_horaAnt');
            $this->readOnlyField($viewName, 'salida_desde_nave_sn');
            $this->readOnlyField($viewName, 'inicio_dia');
            $this->readOnlyField($viewName, 'inicio_hora');
            $this->readOnlyField($viewName, 'fin_dia');
            $this->readOnlyField($viewName, 'fin_hora');
            $this->readOnlyField($viewName, 'idvehicle');
            $this->readOnlyField($viewName, 'iddriver_1');
            $this->readOnlyField($viewName, 'driver_alojamiento_1');
            $this->readOnlyField($viewName, 'driver_observaciones_1');
            $this->readOnlyField($viewName, 'iddriver_2');
            $this->readOnlyField($viewName, 'driver_alojamiento_2');
            $this->readOnlyField($viewName, 'driver_observaciones_2');
            $this->readOnlyField($viewName, 'iddriver_3');
            $this->readOnlyField($viewName, 'driver_alojamiento_3');
            $this->readOnlyField($viewName, 'driver_observaciones_3');
            $this->readOnlyField($viewName, 'observaciones');
            $this->readOnlyField($viewName, 'observaciones_montaje');
            $this->readOnlyField($viewName, 'observaciones_drivers');
            $this->readOnlyField($viewName, 'observaciones_vehiculo');
            $this->readOnlyField($viewName, 'observaciones_facturacion');
            $this->readOnlyField($viewName, 'observaciones_liquidacion');
            $this->readOnlyField($viewName, 'motivobaja');
            $this->displayNoneField($viewName, 'aceptado');
            $this->displayNoneField($viewName, 'fuera_del_municipio');
            $this->displayNoneField($viewName, 'facturar_SN');
            $this->displayNoneField($viewName, 'salida_desde_nave_sn');
            $this->displayNoneField($viewName, 'activo');
            return;
        }

        $this->displayNoneField($viewName, 'aceptado_text');
        $this->displayNoneField($viewName, 'fuera_del_municipio_text');
        $this->displayNoneField($viewName, 'facturar_SN_text');
        $this->displayNoneField($viewName, 'salida_desde_nave_text');
        $this->displayNoneField($viewName, 'activo_text');
    }
}