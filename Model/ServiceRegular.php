<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class ServiceRegular extends Base\ModelClass
{
    use Base\ModelTrait;
    use OpenServBusModelTrait;

    /** @var bool */
    public $aceptado;

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

    /** @var bool */
    public $combinadoSN;

    /** @var string */
    public $cod_servicio;

    /** @var bool */
    public $domingo;

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
    public $facturar_SN;

    /** @var bool */
    public $facturar_agrupando;

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
    public $idhelper;

    /** @var int */
    public $idservice_regular;

    /** @var int */
    public $idservice_regular_period;

    /** @var int */
    public $idservice_type;

    /** @var int */
    public $idvehicle;

    /** @var int */
    public $idvehicle_type;

    /** @var float */
    public $importe;

    /** @var float */
    public $importe_enextranjero;

    /** @var bool */
    public $jueves;

    /** @var bool */
    public $lunes;

    /** @var bool */
    public $martes;

    /** @var bool */
    public $miercoles;

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
    public $sabado;

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

    /** @var bool */
    public $viernes;

    public function clear()
    {
        parent::clear();
        $this->aceptado = false;
        $this->activo = true;
        $this->domingo = false;
        $this->facturar_agrupando = true;
        $this->facturar_SN = true;
        $this->fechaalta = date(static::DATETIME_STYLE);
        $this->importe = 0;
        $this->importe_enextranjero = 0;
        $this->jueves = false;
        $this->lunes = false;
        $this->martes = false;
        $this->miercoles = false;
        $this->plazas = 0;
        $this->sabado = false;
        $this->total = 0;
        $this->useralta = Session::get('user')->nick ?? null;
        $this->viernes = false;
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
        return 'idservice_regular';
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
        return 'service_regulars';
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
        // Comprobamos que el código se ha introducido correctamente
        if (!empty($this->cod_servicio) && 1 !== \preg_match('/^[A-Z0-9_\+\.\-]{1,10}$/i', $this->cod_servicio)) {
            $this->toolBox()->i18nLog()->error(
                'invalid-alphanumeric-code',
                ['%value%' => $this->cod_servicio, '%column%' => 'cod_servicio', '%min%' => '1', '%max%' => '10']
            );
            return false;
        }

        if ($this->lunes === false &&
            $this->martes === false &&
            $this->miercoles === false &&
            $this->jueves === false &&
            $this->viernes === false &&
            $this->sabado === false &&
            $this->domingo === false) {
            $this->toolBox()->i18nLog()->error('service-regular-choose-days-of-week');
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

        if (empty($this->plazas) or $this->plazas <= 0) {
            $this->toolBox()->i18nLog()->error('complete-squares');
            return false;
        }

        if (!$this->aceptado) {
            $this->toolBox()->i18nLog()->info('accept-service-able-mount');
        }

        if ($this->hayCombinacionesDondeEsteElServicioQueNoCoincidenLosDiasDeSemana() === true) {
            return false;
        }

        if (empty($this->iddriver_1) || empty($this->idvehicle)) {
            $this->toolBox()->i18nLog()->info('service-default-priority');
        }

        $this->completarCombinadoSN();
        $this->completarDatosUltimoPeriodo();

        $this->codsubcuenta_km_nacional = empty($this->codsubcuenta_km_nacional) ? null : $this->codsubcuenta_km_nacional;
        $this->codsubcuenta_km_extranjero = empty($this->codsubcuenta_km_extranjero) ? null : $this->codsubcuenta_km_extranjero;
        $this->cod_servicio = empty($this->cod_servicio) ? (string)$this->newCode() : $this->cod_servicio;
        return true;
    }

    protected function completarCombinadoSN()
    {
        if (empty($this->idservice_regular)) {
            return;
        }

        $sql = ' SELECT COUNT(*) AS cantidad '
            . ' FROM service_regular_combination_servs '
            . ' WHERE service_regular_combination_servs.idservice_regular = ' . $this->idservice_regular . ' '
            . ' AND service_regular_combination_servs.activo = 1 '
            . ' ORDER BY service_regular_combination_servs.idservice_regular ';

        $this->combinadoSN = false;
        $registros = self::$dataBase->select($sql);
        foreach ($registros as $fila) {
            if ($fila['cantidad'] > 0) {
                $this->combinadoSN = true;
            }
        }
    }

    protected function completarDatosUltimoPeriodo()
    {
        if (empty($this->idservice_regular)) {
            return;
        }

        $sql = ' SELECT idservice_regular_period '
            . ' , fecha_desde '
            . ' , fecha_hasta '
            . ' , hora_anticipacion '
            . ' , hora_desde '
            . ' , hora_hasta '
            . ' , salida_desde_nave_sn '
            . ' , observaciones '
            . ' FROM service_regular_periods '
            . ' WHERE idservice_regular = ' . $this->idservice_regular . ' '
            . ' AND activo = 1 '
            . ' ORDER BY fecha_desde DESC '
            . ' , fecha_hasta DESC '
            . ' , hora_desde DESC '
            . ' , hora_hasta DESC '
            . ' , idservice_regular '
            . ' LIMIT 1 ';

        $this->idservice_regular_period = null;
        $this->fecha_desde = null;
        $this->fecha_hasta = null;
        $this->hora_anticipacion = null;
        $this->hora_desde = null;
        $this->hora_hasta = null;
        $this->salida_desde_nave_sn = null;
        $this->observaciones_periodo = null;

        $registros = self::$dataBase->select($sql);
        foreach ($registros as $fila) {
            $this->idservice_regular_period = $fila['idservice_regular_period'];
            $this->fecha_desde = $fila['fecha_desde'];
            $this->fecha_hasta = $fila['fecha_hasta'];
            $this->hora_anticipacion = $fila['hora_anticipacion'];
            $this->hora_desde = $fila['hora_desde'];
            $this->hora_hasta = $fila['hora_hasta'];
            $this->salida_desde_nave_sn = $fila['salida_desde_nave_sn'];
            $this->observaciones_periodo = $fila['observaciones'];
        }
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

    protected function hayCombinacionesDondeEsteElServicioQueNoCoincidenLosDiasDeSemana(): bool
    {
        if (empty($this->idservice_regular)) {
            return false;
        }

        $combinacionesConDiasDiferentes = [];

        $sql = ' SELECT service_regular_combinations.lunes '
            . ' , service_regular_combinations.martes '
            . ' , service_regular_combinations.miercoles '
            . ' , service_regular_combinations.jueves '
            . ' , service_regular_combinations.viernes '
            . ' , service_regular_combinations.sabado '
            . ' , service_regular_combinations.domingo '
            . ' , service_regular_combinations.idservice_regular_combination '
            . ' , service_regular_combinations.nombre '
            . ' FROM service_regular_combination_servs '
            . ' LEFT JOIN service_regular_combinations on (service_regular_combinations.idservice_regular_combination = service_regular_combination_servs.idservice_regular_combination) '
            . ' WHERE service_regular_combination_servs.idservice_regular = ' . $this->idservice_regular;

        $registros = self::$dataBase->select($sql);

        foreach ($registros as $fila) {
            $coincideAlgunDia = false;

            // Una combinación puede tener varios servicios regulares, por lo
            // que tengo que comprobar todos sus servicios
            if ($this->lunes == 1) {
                if ($this->lunes == $fila['lunes']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->martes == 1) {
                if ($this->martes == $fila['martes']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->miercoles == 1) {
                if ($this->miercoles == $fila['miercoles']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->jueves == 1) {
                if ($this->jueves == $fila['jueves']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->viernes == 1) {
                if ($this->viernes == $fila['viernes']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->sabado == 1) {
                if ($this->sabado == $fila['sabado']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($this->domingo == 1) {
                if ($this->domingo == $fila['domingo']) {
                    $coincideAlgunDia = true;
                }
            }

            if ($coincideAlgunDia === false) {
                $combinacionesConDiasDiferentes[] = $fila['nombre'];
            }
        }

        if (empty($combinacionesConDiasDiferentes)) {
            return false;
        }

        foreach ($combinacionesConDiasDiferentes as $combinacion) {
            $this->toolBox()->i18nLog()->error("days-week-combination-not-coincide-with-week-service", ['%combination%' => $combinacion]);
        }
        return true;
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}