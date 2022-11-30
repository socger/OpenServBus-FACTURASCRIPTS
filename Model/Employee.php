<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class Employee extends Base\ModelClass
{
    use Base\ModelTrait;
    use OpenServBusModelTrait;

    /** @var bool */
    public $activo;

    /** @var string */
    public $apartado;

    /** @var string */
    public $cifnif;

    /** @var string */
    public $ciudad;

    /** @var string */
    public $codpais;

    /** @var string */
    public $codpostal;

    /** @var string */
    public $cod_employee;

    /** @var string */
    public $direccion;

    /** @var bool */
    public $driver_yn;

    /** @var string */
    public $email;

    /** @var string */
    public $fechaalta;

    /** @var string */
    public $fechabaja;

    /** @var string */
    public $fechamodificacion;

    /** @var string */
    public $fecha_nacimiento;

    /** @var int */
    public $idemployee;

    /** @var int */
    public $idempresa;

    /** @var string */
    public $motivobaja;

    /** @var string */
    public $nombre;

    /** @var string */
    public $num_seg_social;

    /** @var string */
    public $observaciones;

    /** @var string */
    public $provincia;

    /** @var string */
    public $telefono1;

    /** @var string */
    public $telefono2;

    /** @var string */
    public $tipoidfiscal;

    /** @var string */
    public $useralta;

    /** @var string */
    public $userbaja;

    /** @var string */
    public $usermodificacion;

    /** @var string */
    public $user_facturascripts_nick;

    /** @var string */
    public $web;

    public function actualizarNombreEmpleadoEn()
    {
        // Rellenamos el nombre del empleado en otras tablas
        $sql = "UPDATE drivers SET drivers.nombre = '" . $this->nombre . "' WHERE drivers.idemployee = " . $this->idemployee . ";";
        self::$dataBase->exec($sql);

        $sql = "UPDATE employees_attendance_management_yn SET employees_attendance_management_yn.nombre = '" . $this->nombre . "' WHERE employees_attendance_management_yn.idemployee = " . $this->idemployee . ";";
        self::$dataBase->exec($sql);

        $sql = "UPDATE employee_contracts SET employee_contracts.nombre = '" . $this->nombre . "' WHERE employee_contracts.idemployee = " . $this->idemployee . ";";
        self::$dataBase->exec($sql);

        $sql = "UPDATE helpers SET helpers.nombre = '" . $this->nombre . "' WHERE helpers.idemployee = " . $this->idemployee . ";";
        self::$dataBase->exec($sql);

    }

    public function clear()
    {
        parent::clear();
        $this->activo = true;
        $this->codpais = $this->toolBox()->appSettings()->get('default', 'codpais');
        $this->fechaalta = date(static::DATETIME_STYLE);
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public static function primaryColumn(): string
    {
        return 'idemployee';
    }

    public function save(): bool
    {
        if (false === parent::save()) {
            return false;
        }

        $this->actualizarNombreEmpleadoEn();
        return true;
    }

    public static function tableName(): string
    {
        return 'employees';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        // Comprobamos que el código de empleado si se ha introducido correctamente
        if (!empty($this->cod_employee) && 1 !== preg_match('/^[A-Z0-9_\+\.\-]{1,10}$/i', $this->cod_employee)) {
            $this->toolBox()->i18nLog()->error(
                'invalid-alphanumeric-code',
                ['%value%' => $this->cod_employee, '%column%' => 'cod_employee', '%min%' => '1', '%max%' => '10']
            );
            return false;
        }

        $this->comprobarSiEsConductor();

        $utils = $this->toolBox()->utils();
        $this->cod_employee = $utils->noHtml($this->cod_employee);
        $this->user_facturascripts_nick = $utils->noHtml($this->user_facturascripts_nick);
        $this->tipoidfiscal = $utils->noHtml($this->tipoidfiscal);
        $this->cifnif = $utils->noHtml($this->cifnif);
        $this->nombre = $utils->noHtml($this->nombre);
        $this->ciudad = $utils->noHtml($this->ciudad);
        $this->provincia = $utils->noHtml($this->provincia);
        $this->codpais = $utils->noHtml($this->codpais);
        $this->codpostal = $utils->noHtml($this->codpostal);
        $this->apartado = $utils->noHtml($this->apartado);
        $this->direccion = $utils->noHtml($this->direccion);
        $this->telefono1 = $utils->noHtml($this->telefono1);
        $this->telefono2 = $utils->noHtml($this->telefono2);
        $this->email = $utils->noHtml($this->email);
        $this->web = $utils->noHtml($this->web);
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->num_seg_social = $utils->noHtml($this->num_seg_social);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        return parent::test();
    }

    // Comprobar si está creado como conductor
    protected function comprobarSiEsConductor()
    {
        $sql = ' SELECT COUNT(*) AS cuantos '
            . ' FROM drivers '
            . ' WHERE drivers.idemployee = ' . $this->idemployee;

        $registros = self::$dataBase->select($sql);
        foreach ($registros as $fila) {
            $this->driver_yn = 0;
            if ($fila['cuantos'] > 0) {
                $this->driver_yn = 1;
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