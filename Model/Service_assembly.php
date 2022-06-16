<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;

class Service_assembly extends Base\ModelClass
{
    use Base\ModelTrait;

    public $idservice_assembly;

    public $user_fecha;
    public $user_nick;
    public $fechaalta;
    public $useralta;
    public $fechamodificacion;
    public $usermodificacion;

    public $activo;
    public $activo_text;

    public $fechabaja;
    public $userbaja;
    public $motivobaja;

    public $cod_servicio;
    public $idservice_regular;
    public $idservice;

    public $nombre;
    public $plazas;

    public $codcliente;
    public $idvehicle_type;
    public $idhelper;

    public $facturar_SN;
    public $facturar_SN_text;

    public $facturar_agrupando;
    public $facturar_agrupando_text;

    public $importe;
    public $importe_enextranjero;
    public $codimpuesto;
    public $codimpuesto_enextranjero;
    public $total;

    public $fuera_del_municipio;
    public $fuera_del_municipio_text;

    public $hoja_ruta_origen;
    public $hoja_ruta_destino;
    public $hoja_ruta_expediciones;
    public $hoja_ruta_contratante;
    public $hoja_ruta_tipoidfiscal;
    public $hoja_ruta_cifnif;

    public $idservice_type;
    public $idempresa;

    public $observaciones;
    public $observaciones_montaje;
    public $observaciones_vehiculo;
    public $observaciones_facturacion;
    public $observaciones_liquidacion;
    public $observaciones_drivers;

    public $iddriver_1;
    public $driver_alojamiento_1;
    public $driver_observaciones_1;

    public $iddriver_2;
    public $driver_alojamiento_2;
    public $driver_observaciones_2;

    public $iddriver_3;
    public $driver_alojamiento_3;
    public $driver_observaciones_3;

    public $idvehicle;

    public $codsubcuenta_km_nacional;
    public $codsubcuenta_km_extranjero;

    public $idservice_regular_period;
    public $fecha_desde;
    public $fecha_hasta;

    public $hora_anticipacion;
    public $hora_desde;
    public $hora_hasta;

    public $inicio_horaAnt;

    public $salida_desde_nave_sn;
    public $salida_desde_nave_text;

    public $observaciones_periodo;
    public $idfactura;

    // función que inicializa algunos valores antes de la vista del controlador
    public function clear()
    {
        parent::clear();

        $this->activo = true; // Por defecto estará activo

        $this->facturar_SN = true;
        $this->facturar_agrupando = true;

        $this->importe = 0;
        $this->importe_enextranjero = 0;
        $this->total = 0;
        $this->plazas = 0;
    }

    public function install(): string
    {
        // needed dependencies
        new Vehicle_type();
        new Helper();
        new Service_type();
        new Driver();
        new Vehicle();
        new Service_valuation_type();
        new Service();
        new Service_regular();

        return parent::install();
    }

    // función que devuelve el id principal
    public static function primaryColumn(): string
    {
        return 'idservice_assembly';
    }

    // función que devuelve el nombre de la tabla
    public static function tableName(): string
    {
        return 'service_assemblies';
    }

    // Para realizar cambios en los datos antes de guardar por modificación
    protected function saveUpdate(array $values = []): bool
    {
        $this->rellenarDatosModificacion();

        if ($this->comprobarSiActivo() == false) {
            return false;
        }

        return parent::saveUpdate($values);
    }

    // Para realizar cambios en los datos antes de guardar por alta
    protected function saveInsert(array $values = []): bool
    {
        // Creamos el nuevo id
        if (empty($this->idservice_regular)) {
            $this->idservice_regular = $this->newCode();
        }

        $this->rellenarDatosAlta();
        $this->rellenarDatosModificacion();

        if ($this->comprobarSiActivo() == false) {
            return false;
        }

        return parent::saveInsert($values);
    }

    public function test(): bool
    {
        $this->completarServicio();

        if ($this->checkFields() === false) {
            return false;
        }

        $this->rellenarTotal();

        $this->evitarInyeccionSQL();
        return parent::test();
    }


    // ** ********************************** ** //
    // ** FUNCIONES CREADAS PARA ESTE MODELO ** //
    // ** ********************************** ** //
    private function comprobarSiActivo()
    {
        $a_devolver = true;

        if ($this->activo == false) {
            $this->fechabaja = $this->fechamodificacion;
            $this->userbaja = $this->usermodificacion;

            if (empty($this->motivobaja)) {
                $a_devolver = false;
                $this->toolBox()->i18nLog()->error('Si el registro no está activo, debe especificar el motivo.');
            }
        } else { // Por si se vuelve a poner Activo = true
            $this->fechabaja = null;
            $this->userbaja = null;
            $this->motivobaja = null;
        }
        return $a_devolver;
    }

    private function rellenarDatosModificacion()
    {
        $this->usermodificacion = $this->user_nick;
        $this->fechamodificacion = $this->user_fecha;
    }

    private function rellenarDatosAlta()
    {
        $this->useralta = $this->user_nick;
        $this->fechaalta = $this->user_fecha;
    }

    private function comprobarFacturacion()
    {
        if ($this->facturar_SN === false && $this->facturar_agrupando === true) {
            $this->toolBox()->i18nLog()->error('Si elige FACTURAR = NO, no puede elegir AGRUPANDO = SI.');
            return false;
        }
        return true;
    }

