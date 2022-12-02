<?php
namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Core\Session;

class TourOperador extends ModelClass
{
    use ModelTrait;

    /** @var string */
    public $creationdate;

    /** @var int */
    public $id;

    /** @var string */
    public $lastnick;

    /** @var string */
    public $lastupdate;

    /** @var string */
    public $name;

    /** @var string */
    public $nick;

    public function clear() 
    {
        parent::clear();
        $this->creationdate = date(self::DATETIME_STYLE);
        $this->nick = Session::get('user')->nick ?? null;
    }

    public static function primaryColumn(): string
    {
        return "id";
    }

    public static function tableName(): string
    {
        return "tour_operadores";
    }

    protected function saveInsert(array $values = []): bool
    {
        $this->lastnick = null;
        $this->lastupdate = null;
        return parent::saveInsert($values);
    }

    protected function saveUpdate(array $values = []): bool
    {
        $this->lastnick = Session::get('user')->nick ?? null;
        $this->lastupdate = date(self::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}
