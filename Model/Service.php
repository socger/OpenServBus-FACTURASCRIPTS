<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class Service extends Base\ModelClass
{
    use Base\ModelTrait;
    use OpenServBusModelTrait;

    /** @var bool */
    public $activo;

    /** @var bool */
    public $aceptado;

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

    /** @var int */
    public $driver_alojamiento_1;

    /** @var int */
    public $driver_alojamiento_2;

    /** @var int */
    public $driver_alojamiento_3;

    /** @var int */
    public $driver_observaciones_1;

    /** @var int */
    public $driver_observaciones_2;

    /** @var int */
    public $driver_observaciones_3;

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
    public $hora_anticipacion;

    /** @var string */
    public $hora_desde;

    /** @var string */
    public $hora_hasta;

    /** @var string */
    public $hoja_ruta_cifnif;

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

    /** @var float */
    public $importe;

    /** @var float */
    public $importe_enextranjero;

    /** @var int */
    public $idservice;

    /** @var int */
    public $idservice_type;

    /** @var int */
    public $idvehicle;

    /** @var int */
    public $idvehicle_type;

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
        $this->aceptado = false;
        $this->activo = true;
        $this->facturar_SN = true;
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
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idservice';
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

        $this->total = round($this->total, FS_NF0);
    }

    public static function tableName(): string
    {
        return 'services';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        if ($this->checkFields() === false) {
            return false;
        }

        $this->rellenarTotal();

        $this->evitarInyeccionSQL();
        return parent::test();
    }

    protected function actualizarServicioEnMontaje()
    {
        // Actualizamos el servicio discrecional en montaje de servicios
        $sql = ' UPDATE service_assemblies AS S1, services AS S2 '
            . ' SET S1.nombre = S2.nombre '
            . ', S1.fechaalta = S2.fechaalta '
            . ', S1.useralta = S2.useralta '
            . ', S1.fechamodificacion = S2.fechamodificacion '
            . ', S1.usermodificacion = S2.usermodificacion '
            . ', S1.activo = S2.activo '
            . ', S1.fechabaja = S2.fechabaja '
            . ', S1.userbaja = S2.userbaja '
            . ', S1.motivobaja = S2.motivobaja '
            . ', S1.plazas = S2.plazas '
            . ', S1.codcliente = S2.codcliente '
            . ', S1.idvehicle_type = S2.idvehicle_type '
            . ', S1.idhelper = S2.idhelper '
            . ', S1.facturar_SN = S2.facturar_SN '
            . ', S1.facturar_agrupando = 0 '
            . ', S1.importe = S2.importe '
            . ', S1.importe_enextranjero = S2.importe_enextranjero '
            . ', S1.codimpuesto = S2.codimpuesto '
            . ', S1.codimpuesto_enextranjero = S2.codimpuesto_enextranjero '
            . ', S1.total = S2.total '
            . ', S1.fuera_del_municipio = S2.fuera_del_municipio '
            . ', S1.hoja_ruta_origen = S2.hoja_ruta_origen '
            . ', S1.hoja_ruta_destino = S2.hoja_ruta_destino '
            . ', S1.hoja_ruta_expediciones = S2.hoja_ruta_expediciones '
            . ', S1.hoja_ruta_contratante = S2.hoja_ruta_contratante '
            . ', S1.hoja_ruta_tipoidfiscal = S2.hoja_ruta_tipoidfiscal '
            . ', S1.hoja_ruta_cifnif = S2.hoja_ruta_cifnif '
            . ', S1.idservice_type = S2.idservice_type '
            . ', S1.idempresa = S2.idempresa '
            . ', S1.observaciones = S2.observaciones '
            . ', S1.observaciones_montaje = S2.observaciones_montaje '
            . ', S1.observaciones_vehiculo = S2.observaciones_vehiculo '
            . ', S1.observaciones_facturacion = S2.observaciones_facturacion '
            . ', S1.observaciones_liquidacion = S2.observaciones_liquidacion '
            . ', S1.observaciones_drivers = S2.observaciones_drivers '
            . ', S1.iddriver_1 = S2.iddriver_1 '
            . ', S1.driver_alojamiento_1 = S2.driver_alojamiento_1 '
            . ', S1.driver_observaciones_1 = S2.driver_observaciones_1 '
            . ', S1.iddriver_2 = S2.iddriver_2 '
            . ', S1.driver_alojamiento_2 = S2.driver_alojamiento_2 '
            . ', S1.driver_observaciones_2 = S2.driver_observaciones_2 '
            . ', S1.iddriver_3 = S2.iddriver_3 '
            . ', S1.driver_alojamiento_3 = S2.driver_alojamiento_3 '
            . ', S1.driver_observaciones_3 = S2.driver_observaciones_3 '
            . ', S1.idvehicle = S2.idvehicle '
            . ', S1.codsubcuenta_km_nacional = S2.codsubcuenta_km_nacional '
            . ', S1.codsubcuenta_km_extranjero = S2.codsubcuenta_km_extranjero '
            . ', S1.fecha_desde = S2.fecha_desde '
            . ', S1.fecha_hasta = S2.fecha_hasta '
            . ', S1.hora_anticipacion = S2.hora_anticipacion '
            . ', S1.hora_desde = S2.hora_desde '
            . ', S1.hora_hasta = S2.hora_hasta '
            . ', S1.salida_desde_nave_sn = S2.salida_desde_nave_sn '
            . ' WHERE S1.idservice = ' . $this->idservice . ' '
            . ' AND S2.idservice = ' . $this->idservice . ';';

        self::$dataBase->exec($sql);
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

    protected function checkFechasPeriodo(): bool
    {
        // La fecha de inicio es obligatoria
        if (empty($this->fecha_desde)) {
            $this->toolBox()->i18nLog()->error('La fecha de inicio, debe de introducirla.');
            return false;
        }

        // Si fecha hasta está introducida y fecha desde no está vacía y además es mayor que fecha hasta ... fallo
        if (!empty($this->fecha_hasta)) {
            if (!empty($this->fecha_desde) and
                $this->fecha_desde > $this->fecha_hasta) {
                $this->toolBox()->i18nLog()->error('La fecha de inicio, no puede ser mayor que la fecha de fin.');
                return false;
            }
        }
        return true;
    }

    protected function checkHorasPeriodo(): bool
    {
        // La hora de inicio es obligatoria
        if (empty($this->hora_desde)) {
            $a_devolver = false;
            $this->toolBox()->i18nLog()->error('La hora de inicio, debe de introducirla.');
            return false;
        }

        // La hora de fin es obligatoria
        if (empty($this->hora_hasta)) {
            $this->toolBox()->i18nLog()->error('La hora fin, debe de introducirla.');
            return false;
        }

        // Si fecha hasta está introducida y fecha desde no está vacía y además es mayor que fecha hasta ... fallo
        if (!empty($this->hora_hasta)) {
            if (!empty($this->hora_desde) and
                $this->hora_desde > $this->hora_hasta) {
                $this->toolBox()->i18nLog()->error('La hora de inicio, no puede ser mayor que la hora de fin.');
                return false;
            }
        }

        return true;
    }

    protected function checkFields(): bool
    {
        if ($this->checkFechasPeriodo() === false) {
            return false;
        }

        if ($this->checkHorasPeriodo() === false) {
            return false;
        }

        if (empty($this->codcliente)) {
            $this->toolBox()->i18nLog()->error('Debe de asignar el servicio a un cliente.');
            return false;
        }

        if (empty($this->nombre)) {
            $this->toolBox()->i18nLog()->error('Debe completar la descripción del servicio.');
            return false;
        }

        if (empty($this->hoja_ruta_origen)) {
            $this->toolBox()->i18nLog()->error('Debe completar el origen de la Hoja de Ruta.');
            return false;
        }

        if (empty($this->hoja_ruta_destino)) {
            $this->toolBox()->i18nLog()->error('Debe completar el destino de la Hoja de Ruta.');
            return false;
        }

        if (empty($this->hoja_ruta_expediciones)) {
            $this->toolBox()->i18nLog()->error('Debe completar las expediciones de la Hoja de Ruta.');
            return false;
        }

        if (empty($this->hoja_ruta_contratante)) {
            $this->toolBox()->i18nLog()->error('Debe completar el contratante de la Hoja de Ruta.');
            return false;
        }

        if (empty($this->hoja_ruta_tipoidfiscal)) {
            $this->toolBox()->i18nLog()->error('Debe completar Id. Fiscal de la Hoja de Ruta.');
            return false;
        }

        if (empty($this->hoja_ruta_cifnif)) {
            $this->toolBox()->i18nLog()->error('Debe completar el Num. Fiscal de la Hoja de Ruta.');
            return false;
        }

        if (empty($this->idempresa)) {
            $this->toolBox()->i18nLog()->error('Debe completar la empresa que realiza el servicio.');
            return false;
        }

        if (empty($this->importe)) {
            $this->toolBox()->i18nLog()->error('Debe completar el Importe x km nacional.');
            return false;
        }

        if (empty($this->codimpuesto)) {
            $this->toolBox()->i18nLog()->error('No ha elegido el tipo de impuesto para "Importe x km nacional".');
            return false;
        }

        if (empty($this->importe_enextranjero)) {
            $this->toolBox()->i18nLog()->error('Debe completar el Importe x km en extranjero.');
            return false;
        }

        if (empty($this->codimpuesto_enextranjero)) {
            $this->toolBox()->i18nLog()->error('No ha elegido el tipo de impuesto para "Importe x km en extrajero".');
            return false;
        }

        if (empty($this->inicio_dia)) {
            $this->toolBox()->i18nLog()->error('No ha elegido la fecha de inicio del servicio.');
            return false;
        }

        if (empty($this->fin_dia)) {
            $this->toolBox()->i18nLog()->error('No ha elegido la fecha de fin del servicio.');
            return false;
        }

        if (empty($this->plazas) or $this->plazas <= 0) {
            $this->toolBox()->i18nLog()->error('Debe de completar las plazas.');
            return false;
        }

        if (!$this->aceptado) {
            $this->toolBox()->i18nLog()->info('Si no acepta el servicio, no podrá montarse.');
            return false;
        }

        $this->codsubcuenta_km_nacional = empty($this->codsubcuenta_km_nacional) ? null : $this->codsubcuenta_km_nacional;
        $this->codsubcuenta_km_extranjero = empty($this->codsubcuenta_km_extranjero) ? null : $this->codsubcuenta_km_extranjero;

        return true;
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
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);
        if (false === parent::saveUpdate($values)) {
            return false;
        }

        $this->actualizarServicioEnMontaje();
        return true;
    }
}