    private function evitarInyeccionSQL()
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
        $this->activo_text = $utils->noHtml($this->activo_text);
        $this->observaciones_periodo = $utils->noHtml($this->observaciones_periodo);
    }

    private function comprobarVehiculo()
    {
        if (empty($this->idvehicle)) {
            $this->toolBox()->i18nLog()->error('Debe de completar el vehículo.');
            return false;
        }
        return true;
    }

    private function comprobarConductor_1()
    {
        if (empty($this->iddriver_1)) {
            $this->toolBox()->i18nLog()->error('Debe de completar el conductor 1.');
            return false;
        }
        return true;
    }

    private function calcularImpuesto($importe, $codimpuesto, $cliente_RegimenIVA, $cliente_PorcentajeRetencion, &$total)
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

                $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

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

    public function rellenarTotal()
    {
        $cliente_RegimenIVA = '';
        $cliente_CodRetencion = '';
        $cliente_PorcentajeRetencion = 0.0;

        $this->total = $this->importe + $this->importe_enextranjero;

        // Traemos los datos del cliente sólo si hay algún importe y si hay algún tipo de impuesto
        if ($this->importe <> 0 || $this->importe_enextranjero <> 0) {
            if (!empty($this->codimpuesto) || !empty($this->codimpuesto_enextranjero)) {
                // Cargar datos del cliente que nos interesan
                $sql = ' SELECT clientes.regimeniva '
                    . ' , clientes.codretencion '
                    . ' , retenciones.porcentaje '
                    . ' FROM clientes '
                    . ' LEFT JOIN retenciones ON (retenciones.codretencion = clientes.codretencion) '
                    . ' WHERE clientes.codcliente = "' . $this->codcliente . '" ';

                $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

                foreach ($registros as $fila) {
                    $cliente_RegimenIVA = $fila['regimeniva'];
                    $cliente_CodRetencion = $fila['codretencion'];
                    $cliente_PorcentajeRetencion = $fila['porcentaje'];
                }
            }
        }

        $this->calcularImpuesto($this->importe, $this->codimpuesto, $cliente_RegimenIVA, $cliente_PorcentajeRetencion, $this->total);
        $this->calcularImpuesto($this->importe_enextranjero, $this->codimpuesto_enextranjero, $cliente_RegimenIVA, $cliente_PorcentajeRetencion, $this->total);

        $this->total = round($this->total, (int)\FS_NF0);

    }

    private function completarServicio()
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

        $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

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
        $this->toolBox()->i18nLog()->error('No se pudo completar el servicio. Compruebe que el servicio existe o que no haya sido borrado.');
        return;
    }

    private function checkFields()
    {
        $aDevolver = true;

        if (empty($this->idservice) && empty($this->idservice_regular)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe elegir si es un servicio regular o es un servicio discrecional.');
        }

        if (!empty($this->idservice) && !empty($this->idservice_regular)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('O es un servicio regular o es un servicio discrecional. Pero no ambos');
        }

        if ($this->comprobarConductor_1() == false) {
            $aDevolver = false;
        }
        if ($this->comprobarVehiculo() == false) {
            $aDevolver = false;
        }

        // Comprobamos que el código se ha introducido correctamente
        if (!empty($this->cod_servicio) && 1 !== preg_match('/^[A-Z0-9_\+\.\-]{1,10}$/i', $this->cod_servicio)) {
            $this->toolBox()->i18nLog()->error(
                'invalid-alphanumeric-code',
                ['%value%' => $this->cod_servicio, '%column%' => 'cod_servicio', '%min%' => '1', '%max%' => '10']
            );
            $aDevolver = false;
        }

        if ($this->comprobarFacturacion() == false) {
            $aDevolver = false;
        }

        if (empty($this->codcliente)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe de asignar el servicio a un cliente.');
        }

        if (empty($this->nombre)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar la descripción del servicio.');
        }

        if (empty($this->hoja_ruta_origen)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar el origen de la Hoja de Ruta.');
        }

        if (empty($this->hoja_ruta_destino)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar el destino de la Hoja de Ruta.');
        }

        if (empty($this->hoja_ruta_expediciones)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar las expediciones de la Hoja de Ruta.');
        }

        if (empty($this->hoja_ruta_contratante)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar el contratante de la Hoja de Ruta.');
        }

        if (empty($this->hoja_ruta_tipoidfiscal)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar Id. Fiscal de la Hoja de Ruta.');
        }

        if (empty($this->hoja_ruta_cifnif)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar el Num. Fiscal de la Hoja de Ruta.');
        }

        if (empty($this->idempresa)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar la empresa que realiza el servicio.');
        }

        if (empty($this->importe)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar el Importe x km nacional.');
        }

        if (empty($this->codimpuesto)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('No ha elegido el tipo de impuesto para "Importe x km nacional".');
        }

        if (empty($this->importe_enextranjero)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe completar el Importe x km en extranjero.');
        }

        if (empty($this->codimpuesto_enextranjero)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('No ha elegido el tipo de impuesto para "Importe x km en extrajero".');
        }

        if (empty($this->inicio_dia)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('No ha elegido la fecha de inicio del servicio.');
        }

        if (empty($this->fin_dia)) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('No ha elegido la fecha de fin del servicio.');
        }

        if (empty($this->plazas) or $this->plazas <= 0) {
            $aDevolver = false;
            $this->toolBox()->i18nLog()->error('Debe de completar las plazas.');
        }

        $this->codsubcuenta_km_nacional = empty($this->codsubcuenta_km_nacional) ? null : $this->codsubcuenta_km_nacional;
        $this->codsubcuenta_km_extranjero = empty($this->codsubcuenta_km_extranjero) ? null : $this->codsubcuenta_km_extranjero;

        return $aDevolver;
    }

}

