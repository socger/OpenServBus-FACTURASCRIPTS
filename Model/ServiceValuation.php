<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Model\Impuesto;
use FacturaScripts\Core\Session;

class ServiceValuation extends Base\ModelClass
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

    /** @var int */
    public $idservice;

    /** @var int */
    public $idservice_valuation;

    /** @var int */
    public $idservice_valuation_type;

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

    /** @var int */
    public $orden;

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
        $this->importe = 0;
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public function delete(): bool
    {
        if (false === parent::delete()) {
            return false;
        }

        $this->actualizar_Importes();
        return true;
    }

    public function getService(): Service
    {
        $service = new Service();
        $service->loadFromCode($this->idservice);
        return $service;
    }

    public function install(): string
    {
        new Service();
        new ServiceValuationType();
        new Impuesto();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idservice_valuation';
    }

    public static function tableName(): string
    {
        return 'service_valuations';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        if (empty($this->idservice)) {
            $this->toolBox()->i18nLog()->error('assign-service-this-itinerary');
            return false;
        }

        if ($this->checkDescripcion() === false) {
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

    protected function actualizar_Importes()
    {
        $sql = " UPDATE services "
            . " SET services.importe = ( SELECT SUM(service_valuations.importe) "
            . " FROM service_valuations "
            . " WHERE service_valuations.idservice = " . $this->idservice . " "
            . " AND service_valuations.activo = 1 ) "
            . " , services.importe_enextranjero = ( SELECT SUM(service_valuations.importe_enextranjero)  "
            . " FROM service_valuations "
            . " WHERE service_valuations.idservice = " . $this->idservice . " "
            . " AND service_valuations.activo = 1 ) "
            . " WHERE services.idservice = " . $this->idservice . ";";

        self::$dataBase->exec($sql);

        $servicio = $this->getService();
        $servicio->rellenarTotal();
        $servicio->save();
    }

    protected function checkDescripcion(): bool
    {
        if (false === empty($this->nombre)) {
            return true;
        }

        if (empty($this->idservice_valuation_type)) {
            $this->toolBox()->i18nLog()->error('complete-to-description');
            return false;
        }

        $sql = ' SELECT nombre '
            . ' FROM service_valuation_types '
            . ' WHERE idservice_valuation_type = ' . $this->idservice_valuation_type
            . ' ORDER BY idservice_valuation_type ';

        $registros = self::$dataBase->select($sql);
        foreach ($registros as $fila) {
            $this->nombre = $fila['nombre'];
        }
        return true;
    }

    protected function comprobarOrden()
    {
        if (empty($this->orden)) {
            $sql = ' SELECT MAX(service_valuations.orden) AS orden '
                . ' FROM service_valuations '
                . ' WHERE service_valuations.idservice = ' . $this->idservice
                . ' ORDER BY service_valuations.idservice '
                . ' , service_valuations.orden ';

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

    protected function saveInsert(array $values = []): bool
    {
        if (false === parent::saveInsert($values)) {
            return false;
        }

        $this->actualizar_Importes();
        return true;
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);

        if (false === parent::saveUpdate($values)) {
            return false;
        }

        $this->actualizar_Importes();
        return true;
    }
}