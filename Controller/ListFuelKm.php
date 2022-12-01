<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListFuelKm extends ListController
{

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Repostajes / kms';
        $pageData['icon'] = 'fas fa-gas-pump';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewFuelKm();
        $this->createViewFuel_pump();
    }

    protected function createViewFuelKm($viewName = 'ListFuelKm')
    {
        $this->addView($viewName, 'FuelKm', 'Repostajes / kms', 'fas fa-gas-pump');
        $this->addOrderBy($viewName, ['fecha'], 'Fecha', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $this->addFilterAutocomplete($viewName, 'xIdEmpresa', 'Empresa', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdVehicle', 'Vehículo', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdFuel_Type', 'Combustible', 'idfuel_type', 'fuel_types', 'idfuel_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdFuel_Pumps', 'Surtidor Interno', 'idfuel_pump', 'fuel_pumps', 'idfuel_pump', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdEmployee', 'Empleado', 'idemployee', 'employees', 'idemployee', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xCodProveedor', 'Proveedor', 'codproveedor', 'proveedores', 'codproveedor', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdTarjeta', 'Tarjeta', 'idtarjeta', 'tarjetas', 'idtarjeta', 'nombre');
        $this->addFilterPeriod($viewName, 'porFecha', 'Fecha repostaje', 'fecha');

        $esDepositoLleno = [
            ['code' => '1', 'description' => 'Depósito lleno = SI'],
            ['code' => '0', 'description' => 'Depósito lleno = NO'],
        ];
        $this->addFilterSelect($viewName, 'esDepositoLleno', 'Depósito lleno = TODO', 'deposito_lleno', $esDepositoLleno);
    }

    protected function createViewFuel_pump($viewName = 'ListFuelPump')
    {
        $this->addView($viewName, 'FuelPump', 'Surtidores interno', 'fas fa-thumbtack');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta+F.MOdif.');

        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);
    }
}