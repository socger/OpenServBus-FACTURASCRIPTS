<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Model\Impuesto;
use FacturaScripts\Core\Session;

class ServiceRegularValuation extends Base\ModelClass
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
    public $idservice_regular;

    /** @var int */
    public $idservice_valuation_type;

    /** @var int */
    public $idservice_regular_valuation;

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

    public function getServicioRegular(): ServiceRegular
    {
        $servicioRegular = new ServiceRegular();
        $servicioRegular->loadFromCode($this->idservice_regular);
        return $servicioRegular;
    }

    public function install(): string
    {
        new ServiceRegular();
        new ServiceValuationType();
        new Impuesto();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idservice_regular_valuation';
    }

    public static function tableName(): string
    {
        return 'service_regular_valuations';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        if (empty($this->idservice_regular)) {
            $this->toolBox()->i18nLog()->error('Debe de asignar el servicio regular al que pertenece este itinerario.');
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

    public function url(string $type = 'auto', string $list = 'ListServiceRegular'): string
    {
        return parent::url($type, $list . '?activetab=List');
    }

    protected function actualizar_Importes()
    {
        $sql = " UPDATE service_regulars "
            . " SET service_regulars.importe = ( SELECT SUM(service_regular_valuations.importe) "
            . " FROM service_regular_valuations "
            . " WHERE service_regular_valuations.idservice_regular = " . $this->idservice_regular . " "
            . " AND service_regular_valuations.activo = 1 ) "
            . " , service_regulars.importe_enextranjero = ( SELECT SUM(service_regular_valuations.importe_enextranjero)  "
            . " FROM service_regular_valuations "
            . " WHERE service_regular_valuations.idservice_regular = " . $this->idservice_regular . " "
            . " AND service_regular_valuations.activo = 1 ) "
            . " WHERE service_regulars.idservice_regular = " . $this->idservice_regular . ";";

        self::$dataBase->exec($sql);

        $servicioRegular = $this->getServicioRegular();
        $servicioRegular->rellenarTotal();
        $servicioRegular->save();
    }

    protected function checkDescripcion(): bool
    {
        if (false === empty($this->nombre)) {
            return true;
        }

        if (empty($this->idservice_valuation_type)) {
            $this->toolBox()->i18nLog()->error('Debe de completar la descripción.');
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
            // Comprobamos si la cuenta existe
            $sql = ' SELECT MAX(service_regular_valuations.orden) AS orden '
                . ' FROM service_regular_valuations '
                . ' WHERE service_regular_valuations.idservice_regular = ' . $this->idservice_regular
                . ' ORDER BY service_regular_valuations.idservice_regular '
                . ' , service_regular_valuations.orden ';

            $registros = self::$dataBase->select($sql); // Para entender su funcionamiento visitar ... https://facturascripts.com/publicaciones/acceso-a-la-base-de-datos-818

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
        if (false === parent::saveUpdate($values)) {
            return false;
        }

        $this->actualizar_Importes();
        return true;
    }
}