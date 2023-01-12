<?php
namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Core\Session;
use FacturaScripts\Dinamic\Model\Variante;

class SubReservaTourProduct extends ModelClass
{
    use ModelTrait;

    /** @var float */
    public $cantidad;

    /** @var string */
    public $creationdate;

    /** @var string */
    public $descripcion;

    /** @var int */
    public $id;

    /** @var int */
    public $idsubreserva;

    /** @var string */
    public $lastnick;

    /** @var string */
    public $lastupdate;

    /** @var string */
    public $nick;

    /** @var string */
    public $referencia;

    public function clear() 
    {
        parent::clear();
        $this->cantidad = 1;
        $this->creationdate = date(self::DATETIME_STYLE);
        $this->nick = Session::get('user')->nick ?? null;
    }

    public function getVariante(): Variante
    {
        $variant = new Variante();
        $where = [new DataBaseWhere('referencia', $this->referencia)];
        $variant->loadFromCode('', $where);
        return $variant;
    }

    public static function primaryColumn(): string
    {
        return "id";
    }

    public static function tableName(): string
    {
        return "tour_subreservas_products";
    }

    public function test(): bool
    {
        $this->descripcion = $this->toolBox()->utils()->noHtml($this->descripcion);
        $this->lastnick = $this->toolBox()->utils()->noHtml($this->lastnick);
        $this->nick = $this->toolBox()->utils()->noHtml($this->nick);
        $this->referencia = $this->toolBox()->utils()->noHtml($this->referencia);
        return parent::test();
    }

    protected function saveInsert(array $values = []): bool
    {
        $this->lastupdate = null;
        $this->lastnick = null;
        return parent::saveInsert($values);
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->lastupdate = date(self::DATETIME_STYLE);
        $this->lastnick = Session::get('user')->nick ?? null;
        return parent::saveUpdate($values);
    }
}
