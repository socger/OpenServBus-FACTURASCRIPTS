<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;
use FacturaScripts\Dinamic\Model\Proveedor;

class Collaborator extends Base\ModelClass
{
    use Base\ModelTrait;
    use OpenServBusModelTrait;

    /** @var bool */
    public $activo;

    /** @var string */
    public $codproveedor;

    /** @var string */
    public $fechaalta;

    /** @var string */
    public $fechabaja;

    /** @var string */
    public $fechamodificacion;

    /** @var int */
    public $idcollaborator;

    /** @var string */
    public $motivobaja;

    /** @var string */
    public $nombre;

    /** @var string */
    public $observaciones;

    /** @var string */
    public $useralta;

    /** @var string */
    public $userbaja;

    /** @var string */
    public $usermodificacion;

    public function actualizarNombreColaboradorEn()
    {
        // Rellenamos el nombre del colaborador en otras tablas
        $sql = "UPDATE drivers SET drivers.nombre = '" . $this->nombre . "' WHERE drivers.idcollaborator = " . $this->idcollaborator . ";";
        self::$dataBase->exec($sql);

        $sql = "UPDATE helpers SET helpers.nombre = '" . $this->nombre . "' WHERE helpers.idcollaborator = " . $this->idcollaborator . ";";
        self::$dataBase->exec($sql);
    }

    public function clear()
    {
        parent::clear();
        $this->activo = true;
        $this->fechaalta = date(static::DATETIME_STYLE);
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public static function primaryColumn(): string
    {
        return 'idcollaborator';
    }

    public static function tableName(): string
    {
        return 'collaborators';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        $utils = $this->toolBox()->utils();
        $this->codproveedor = $utils->noHtml($this->codproveedor);
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'ListHelper'): string
    {
        return parent::url($type, $list . '?activetab=List');
    }

    protected function getProveedor(): Proveedor
    {
        $proveedor = new Proveedor();
        $proveedor->loadFromCode($this->codproveedor);
        return $proveedor;
    }

    protected function saveInsert(array $values = []): bool
    {
        $this->nombre = $this->getProveedor()->nombre;
        return parent::saveInsert($values);
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}