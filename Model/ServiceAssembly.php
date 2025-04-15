<?php
/**
 * This file is part of OpenServBus plugin for FacturaScripts
 * Copyright (C) 2021-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 * Copyright (C) 2021 Jerónimo Pedro Sánchez Manzano <socger@gmail.com>
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

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class ServiceAssembly extends Base\ModelClass
{
    use Base\ModelTrait;
    use OpenServBusModelTrait;

    /** @var bool */
    public $activo;

    /** @var string */
    public $codcliente;

    /** @var string */
    public $codimpuesto;

    /** @var string */
    public $codimpuesto_enextranjero;

    /** @var string */
    public $codsubcuenta_km_extranjero;

    /** @var string */
    public $codsubcuenta_km_nacional;

    /** @var string */
    public $cod_servicio;

    /** @var int */
    public $driver_alojamiento_1;

    /** @var int */
    public $driver_alojamiento_2;

    /** @var int */
    public $driver_alojamiento_3;

    /** @var string */
    public $driver_observaciones_1;

    /** @var string */
    public $driver_observaciones_2;

    /** @var string */
    public $driver_observaciones_3;

    /** @var bool */
    public $facturar_agrupando;

    /** @var bool */
    public $facturar_SN;

    /** @var string */
    public $fechaalta;

    /** @var string */
    public $fechabaja;

    /** @var string */
    public $fechamodificacion;

    /** @var string */
    public $fecha_desde;

    /** @var string */
    public $fecha_hasta;

    /** @var bool */
    public $fuera_del_municipio;

    /** @var string */
    public $hoja_ruta_contratante;

    /** @var string */
    public $hoja_ruta_destino;

    /** @var string */
    public $hoja_ruta_expediciones;

    /** @var string */
    public $hoja_ruta_origen;

    /** @var string */
    public $hoja_ruta_tipoidfiscal;

    /** @var string */
    public $hoja_ruta_cifnif;

    /** @var string */
    public $hora_anticipacion;

    /** @var string */
    public $hora_desde;

    /** @var string */
    public $hora_hasta;

    /** @var int */
    public $iddriver_1;

    /** @var int */
    public $iddriver_2;

    /** @var int */
    public $iddriver_3;

    /** @var int */
    public $idempresa;

    /** @var int */
    public $idfactura;

    /** @var int */
    public $idhelper;

    /** @var int */
    public $idservice;

    /** @var int */
    public $idservice_assembly;

    /** @var int */
    public $idvehicle;

    /** @var int */
    public $idservice_type;

    /** @var int */
    public $idservice_regular;

    /** @var int */
    public $idservice_regular_period;

    /** @var int */
    public $idvehicle_type;

    /** @var float */
    public $importe;

    /** @var float */
    public $importe_enextranjero;

    /** @var string */
    public $motivobaja;

    /** @var string */
    public $nombre;

    /** @var string */
    public $observaciones;

    /** @var string */
    public $observaciones_drivers;

    /** @var string */
    public $observaciones_facturacion;

    /** @var string */
    public $observaciones_liquidacion;

    /** @var string */
    public $observaciones_montaje;

    /** @var string */
    public $observaciones_periodo;

    /** @var string */
    public $observaciones_vehiculo;

    /** @var int */
    public $plazas;

    /** @var bool */
    public $salida_desde_nave_sn;

    /** @var float */
    public $total;

    /** @var string */
    public $useralta;

    /** @var string */
    public $userbaja;

    /** @var string */
    public $usermodificacion;

    public function clear()
    {
        parent::clear();
        $this->activo = true;
        $this->facturar_SN = true;
        $this->facturar_agrupando = true;
        $this->fechaalta = date(static::DATETIME_STYLE);
        $this->importe = 0;
        $this->importe_enextranjero = 0;
        $this->plazas = 0;
        $this->total = 0;
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public function install(): string
    {
        new VehicleType();
        new Helper();
        new ServiceType();
        new Driver();
        new Vehicle();
        new ServiceValuationType();
        new Service();
        new ServiceRegular();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idservice_assembly';
    }

    public function rellenarTotal()
    {
        $cliente_RegimenIVA = '';
        $cliente_CodRetencion = '';
        $cliente_PorcentajeRetencion = 0.0;

        $this->total = $this->importe + $this->importe_enextranjero;

        // Traemos los datos del cliente solo si hay algún importe y si hay algún tipo de impuesto
        if ($this->importe <> 0 || $this->importe_enextranjero <> 0) {
            if (!empty($this->codimpuesto) || !empty($this->codimpuesto_enextranjero)) {
                // Cargar datos del cliente que nos interesan
                $sql = ' SELECT clientes.regimeniva '
                    . ' , clientes.codretencion '
                    . ' , retenciones.porcentaje '
                    . ' FROM clientes '
                    . ' LEFT JOIN retenciones ON (retenciones.codretencion = clientes.codretencion) '
                    . ' WHERE clientes.codcliente = "' . $this->codcliente . '" ';

                $registros = self::$dataBase->select($sql);

                foreach ($registros as $fila) {
                    $cliente_RegimenIVA = $fila['regimeniva'];
                    $cliente_CodRetencion = $fila['codretencion'];
                    $cliente_PorcentajeRetencion = $fila['porcentaje'];
                }
            }
        }

        $this->calcularImpuesto($this->importe, $this->codimpuesto, $cliente_RegimenIVA, $cliente_PorcentajeRetencion, $this->total);
        $this->calcularImpuesto($this->importe_enextranjero, $this->codimpuesto_enextranjero, $cliente_RegimenIVA, $cliente_PorcentajeRetencion, $this->total);
        $this->total = round($this->total, FS_NF0);
    }

    public static function tableName(): string
    {
        return 'service_assemblies';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        $this->completarServicio();

        if ($this->checkFields() === false) {
            return false;
        }

        $this->rellenarTotal();

        $this->evitarInyeccionSQL();
        return parent::test();
    }

    protected function calcularImpuesto($importe, $codimpuesto, $cliente_RegimenIVA, $cliente_PorcentajeRetencion, &$total)
    {
        $impto_tipo = 0.0;
        $impto_IVA = 0.0;
        $impto_Recargo = 0.0;

        if ($importe <> 0) {
            if (!empty($codimpuesto)) {
                // Cargar datos del impuesto que nos interesan
                $sql = ' SELECT impuestos.tipo '
                    . ' , impuestos.iva '
                    . ' , impuestos.recargo '
                    . ' FROM impuestos '
                    . ' WHERE impuestos.codimpuesto = "' . $codimpuesto . '" ';

                $registros = self::$dataBase->select($sql);

                foreach ($registros as $fila) {
                    $impto_tipo = $fila['tipo'];
                    $impto_IVA = $fila['iva'];
                    $impto_Recargo = $fila['recargo'];
                }

                switch ($impto_tipo) {
                    case 1:
                        // calcularlo como porcentaje
                        if (!empty($impto_IVA) && trim(strtolower($cliente_RegimenIVA)) <> 'exento') {
                            $total = $total + (($importe * $impto_IVA) / 100);
                        }

                        if (!empty($impto_Recargo) && trim(strtolower($cliente_RegimenIVA)) === 'recargo') {
                            $total = $total + (($importe * $impto_Recargo) / 100);
                        }

                        break;

                    default:
                        // calcularlo como suma
                        if (!empty($impto_IVA) && trim(strtolower($cliente_RegimenIVA)) <> 'exento') {
                            $total = $total + $impto_IVA;
                        }

                        if (!empty($impto_Recargo) && trim(strtolower($cliente_RegimenIVA)) === 'recargo') {
                            $total = $total + $impto_Recargo;
                        }

                        break;
                }

                // Cálculo de las retenciones (IRPF - profesionales)
                if (!empty($cliente_CodRetencion) && !empty($cliente_PorcentajeRetencion)) {
                    if ($cliente_PorcentajeRetencion <> 0) {
                        $total = $total - (($importe * $cliente_PorcentajeRetencion) / 100);
                    }
                }

            }
        }
    }

    protected function checkFields(): bool
    {
        if (empty($this->idservice) && empty($this->idservice_regular)) {
            $this->toolBox()->i18nLog()->error('service-regular-or-discretionary');
            return false;
        }

        if (!empty($this->idservice) && !empty($this->idservice_regular)) {
            $this->toolBox()->i18nLog()->error('service-is-regular-or-discretional-bat-not-both');
            return false;
        }

        if (empty($this->iddriver_1)) {
            $this->toolBox()->i18nLog()->error('complete-driver-1');
            return false;
        }

        if (empty($this->idvehicle)) {
            $this->toolBox()->i18nLog()->error('complete-to-vehicle');
            return false;
        }

        // Comprobamos que el código se ha introducido correctamente
        if (!empty($this->cod_servicio) && 1 !== preg_match('/^[A-Z0-9_\+\.\-]{1,10}$/i', $this->cod_servicio)) {
            $this->toolBox()->i18nLog()->error(
                'invalid-alphanumeric-code',
                ['%value%' => $this->cod_servicio, '%column%' => 'cod_servicio', '%min%' => '1', '%max%' => '10']
            );
            return false;
        }

        if ($this->facturar_SN === false && $this->facturar_agrupando === true) {
            $this->toolBox()->i18nLog()->error('billing-is-no-cannot-grouping-yes');
            return false;
        }

        if (empty($this->codcliente)) {
            $this->toolBox()->i18nLog()->error('assign-service-to-customer');
            return false;
        }

        if (empty($this->nombre)) {
            $this->toolBox()->i18nLog()->error('complete-description-of-service');
            return false;
        }

        if (empty($this->hoja_ruta_origen)) {
            $this->toolBox()->i18nLog()->error('complete-origin-of-roadmap');
            return false;
        }

        if (empty($this->hoja_ruta_destino)) {
            $this->toolBox()->i18nLog()->error('complete-destination-of-roadmap');
            return false;
        }

        if (empty($this->hoja_ruta_expediciones)) {
            $this->toolBox()->i18nLog()->error('complete-expeditions-of-roadmap');
            return false;
        }

        if (empty($this->hoja_ruta_contratante)) {
            $this->toolBox()->i18nLog()->error('complete-contracting-of-roadmap');
            return false;
        }

        if (empty($this->hoja_ruta_tipoidfiscal)) {
            $this->toolBox()->i18nLog()->error('complete-fiscal-id-of-roadmap');
            return false;
        }

        if (empty($this->hoja_ruta_cifnif)) {
            $this->toolBox()->i18nLog()->error('complete-fiscal-number-of-roadmap');
            return false;
        }

        if (empty($this->idempresa)) {
            $this->toolBox()->i18nLog()->error('complete-company-performs-service');
            return false;
        }

        if (empty($this->importe)) {
            $this->toolBox()->i18nLog()->error('complete-amount-national-km');
            return false;
        }

        if (empty($this->codimpuesto)) {
            $this->toolBox()->i18nLog()->error('have-not-type-tax-national-km');
            return false;
        }

        if (empty($this->importe_enextranjero)) {
            $this->toolBox()->i18nLog()->error('complete-amount-abroad-km');
            return false;
        }

        if (empty($this->codimpuesto_enextranjero)) {
            $this->toolBox()->i18nLog()->error('have-not-type-tax-abroad-km');
            return false;
        }

        if (empty($this->inicio_dia)) {
            $this->toolBox()->i18nLog()->error('date-start-is-required');
            return false;
        }

        if (empty($this->fin_dia)) {
            $this->toolBox()->i18nLog()->error('date-end-is-required');
            return false;
        }

        if (empty($this->plazas) or $this->plazas <= 0) {
            $this->toolBox()->i18nLog()->error('complete-squares');
            return false;
        }

        $this->codsubcuenta_km_nacional = empty($this->codsubcuenta_km_nacional) ? null : $this->codsubcuenta_km_nacional;
        $this->codsubcuenta_km_extranjero = empty($this->codsubcuenta_km_extranjero) ? null : $this->codsubcuenta_km_extranjero;

        return true;
    }

    protected function completarServicio()
    {
        $campos = ' nombre, codcliente, idvehicle_type, idhelper, hoja_ruta_origen,'
            . ' hoja_ruta_destino, hoja_ruta_expediciones, fuera_del_municipio,'
            . ' hoja_ruta_contratante, hoja_ruta_tipoidfiscal, hoja_ruta_cifnif,'
            . ' idservice_type, idempresa, facturar_SN, importe, codimpuesto, '
            . ' importe_enextranjero, codimpuesto_enextranjero,'
            . ' total, codsubcuenta_km_nacional, codsubcuenta_km_extranjero,'

            . ' fecha_desde, fecha_hasta, hora_anticipacion, '
            . ' hora_desde, hora_hasta, '

            . ' salida_desde_nave_sn, idvehicle,'
            . ' iddriver_1, driver_alojamiento_1, driver_observaciones_1,'
            . ' iddriver_2, driver_alojamiento_2, driver_observaciones_2,'
            . ' iddriver_3, driver_alojamiento_3, driver_observaciones_3,'
            . ' observaciones, observaciones_montaje, observaciones_drivers,'
            . ' observaciones_vehiculo, observaciones_facturacion, observaciones_liquidacion, '
            . ' activo, fechaalta, useralta, fechamodificacion, '
            . ' fechabaja, userbaja, motivobaja, usermodificacion';


        if (!empty($this->idservice)) {
            $sql = ' SELECT ' . $campos . ', 0 as facturar_agrupando, NULL as observaciones_periodo '
                . ' FROM services '
                . ' WHERE services.idservice = ' . $this->idservice . ' ';
        } else {
            $sql = ' SELECT ' . $campos . ', facturar_agrupando, observaciones_periodo '
                . ' FROM service_regulars '
                . ' WHERE service_regulars.idservice_regular = ' . $this->idservice_regular . ' ';
        }

        $registros = self::$dataBase->select($sql);

        foreach ($registros as $fila) {
            //jerofa si es un regular no se debe de actualizar si se ha modificado a mano
            //si es un discrecional se cambia todo, pues la mayoría de estos campos estan readonly=true

            $this->codsubcuenta_km_nacional = empty($this->codsubcuenta_km_nacional) ? null : $this->codsubcuenta_km_nacional;
            $this->nombre = empty($this->idservice_regular) ? $fila['nombre'] : (empty($this->nombre) ? $fila['nombre'] : $this->nombre);
            $this->codcliente = empty($this->idservice_regular) ? $fila['codcliente'] : (empty($this->codcliente) ? $fila['codcliente'] : $this->codcliente);
            $this->idvehicle_type = empty($this->idservice_regular) ? $fila['idvehicle_type'] : (empty($this->idvehicle_type) ? $fila['idvehicle_type'] : $this->idvehicle_type);
            $this->idhelper = empty($this->idservice_regular) ? $fila['idhelper'] : (empty($this->idhelper) ? $fila['idhelper'] : $this->idhelper);
            $this->hoja_ruta_origen = empty($this->idservice_regular) ? $fila['hoja_ruta_origen'] : (empty($this->hoja_ruta_origen) ? $fila['hoja_ruta_origen'] : $this->hoja_ruta_origen);
            $this->hoja_ruta_destino = empty($this->idservice_regular) ? $fila['hoja_ruta_destino'] : (empty($this->hoja_ruta_destino) ? $fila['hoja_ruta_destino'] : $this->hoja_ruta_destino);
            $this->hoja_ruta_expediciones = empty($this->idservice_regular) ? $fila['hoja_ruta_expediciones'] : (empty($this->hoja_ruta_expediciones) ? $fila['hoja_ruta_expediciones'] : $this->hoja_ruta_expediciones);
            $this->fuera_del_municipio = empty($this->idservice_regular) ? $fila['fuera_del_municipio'] : (empty($this->fuera_del_municipio) ? $fila['fuera_del_municipio'] : $this->fuera_del_municipio);
            $this->hoja_ruta_contratante = empty($this->idservice_regular) ? $fila['hoja_ruta_contratante'] : (empty($this->hoja_ruta_contratante) ? $fila['hoja_ruta_contratante'] : $this->hoja_ruta_contratante);
            $this->hoja_ruta_tipoidfiscal = empty($this->idservice_regular) ? $fila['hoja_ruta_tipoidfiscal'] : (empty($this->hoja_ruta_tipoidfiscal) ? $fila['hoja_ruta_tipoidfiscal'] : $this->hoja_ruta_tipoidfiscal);
            $this->hoja_ruta_cifnif = empty($this->idservice_regular) ? $fila['hoja_ruta_cifnif'] : (empty($this->hoja_ruta_cifnif) ? $fila['hoja_ruta_cifnif'] : $this->hoja_ruta_cifnif);
            $this->idservice_type = empty($this->idservice_regular) ? $fila['idservice_type'] : (empty($this->idservice_type) ? $fila['idservice_type'] : $this->idservice_type);
            $this->idempresa = empty($this->idservice_regular) ? $fila['idempresa'] : (empty($this->idempresa) ? $fila['idempresa'] : $this->idempresa);
            $this->facturar_SN = empty($this->idservice_regular) ? $fila['facturar_SN'] : (empty($this->facturar_SN) ? $fila['facturar_SN'] : $this->facturar_SN);
            $this->facturar_agrupando = empty($this->idservice_regular) ? $fila['facturar_agrupando'] : (empty($this->facturar_agrupando) ? $fila['facturar_agrupando'] : $this->facturar_agrupando);
            $this->importe = empty($this->idservice_regular) ? $fila['importe'] : (empty($this->importe) ? $fila['importe'] : $this->importe);
            $this->codimpuesto = empty($this->idservice_regular) ? $fila['codimpuesto'] : (empty($this->codimpuesto) ? $fila['codimpuesto'] : $this->codimpuesto);
            $this->importe_enextranjero = empty($this->idservice_regular) ? $fila['importe_enextranjero'] : (empty($this->importe_enextranjero) ? $fila['importe_enextranjero'] : $this->importe_enextranjero);
            $this->codimpuesto_enextranjero = empty($this->idservice_regular) ? $fila['codimpuesto_enextranjero'] : (empty($this->codimpuesto_enextranjero) ? $fila['codimpuesto_enextranjero'] : $this->codimpuesto_enextranjero);
            $this->total = empty($this->idservice_regular) ? $fila['total'] : (empty($this->total) ? $fila['total'] : $this->total);
            $this->codsubcuenta_km_nacional = empty($this->idservice_regular) ? $fila['codsubcuenta_km_nacional'] : (empty($this->codsubcuenta_km_nacional) ? $fila['codsubcuenta_km_nacional'] : $this->codsubcuenta_km_nacional);
            $this->codsubcuenta_km_extranjero = empty($this->idservice_regular) ? $fila['codsubcuenta_km_extranjero'] : (empty($this->codsubcuenta_km_extranjero) ? $fila['codsubcuenta_km_extranjero'] : $this->codsubcuenta_km_extranjero);
            $this->observaciones_periodo = empty($this->idservice_regular) ? $fila['observaciones_periodo'] : (empty($this->observaciones_periodo) ? $fila['observaciones_periodo'] : $this->observaciones_periodo);
            $this->salida_desde_nave_sn = empty($this->idservice_regular) ? $fila['salida_desde_nave_sn'] : (empty($this->salida_desde_nave_sn) ? $fila['salida_desde_nave_sn'] : $this->salida_desde_nave_sn);
            $this->idvehicle = empty($this->idservice_regular) ? $fila['idvehicle'] : (empty($this->idvehicle) ? $fila['idvehicle'] : $this->idvehicle);
            $this->iddriver_1 = empty($this->idservice_regular) ? $fila['iddriver_1'] : (empty($this->iddriver_1) ? $fila['iddriver_1'] : $this->iddriver_1);
            $this->driver_alojamiento_1 = empty($this->idservice_regular) ? $fila['driver_alojamiento_1'] : (empty($this->driver_alojamiento_1) ? $fila['driver_alojamiento_1'] : $this->driver_alojamiento_1);
            $this->driver_observaciones_1 = empty($this->idservice_regular) ? $fila['driver_observaciones_1'] : (empty($this->driver_observaciones_1) ? $fila['driver_observaciones_1'] : $this->driver_observaciones_1);
            $this->iddriver_2 = empty($this->idservice_regular) ? $fila['iddriver_2'] : (empty($this->iddriver_2) ? $fila['iddriver_2'] : $this->iddriver_2);
            $this->driver_alojamiento_2 = empty($this->idservice_regular) ? $fila['driver_alojamiento_2'] : (empty($this->driver_alojamiento_2) ? $fila['driver_alojamiento_2'] : $this->driver_alojamiento_2);
            $this->driver_observaciones_2 = empty($this->idservice_regular) ? $fila['driver_observaciones_2'] : (empty($this->driver_observaciones_2) ? $fila['driver_observaciones_2'] : $this->driver_observaciones_2);
            $this->iddriver_3 = empty($this->idservice_regular) ? $fila['iddriver_3'] : (empty($this->iddriver_3) ? $fila['iddriver_3'] : $this->iddriver_3);
            $this->driver_alojamiento_3 = empty($this->idservice_regular) ? $fila['driver_alojamiento_3'] : (empty($this->driver_alojamiento_3) ? $fila['driver_alojamiento_3'] : $this->driver_alojamiento_3);

            $this->driver_observaciones_3 = empty($this->idservice_regular) ? $fila['driver_observaciones_3'] : (empty($this->driver_observaciones_3) ? $fila['driver_observaciones_3'] : $this->driver_observaciones_3);
            $this->observaciones = empty($this->idservice_regular) ? $fila['observaciones'] : (empty($this->observaciones) ? $fila['observaciones'] : $this->observaciones);
            $this->observaciones_montaje = empty($this->idservice_regular) ? $fila['observaciones_montaje'] : (empty($this->observaciones_montaje) ? $fila['observaciones_montaje'] : $this->observaciones_montaje);
            $this->observaciones_drivers = empty($this->idservice_regular) ? $fila['observaciones_drivers'] : (empty($this->observaciones_drivers) ? $fila['observaciones_drivers'] : $this->observaciones_drivers);
            $this->observaciones_vehiculo = empty($this->idservice_regular) ? $fila['observaciones_vehiculo'] : (empty($this->observaciones_vehiculo) ? $fila['observaciones_vehiculo'] : $this->observaciones_vehiculo);
            $this->observaciones_facturacion = empty($this->idservice_regular) ? $fila['observaciones_facturacion'] : (empty($this->observaciones_facturacion) ? $fila['observaciones_facturacion'] : $this->observaciones_facturacion);
            $this->observaciones_liquidacion = empty($this->idservice_regular) ? $fila['observaciones_liquidacion'] : (empty($this->observaciones_liquidacion) ? $fila['observaciones_liquidacion'] : $this->observaciones_liquidacion);
            $this->activo = empty($this->idservice_regular) ? $fila['activo'] : (empty($this->activo) ? $fila['activo'] : $this->activo);
            $this->fechaalta = empty($this->idservice_regular) ? $fila['fechaalta'] : (empty($this->fechaalta) ? $fila['fechaalta'] : $this->fechaalta);
            $this->useralta = empty($this->idservice_regular) ? $fila['useralta'] : (empty($this->useralta) ? $fila['useralta'] : $this->useralta);

            $this->fechamodificacion = empty($this->idservice_regular) ? $fila['fechamodificacion'] : (empty($this->fechamodificacion) ? $fila['fechamodificacion'] : $this->fechamodificacion);
            $this->usermodificacion = empty($this->idservice_regular) ? $fila['usermodificacion'] : (empty($this->usermodificacion) ? $fila['usermodificacion'] : $this->usermodificacion);
            $this->fechabaja = empty($this->idservice_regular) ? $fila['fechabaja'] : (empty($this->fechabaja) ? $fila['fechabaja'] : $this->fechabaja);
            $this->userbaja = empty($this->idservice_regular) ? $fila['userbaja'] : (empty($this->userbaja) ? $fila['userbaja'] : $this->userbaja);
            $this->motivobaja = empty($this->idservice_regular) ? $fila['motivobaja'] : (empty($this->motivobaja) ? $fila['motivobaja'] : $this->motivobaja);
            $this->fecha_desde = empty($this->idservice_regular) ? $fila['fecha_desde'] : (empty($this->fecha_desde) ? $fila['fecha_desde'] : $this->fecha_desde);
            $this->fecha_hasta = empty($this->idservice_regular) ? $fila['fecha_hasta'] : (empty($this->fecha_hasta) ? $fila['fecha_hasta'] : $this->fecha_hasta);
            $this->hora_anticipacion = empty($this->idservice_regular) ? $fila['hora_anticipacion'] : (empty($this->hora_anticipacion) ? $fila['hora_anticipacion'] : $this->hora_anticipacion);
            $this->hora_desde = empty($this->idservice_regular) ? $fila['hora_desde'] : (empty($this->hora_desde) ? $fila['hora_desde'] : $this->hora_desde);
            $this->hora_hasta = empty($this->idservice_regular) ? $fila['hora_hasta'] : (empty($this->hora_hasta) ? $fila['hora_hasta'] : $this->hora_hasta);

            return;
        }

        // No habían registros
        $this->toolBox()->i18nLog()->error('service-not-complete');
    }

    protected function evitarInyeccionSQL()
    {
        $utils = $this->toolBox()->utils();
        $this->nombre = $utils->noHtml($this->nombre);
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->observaciones_montaje = $utils->noHtml($this->observaciones_montaje);
        $this->observaciones_vehiculo = $utils->noHtml($this->observaciones_vehiculo);
        $this->observaciones_facturacion = $utils->noHtml($this->observaciones_facturacion);
        $this->observaciones_liquidacion = $utils->noHtml($this->observaciones_liquidacion);
        $this->observaciones_drivers = $utils->noHtml($this->observaciones_drivers);
        $this->hoja_ruta_origen = $utils->noHtml($this->hoja_ruta_origen);
        $this->hoja_ruta_destino = $utils->noHtml($this->hoja_ruta_destino);
        $this->hoja_ruta_expediciones = $utils->noHtml($this->hoja_ruta_expediciones);
        $this->hoja_ruta_contratante = $utils->noHtml($this->hoja_ruta_contratante);
        $this->hoja_ruta_tipoidfiscal = $utils->noHtml($this->hoja_ruta_tipoidfiscal);
        $this->hoja_ruta_cifnif = $utils->noHtml($this->hoja_ruta_cifnif);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        $this->codsubcuenta_km_nacional = $utils->noHtml($this->codsubcuenta_km_nacional);
        $this->codsubcuenta_km_extranjero = $utils->noHtml($this->codsubcuenta_km_extranjero);
        $this->driver_alojamiento_1 = $utils->noHtml($this->driver_alojamiento_1);
        $this->driver_observaciones_1 = $utils->noHtml($this->driver_observaciones_1);
        $this->driver_alojamiento_2 = $utils->noHtml($this->driver_alojamiento_2);
        $this->driver_observaciones_2 = $utils->noHtml($this->driver_observaciones_2);
        $this->driver_alojamiento_3 = $utils->noHtml($this->driver_alojamiento_3);
        $this->driver_observaciones_3 = $utils->noHtml($this->driver_observaciones_3);
        $this->observaciones_periodo = $utils->noHtml($this->observaciones_periodo);
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}

