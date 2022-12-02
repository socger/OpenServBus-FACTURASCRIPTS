<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Session;

class AdvertismentUser extends Base\ModelClass
{
    use Base\ModelTrait;
    use OpenServBusModelTrait;

    /** @var bool */
    public $activo;

    /** @var string */
    public $codrole;

    /** @var string */
    public $fechaalta;

    /** @var string */
    public $fechabaja;

    /** @var string */
    public $fechamodificacion;

    /** @var string */
    public $fin;

    /** @var int */
    public $idadvertisment_user;

    /** @var string */
    public $inicio;

    /** @var string */
    public $motivobaja;

    /** @var string */
    public $nick;

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

    public function clear()
    {
        parent::clear();
        $this->activo = true;
        $this->fechaalta = date(static::DATETIME_STYLE);
        $this->fin = date(static::DATETIME_STYLE);
        $this->inicio = date(static::DATETIME_STYLE);
        $this->nick = Session::get('user')->nick ?? null;
        $this->useralta = Session::get('user')->nick ?? null;
    }

    public static function primaryColumn(): string
    {
        return 'idadvertisment_user';
    }

    public static function tableName(): string
    {
        return 'advertisment_users';
    }

    public function test(): bool
    {
        if ($this->comprobarSiActivo() === false) {
            return false;
        }

        // Exigimos que se introduzca idempresa o idcollaborator
        if ((!empty($this->nick)) && (!empty($this->codrole))) {
            $this->toolBox()->i18nLog()->error('can-fill-user-or-user-group-bat-not-both');
            return false;
        }

        $utils = $this->toolBox()->utils();
        $this->nombre = $utils->noHtml($this->nombre);
        $this->nick = $utils->noHtml($this->nick);
        $this->codrole = $utils->noHtml($this->codrole);
        $this->observaciones = $utils->noHtml($this->observaciones);
        $this->motivobaja = $utils->noHtml($this->motivobaja);
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'ListAdvertismentUser'): string
    {
        return parent::url($type, $list . '?activetab=List');
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->usermodificacion = Session::get('user')->nick ?? null;
        $this->fechamodificacion = date(static::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}