<?php
/**
 * This file is part of OpenServBus plugin for FacturaScripts
 * Copyright (C) 2022-2023 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\Calculator;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Dinamic\Model\AlbaranCliente;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class EditSubReservaTour extends EditController
{
    use OpenServBusControllerTrait;

    public function getModelClassName(): string
    {
        return "SubReservaTour";
    }

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "underbook";
        $data["icon"] = "fas fa-calendar-alt";
        return $data;
    }

    protected function createAlbaran(string $action)
    {
        // abrimos la transacción
        $newTransaction = $this->dataBase->inTransaction();
        if (false === $newTransaction) {
            $newTransaction = true;
            $this->dataBase->beginTransaction();
        }

        // obtenemos la subreserva
        $subreserva = $this->getModel();
        $subreserva->loadFromCode($this->request->get('code'));
        if (false === $subreserva->exists()) {
            $this->toolBox()->i18nLog()->warning('record-not-found');
            if ($newTransaction) {
                $this->dataBase->rollback();
            }
            return;
        }

        // buscamos los productos de la subreserva
        $productos = $subreserva->getProductos();
        if (empty($productos)) {
            $this->toolBox()->i18nLog()->warning('no-products-found');
            if ($newTransaction) {
                $this->dataBase->rollback();
            }
            return;
        }

        // obtenemos el cliente de la reserva o de la subreserva
        $cliente = $action === 'albaran-reserva' ? $subreserva->getReserva()->getCliente() : $subreserva->getCliente();
        if (false === $cliente->exists()) {
            $this->toolBox()->i18nLog()->warning('no-customer-found');
            if ($newTransaction) {
                $this->dataBase->rollback();
            }
            return;
        }

        // creamos el albarán
        $albaran = new AlbaranCliente();
        $albaran->setSubject($cliente);
        $albaran->setAuthor($this->user);
        if (false === $albaran->save()) {
            $this->toolBox()->i18nLog()->warning('record-save-error');
            if ($newTransaction) {
                $this->dataBase->rollback();
            }
            return;
        }

        // añadimos los productos al albarán
        foreach ($productos as $producto) {
            $variante = $producto->getVariante();
            if (false === $variante->exists()) {
                $this->toolBox()->i18nLog()->warning('no-product-found');
                if ($newTransaction) {
                    $this->dataBase->rollback();
                }
                return;
            }

            $newlinea = $albaran->getNewProductLine($variante->referencia);
            $newlinea->cantidad = $producto->cantidad;
            if (false === empty($producto->descripcion)) {
                $newlinea->descripcion = $producto->descripcion;
            }

            if (false === $newlinea->save()) {
                $this->toolBox()->i18nLog()->warning('record-save-error');
                if ($newTransaction) {
                    $this->dataBase->rollback();
                }
                return;
            }
        }

        // recalculamos el albarán
        $lines = $albaran->getLines();
        if (false === Calculator::calculate($albaran, $lines, true)) {
            $this->toolBox()->i18nLog()->warning('record-save-error');
            if ($newTransaction) {
                $this->dataBase->rollback();
            }
            return;
        }

        // guardamos el idalbaran
        $model = $subreserva;
        if ($action === 'albaran-reserva') {
            $model = $subreserva->getReserva();
        }
        $model->idalbaran = $albaran->idalbaran;
        if (false === $model->save()) {
            $this->toolBox()->i18nLog()->warning('record-save-error');
            if ($newTransaction) {
                $this->dataBase->rollback();
            }
            return;
        }

        if ($newTransaction) {
            $this->dataBase->commit();
        }
    }

    protected function createViews()
    {
        parent::createViews();
        $this->setSettings('EditSubReservaTour', 'btnNew', false);
        $this->createViewsServicioTour();
        $this->createViewsProducts();
        $this->setTabsPosition('bottom');
    }

    protected function createViewsProducts(string $viewName = "EditSubReservaTourProduct")
    {
        $this->addEditListView($viewName, "SubReservaTourProduct", "products", "fas fa-cubes");
    }

    protected function createViewsServicioTour(string $viewName = "ListServicioTour")
    {
        $this->addListView($viewName, "ServicioTour", "services", "fas fa-concierge-bell");
        $this->views[$viewName]->addOrderBy(["id"], "code", 2);
        $this->views[$viewName]->addOrderBy(["pickupdate", "pickuptime"], "pick-up-date");
        $this->views[$viewName]->addOrderBy(["destinationdate", "destinationtime"], "destination-date");
        $this->views[$viewName]->addSearchFields(["id", "routecode", "routename", "pickupflightid", "destinationflightid", "pickuplocation", "pickuppoint", "destinationpoint"]);

        // ocultar columnas
        $this->views[$viewName]->disableColumn('underbook', true);

        // Filtros
        $serviceTypes = $this->codeModel->all('service_types', 'idservice_type', 'nombre');
        $this->views[$viewName]->addFilterSelect('idtiposervicio', 'service-type', 'idtiposervicio', $serviceTypes);

        $status = $this->codeModel->all('tour_servicios_estados', 'id', 'name');
        $this->views[$viewName]->addFilterSelect('idestado', 'status', 'idestado', $status);

        $serviceDiscrecional = $this->codeModel->all('services', 'idservice', 'nombre');
        $this->views[$viewName]->addFilterSelect('idserviciodiscrecional', 'service-discretionary', 'idserviciodiscrecional', $serviceDiscrecional);

        $serviceRegular = $this->codeModel->all('service_regulars', 'idservice_regular', 'nombre');
        $this->views[$viewName]->addFilterSelect('idservicioregular', 'service-regular', 'idservicioregular', $serviceRegular);

        // asignamos los colores
        $this->addColorStatusService($viewName);
    }

    protected function execAfterAction($action)
    {
        if (in_array($action, ['albaran-reserva', 'albaran-subreserva'])) {
            $this->createAlbaran($action);
        }
        parent::execAfterAction($action);
    }

    protected function loadData($viewName, $view)
    {
        $mvn = $this->getMainViewName();
        switch ($viewName) {
            case 'EditSubReservaTourProduct':
            case 'ListServicioTour':
                $idsubreserva = $this->getViewModelValue($mvn, 'id');
                $where = [new DatabaseWhere('idsubreserva', $idsubreserva)];
                $view->loadData('', $where);
                break;

            default:
                parent::loadData($viewName, $view);
                if (empty($view->model->idalbaran)) {
                    $this->addButton($viewName, [
                        'action' => 'albaran-reserva',
                        'color' => 'warning',
                        'icon' => 'fas fa-dolly-flatbed',
                        'label' => 'delivery-note-booking',
                        'type' => 'action'
                    ]);
                    $this->addButton($viewName, [
                        'action' => 'albaran-subreserva',
                        'color' => 'warning',
                        'icon' => 'fas fa-dolly-flatbed',
                        'label' => 'delivery-note-underbook',
                        'type' => 'action'
                    ]);
                } else {
                    $this->setSettings('EditSubReservaTour', 'btnSave', false);
                    $this->setSettings('EditSubReservaTour', 'btnUndo', false);
                    $this->setSettings('ListServicioTour', 'btnNew', false);
                    $this->setSettings('ListServicioTour', 'btnDelete', false);
                    $this->setSettings('ListServicioTour', 'checkBoxes', false);
                    $this->setSettings('EditSubReservaTourProduct', 'btnNew', false);
                    $this->setSettings('EditSubReservaTourProduct', 'btnSave', false);
                    $this->setSettings('EditSubReservaTourProduct', 'btnUndo', false);
                    $this->setSettings('EditSubReservaTourProduct', 'btnDelete', false);
                }
                break;
        }
    }
}
