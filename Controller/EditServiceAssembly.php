<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Plugins\OpenServBus\Model\Driver;
use FacturaScripts\Plugins\OpenServBus\Model\Helper;

class EditServiceAssembly extends EditController
{

    public function getModelClassName(): string
    {
        return 'ServiceAssembly';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Montaje de servicios';
        $pageData['icon'] = 'fas fa-business-time';
        return $pageData;
    }

    protected function createViews()
    {
        parent::createViews();
        $this->createViewContacts();
        $this->setTabsPosition('top');
    }

    protected function createViewContacts(string $viewName = 'EditDireccionContacto')
    {
        $this->addEditListView($viewName, 'Contacto', 'addresses-and-contacts', 'fas fa-address-book');
        $this->views[$viewName]->setInLine(true);
    }

    protected function displayOnlyFieldsForDiscretionalServ($viewName)
    {
        // Es un discrecional, por lo que se ponen invisibles estos campos
        $this->displayNoneField($viewName, 'cod_servicio');
        $this->displayNoneField($viewName, 'fuera_del_municipio');
        $this->displayNoneField($viewName, 'facturar_SN');
        $this->displayNoneField($viewName, 'facturar_agrupando');
        $this->displayNoneField($viewName, 'salida_desde_nave_sn');
        $this->displayNoneField($viewName, 'activo');
    }

    protected function displayOnlyFieldsForRegularServ($viewName)
    {
        // Es un regular, por lo que se ponen invisibles estos campos
        $this->displayNoneField($viewName, 'idservice');
        $this->displayNoneField($viewName, 'fuera_del_municipio_text');
        $this->displayNoneField($viewName, 'facturar_SN_text');
        $this->displayNoneField($viewName, 'facturar_agrupando_text');
        $this->displayNoneField($viewName, 'salida_desde_nave_text');
        $this->displayNoneField($viewName, 'activo_text');
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
                $codcliente = $this->getViewModelValue('EditService_assembly', 'codcliente');
                $where = [new DatabaseWhere('codcliente', $codcliente)];
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

    protected function readOnlyAllCommonFields($viewName)
    {
        // Los campos comunes entre discrecionales y regulares = true ... esto se
        // hará siempre que sea un discrecional sin facturar. En regulares sin facturar no
        // EN discrecional o regular facturados siempre se hará
        $this->readOnlyField($viewName, 'plazas');
        $this->readOnlyField($viewName, 'idvehicle_type');
        $this->readOnlyField($viewName, 'hoja_ruta_origen');
        $this->readOnlyField($viewName, 'hoja_ruta_destino');
        $this->readOnlyField($viewName, 'hoja_ruta_expediciones');
        $this->readOnlyField($viewName, 'hoja_ruta_contratante');
        $this->readOnlyField($viewName, 'hoja_ruta_tipoidfiscal');
        $this->readOnlyField($viewName, 'hoja_ruta_cifnif');
        $this->readOnlyField($viewName, 'idservice_type');
        $this->readOnlyField($viewName, 'idempresa');
        $this->readOnlyField($viewName, 'importe');
        $this->readOnlyField($viewName, 'codimpuesto');
        $this->readOnlyField($viewName, 'importe_enextranjero');
        $this->readOnlyField($viewName, 'codimpuesto_enextranjero');
        $this->readOnlyField($viewName, 'codsubcuenta_km_nacional');
        $this->readOnlyField($viewName, 'codsubcuenta_km_extranjero');
        $this->readOnlyField($viewName, 'inicio_horaAnt');
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
        $this->readOnlyField($viewName, 'idhelper');
    }

    protected function readOnlyField($viewName, $fieldName)
    {
        $column = $this->views[$viewName]->columnForField($fieldName);
        $column->widget->readonly = 'true';
    }

    protected function readOnlyFields($viewName)
    {
        if (!empty($this->views[$viewName]->model->idfactura)) { // Está facturado el servicio
            $this->readOnlyAllCommonFields($viewName); // Da igual que sea discrecional o no, los campos comunes a readonly=true
            $this->displayOnlyFieldsForDiscretionalServ($viewName); // Se hacen invisibles los campos que sólo son para regulares
        } else {
            // No está facturado el servicio

            // Comprobamos si es discrecional o regular
            if (!empty($this->views[$viewName]->model->idservice)) {
                // Discrecional
                $this->readOnlyAllCommonFields($viewName); // Los campos comunes a readonly=true
                $this->displayOnlyFieldsForDiscretionalServ($viewName);
            } else {
                // Regular, así que las columnas comunes las dejamos como estén en la vista

                // Se dejan
                $this->displayOnlyFieldsForRegularServ($viewName);
            }
        }
    }
}