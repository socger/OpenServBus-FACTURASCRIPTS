<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class Vehicle extends Base\ModelClass
{
    use Base\ModelTrait;
    use OpenServBusModelTrait;

    /** @var bool */
    public $activo;

    /** @var string */
    public $carroceria;

    /** @var string */
    public $cod_vehicle;

    /** @var string */
    public $configuraciones_especiales;

    /** @var string */
    public $fechaalta;

    /** @var string */
    public $fechabaja;

    /** @var string */
    public $fechamodificacion;

    /** @var string */
    public $fecha_km_actuales;

    /** @var string */
    public $fecha_matriculacion_actual;

    /** @var string */
    public $fecha_matriculacion_primera;

    /** @var int */
    public $idcollaborator;

    /** @var int */
    public $iddriver_usual;

    /** @var int */
    public $idempresa;

    /** @var int */
    public $idfuel_type;

    /** @var int */
    public $idgarage;

    /** @var int */
    public $idvehicle;

    /** @var int */
    public $idvehicle_type;

    /** @var int */
    public $km_actuales;

    /** @var string */
    public $matricula;

    /** @var string */
    public $motivobaja;

    /** @var string */
    public $motor_chasis;

    /** @var string */
    public $nombre;

    /** @var string */
    public $numero_bastidor;

    /** @var string */
    public $observaciones;

    /** @var string */
    public $numero_obra;

    /** @var int */
    public $plazas_ofertables;

    /** @var string */
    public $plazas_segun_ficha_tecnica;

    /** @var int */
    public $plazas_segun_permiso;

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
        $this->km_actuales = 0;
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public function install()
    {
        new Collaborator();
        new Garage();
        return parent::install();
    }

    public static function primaryColumn(): string
    {
        return 'idvehicle';
    }

    public static function tableName(): string
    {
        return 'vehicles';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        // Comprobamos que el código se ha introducido correctamente
        if (!empty($this->cod_vehicle) && 1 !== \preg_match('/^[A-Z0-9_\+\.\-]{1,10}$/i', $this->cod_vehicle)) {
            $this->toolBox()->i18nLog()->error(
                'invalid-alphanumeric-code',
                ['%value%' => $this->cod_vehicle, '%column%' => 'cod_vehicle', '%min%' => '1', '%max%' => '10']
            );
            return false;
        }

        // Exigimos que se introduzca idempresa o idcollaborator
        if ((empty($this->idempresa)) && (empty($this->idcollaborator))) {
            $this->toolBox()->i18nLog()->error('Debe de confirmar si es un vehículo nuestro o de una empresa colaboradora');
            return false;
        }

        if ((!empty($this->idempresa)) && (!empty($this->idcollaborator))) {
            $this->toolBox()->i18nLog()->error('O es un vehículo nuestro o de una empresa colaboradora, pero ambos no');
            return false;
        }

        // Si Fecha Matriculación Actual está vacía, pero Fecha Matriculación Primera está rellena, pues
        // Fecha Matriculacion Actual = Fecha Matriculación Primera
        if (empty($this->fecha_matriculacion_actual)) {
            if (!empty($this->fecha_matriculacion_primera)) {
                $this->toolBox()->i18nLog()->info('La Fecha Matriculación Actual se ha rellenado con el valor de la Fecha de Matriculación Actual, por estar vacía');
                $this->fecha_matriculacion_actual = $this->fecha_matriculacion_primera;
            }
        }

        $utils = $this->toolBox()->utils();
        $this->cod_vehicle = $utils->noHtml($this->cod_vehicle);
        $this->nombre = $utils->noHtml($this->nombre);
        $this->matricula = $utils->noHtml($this->matricula);
        $this->motor_chasis = $utils->noHtml($this->motor_chasis);
        $this->numero_bastidor = $utils->noHtml($this->numero_bastidor);
        $this->carroceria = $utils->noHtml($this->carroceria);
        $this->numero_obra = $utils->noHtml($this->numero_obra);
        $this->plazas_segun_ficha_tecnica = $utils->noHtml($this->plazas_segun_ficha_tecnica);
        $this->configuraciones_especiales = $utils->noHtml($this->configuraciones_especiales);
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        return parent::test();
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}