<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class ServiceItinerary extends Base\ModelClass
{
    use Base\ModelTrait;
    use OpenServBusModelTrait;

    /** @var bool */
    public $activo;

    /** @var string */
    public $fechaalta;

    /** @var string */
    public $fechabaja;

    /** @var string */
    public $fechamodificacion;

    /** @var string */
    public $hora;

    /** @var int */
    public $idservice;

    /** @var int */
    public $idservice_itinerary;

    /** @var int */
    public $kms;

    /** @var bool */
    public $kms_enExtranjero;

    /** @var bool */
    public $kms_vacios;

    /** @var string */
    public $motivobaja;

    /** @var string */
    public $nombre;

    /** @var string */
    public $observaciones;

    /** @var int */
    public $orden;

    /** @var int */
    public $pasajeros_entradas;

    /** @var int */
    public $pasajeros_salidas;

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
        $this->fechaalta = date(static::DATETIME_STYLE);
        $this->kms = 0;
        $this->kms_vacios = false;
        $this->pasajeros_entradas = 0;
        $this->pasajeros_salidas = 0;
        $this->kms_enExtranjero = false;
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public function getServicio(): Service
    {
        $servicio = new Service();
        $servicio->loadFromCode($this->idservice);
        return $servicio;
    }

    public function install(): string
    {
        new Service();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idservice_itinerary';
    }

    public static function tableName(): string
    {
        return 'service_itineraries';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        if (empty($this->idservice)) {
            $this->toolBox()->i18nLog()->error('Debe de asignar el servicio discrecional al que pertenece este itinerario.');
            return false;
        }

        if (empty($this->hora)) {
            $this->toolBox()->i18nLog()->error('Falta la hora en la que debe de estar en la parada.');
            return false;
        }

        if (empty($this->pasajeros_entradas) && empty($this->pasajeros_salidas)) {
            $this->toolBox()->i18nLog()->info('Debe de asignar la cantidad de pasajeros a recoger/dejar.');
            return false;
        }

        $this->comprobarOrden();

        $utils = $this->toolBox()->utils();
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        $this->nombre = $utils->noHtml($this->nombre);
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'ListService'): string
    {
        return parent::url($type, $list . '?activetab=List');
    }

    protected function comprobarOrden()
    {
        if (empty($this->orden)) {
            // Comprobamos si la cuenta existe
            $sql = ' SELECT MAX(service_itineraries.orden) AS orden '
                . ' FROM service_itineraries '
                . ' WHERE service_itineraries.idservice = ' . $this->idservice
                . ' ORDER BY service_itineraries.idservice '
                . ' , service_itineraries.orden ';

            $registros = self::$dataBase->select($sql);

            foreach ($registros as $fila) {
                if (empty($fila['orden'])) {
                    $this->orden = 5;
                } else {
                    $this->orden = ($fila['orden'] + 5);
                }
            }
        }
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}