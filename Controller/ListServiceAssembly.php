<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListServiceAssembly extends ListController
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Montaje de servicios';
        $pageData['icon'] = 'fas fa-business-time';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewAssembly();
    }

    protected function createViewAssembly($viewName = 'ListServiceAssembly')
    {
        $this->addView($viewName, 'ServiceAssembly', 'Montaje de servicios', 'fas fa-business-time');
        $this->addSearchFields($viewName, ['nombre']);
        $this->addOrderBy($viewName, ['nombre'], 'Nombre');
        $this->addOrderBy($viewName, ['codcliente'], 'Cliente');
        $this->addOrderBy($viewName, ['fecha_desde', 'fecha_hasta'], 'F.inicio + F.fin', 1);
        $this->addOrderBy($viewName, ['fechaalta', 'fechamodificacion'], 'F.Alta + F.MOdif.');

        // Filtros
        $this->addFilterPeriod($viewName, 'porFechaInicio', 'F.inicio', 'fecha_desde');
        $this->addFilterPeriod($viewName, 'porFechaFin', 'F.fin', 'fecha_hasta');

        $activo = [
            ['code' => '1', 'description' => 'Activos = SI'],
            ['code' => '0', 'description' => 'Activos = NO'],
        ];
        $this->addFilterSelect($viewName, 'soloActivos', 'Activos = TODOS', 'activo', $activo);

        $crearFtraSN = [
            ['code' => '1', 'description' => 'Facturable = SI'],
            ['code' => '0', 'description' => 'Facturable = NO'],
        ];
        $this->addFilterSelect($viewName, 'crearFtra', 'Crear ftra. = TODOS', 'facturar_SN', $crearFtraSN);

        $facturarAgrupandoSN = [
            ['code' => '1', 'description' => 'Ftra.agrupando = SI'],
            ['code' => '0', 'description' => 'Ftra.agrupando = NO'],
        ];
        $this->addFilterSelect($viewName, 'facturarAgrupando', 'Ftra.agrupando = TODOS', 'facturar_agrupando', $facturarAgrupandoSN);

        $this->addFilterAutocomplete($viewName, 'xCodCliente', 'Cliente', 'codcliente', 'clientes', 'codcliente', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdvehicle_type', 'Vehículo - tipo', 'idvehicle_type', 'vehicle_types', 'idvehicle_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdservice_type', 'Servicio - tipo', 'idservice_type', 'service_types', 'idservice_type', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdempresa', 'Empresa', 'idempresa', 'empresas', 'idempresa', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdservice', 'service-discretionary', 'idservice', 'services', 'idservice', 'nombre');
        $this->addFilterAutocomplete($viewName, 'xIdserviceRegular', 'service-regular', 'idservice_regular', 'service_regulars', 'idservice_regular', 'nombre');
    }

    protected function execPreviousAction($action): bool
    {
        if ($action === 'gen-assemblies') {
            return $this->generateAssembliesAction();
        }

        return parent::execPreviousAction($action);
    }

    protected function generateAssembliesAction(): bool
    {
        // Nos traemos todos los campos del form modal
        $form = $this->request->request->all();

        // Comprobamos si han introducido la fecha del montaje
        if (empty($form["date_assembly"])) {
            $this->toolBox()->i18nLog()->warning('No ha facilitado la fecha de montaje');
            return true;
        }

        // Insertamos a tabla de montaje los servicios regulares que todavía no existan
        $sql = ' INSERT INTO service_assemblies ( '
            . '  cod_servicio '
            . ', idservice_regular '
            . ', fechaalta '
            . ', useralta '
            . ', fechamodificacion '
            . ', usermodificacion '
            . ', activo '
            . ', fechabaja '
            . ', userbaja '
            . ', motivobaja '
            . ', nombre '
            . ', plazas '
            . ', codcliente '
            . ', idvehicle_type '
            . ', idhelper '
            . ', facturar_SN '
            . ', facturar_agrupando '
            . ', importe '
            . ', importe_enextranjero '
            . ', codimpuesto '
            . ', codimpuesto_enextranjero '
            . ', total '
            . ', fuera_del_municipio '
            . ', hoja_ruta_origen '
            . ', hoja_ruta_destino '
            . ', hoja_ruta_expediciones '
            . ', hoja_ruta_contratante '
            . ', hoja_ruta_tipoidfiscal '
            . ', hoja_ruta_cifnif '
            . ', idservice_type '
            . ', idempresa '
            . ', observaciones '
            . ', observaciones_montaje '
            . ', observaciones_vehiculo '
            . ', observaciones_facturacion '
            . ', observaciones_liquidacion '
            . ', observaciones_drivers '
            . ', iddriver_1 '
            . ', driver_alojamiento_1 '
            . ', driver_observaciones_1 '
            . ', iddriver_2 '
            . ', driver_alojamiento_2 '
            . ', driver_observaciones_2 '
            . ', iddriver_3 '
            . ', driver_alojamiento_3 '
            . ', driver_observaciones_3 '
            . ', idvehicle '
            . ', codsubcuenta_km_nacional '
            . ', codsubcuenta_km_extranjero '
            . ', idservice_regular_period '
            . ', fecha_desde '
            . ', fecha_hasta '
            . ', hora_anticipacion '
            . ', hora_desde '
            . ', hora_hasta '
            . ', salida_desde_nave_sn '
            . ', observaciones_periodo ) '

            . ' SELECT service_regulars.cod_servicio '
            . ' , service_regulars.idservice_regular '
            . ' , service_regulars.fechaalta '
            . ' , service_regulars.useralta '
            . ' , service_regulars.fechamodificacion '
            . ' , service_regulars.usermodificacion '
            . ' , service_regulars.activo '
            . ' , service_regulars.fechabaja '
            . ' , service_regulars.userbaja '
            . ' , service_regulars.motivobaja '
            . ' , service_regulars.nombre '
            . ' , service_regulars.plazas '
            . ' , service_regulars.codcliente '
            . ' , service_regulars.idvehicle_type '
            . ' , service_regulars.idhelper '
            . ' , service_regulars.facturar_SN '
            . ' , service_regulars.facturar_agrupando '
            . ' , service_regulars.importe '
            . ' , service_regulars.importe_enextranjero '
            . ' , service_regulars.codimpuesto '
            . ' , service_regulars.codimpuesto_enextranjero '
            . ' , service_regulars.total '
            . ' , service_regulars.fuera_del_municipio '
            . ' , service_regulars.hoja_ruta_origen '
            . ' , service_regulars.hoja_ruta_destino '
            . ' , service_regulars.hoja_ruta_expediciones '
            . ' , service_regulars.hoja_ruta_contratante '
            . ' , service_regulars.hoja_ruta_tipoidfiscal '
            . ' , service_regulars.hoja_ruta_cifnif '
            . ' , service_regulars.idservice_type '
            . ' , service_regulars.idempresa '
            . ' , service_regulars.observaciones '
            . ' , service_regulars.observaciones_montaje '
            . ' , service_regulars.observaciones_vehiculo '
            . ' , service_regulars.observaciones_facturacion '
            . ' , service_regulars.observaciones_liquidacion '
            . ' , service_regulars.observaciones_drivers '
            . ' , service_regulars.iddriver_1 '
            . ' , service_regulars.driver_alojamiento_1 '
            . ' , service_regulars.driver_observaciones_1 '
            . ' , service_regulars.iddriver_2 '
            . ' , service_regulars.driver_alojamiento_2 '
            . ' , service_regulars.driver_observaciones_2 '
            . ' , service_regulars.iddriver_3 '
            . ' , service_regulars.driver_alojamiento_3 '
            . ' , service_regulars.driver_observaciones_3 '
            . ' , service_regulars.idvehicle '
            . ' , service_regulars.codsubcuenta_km_nacional '
            . ' , service_regulars.codsubcuenta_km_extranjero '
            . ' , service_regulars.idservice_regular_period '

            . ' , "' . $form["date_assembly"] . '" ' // service_regulars.fecha_desde
            . ' , "' . $form["date_assembly"] . '" ' // service_regulars.fecha_hasta

            . ' , service_regulars.hora_anticipacion '
            . ' , service_regulars.hora_desde '
            . ' , service_regulars.hora_hasta '
            . ' , service_regulars.salida_desde_nave_sn '
            . ' , service_regulars.observaciones_periodo '

            . ' FROM service_regular_periods '
            . ' LEFT JOIN service_regulars ON ( service_regulars.idservice_regular = service_regular_periods.idservice_regular '
            . '  AND service_regulars.activo = 1 )  '
            . ' WHERE service_regular_periods.activo = 1 '
            . ' AND service_regular_periods.fecha_desde <= "' . $form["date_assembly"] . '" '
            . ' AND service_regular_periods.fecha_hasta >= "' . $form["date_assembly"] . '" '
            . ' AND NOT EXISTS ( SELECT 1 '
            . ' FROM service_assemblies '
            . ' WHERE service_assemblies.idservice_regular = service_regular_periods.idservice_regular '
            . ' AND service_assemblies.fecha_desde = "' . $form["date_assembly"] . '" '
            . ' AND service_assemblies.fecha_hasta = "' . $form["date_assembly"] . '" ) ';

        $this->dataBase->exec($sql);

        // Insertamos a tabla de montaje los servicios discrecionales que todavía no existan
        $sql = ' INSERT INTO service_assemblies ( '
            . '  idservice '
            . ', fechaalta '
            . ', useralta '
            . ', fechamodificacion '
            . ', usermodificacion '
            . ', activo '
            . ', fechabaja '
            . ', userbaja '
            . ', motivobaja '
            . ', nombre '
            . ', plazas '
            . ', codcliente '
            . ', idvehicle_type '
            . ', idhelper '
            . ', facturar_SN '
            . ', facturar_agrupando '
            . ', importe '
            . ', importe_enextranjero '
            . ', codimpuesto '
            . ', codimpuesto_enextranjero '
            . ', total '
            . ', fuera_del_municipio '
            . ', hoja_ruta_origen '
            . ', hoja_ruta_destino '
            . ', hoja_ruta_expediciones '
            . ', hoja_ruta_contratante '
            . ', hoja_ruta_tipoidfiscal '
            . ', hoja_ruta_cifnif '
            . ', idservice_type '
            . ', idempresa '
            . ', observaciones '
            . ', observaciones_montaje '
            . ', observaciones_vehiculo '
            . ', observaciones_facturacion '
            . ', observaciones_liquidacion '
            . ', observaciones_drivers '
            . ', iddriver_1 '
            . ', driver_alojamiento_1 '
            . ', driver_observaciones_1 '
            . ', iddriver_2 '
            . ', driver_alojamiento_2 '
            . ', driver_observaciones_2 '
            . ', iddriver_3 '
            . ', driver_alojamiento_3 '
            . ', driver_observaciones_3 '
            . ', idvehicle '
            . ', codsubcuenta_km_nacional '
            . ', codsubcuenta_km_extranjero '
            . ', fecha_desde '
            . ', fecha_hasta '
            . ', hora_anticipacion '
            . ', hora_desde '
            . ', hora_hasta '
            . ', salida_desde_nave_sn ) '

            . ' SELECT services.idservice '

            . ' , services.fechaalta '
            . ' , services.useralta '
            . ' , services.fechamodificacion '
            . ' , services.usermodificacion '
            . ' , services.activo '
            . ' , services.fechabaja '
            . ' , services.userbaja '
            . ' , services.motivobaja '
            . ' , services.nombre '
            . ' , services.plazas '
            . ' , services.codcliente '
            . ' , services.idvehicle_type '
            . ' , services.idhelper '
            . ' , services.facturar_SN '
            . ' , 0 ' // services.facturar_agrupando
            . ' , services.importe '
            . ' , services.importe_enextranjero '
            . ' , services.codimpuesto '
            . ' , services.codimpuesto_enextranjero '
            . ' , services.total '
            . ' , services.fuera_del_municipio '
            . ' , services.hoja_ruta_origen '
            . ' , services.hoja_ruta_destino '
            . ' , services.hoja_ruta_expediciones '
            . ' , services.hoja_ruta_contratante '
            . ' , services.hoja_ruta_tipoidfiscal '
            . ' , services.hoja_ruta_cifnif '
            . ' , services.idservice_type '
            . ' , services.idempresa '
            . ' , services.observaciones '
            . ' , services.observaciones_montaje '
            . ' , services.observaciones_vehiculo '
            . ' , services.observaciones_facturacion '
            . ' , services.observaciones_liquidacion '
            . ' , services.observaciones_drivers '
            . ' , services.iddriver_1 '
            . ' , services.driver_alojamiento_1 '
            . ' , services.driver_observaciones_1 '
            . ' , services.iddriver_2 '
            . ' , services.driver_alojamiento_2 '
            . ' , services.driver_observaciones_2 '
            . ' , services.iddriver_3 '
            . ' , services.driver_alojamiento_3 '
            . ' , services.driver_observaciones_3 '
            . ' , services.idvehicle '
            . ' , services.codsubcuenta_km_nacional '
            . ' , services.codsubcuenta_km_extranjero '

            . ' , services.fecha_desde '
            . ' , services.fecha_hasta '

            . ' , services.hora_anticipacion '
            . ' , services.hora_desde '
            . ' , services.hora_hasta '
            . ' , services.salida_desde_nave_sn '

            . ' FROM services '
            . ' WHERE services.activo = 1 '
            . ' AND services.fecha_desde <= "' . $form["date_assembly"] . '" '
            . ' AND services.fecha_hasta >= "' . $form["date_assembly"] . '" '
            . ' AND NOT EXISTS ( SELECT 1 '
            . ' FROM service_assemblies '
            . ' WHERE service_assemblies.idservice = services.idservice ) ';

        $this->dataBase->exec($sql);

        $this->toolBox()->i18nLog()->notice('items-added-correctly');
        return true;
    }
}