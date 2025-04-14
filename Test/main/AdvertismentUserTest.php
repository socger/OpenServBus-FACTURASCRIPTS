<?php declare(strict_types=1);

namespace FacturaScripts\Test\Plugins;

use FacturaScripts\Core\Base\MiniLog;
use FacturaScripts\Core\Model\User;
use FacturaScripts\Plugins\OpenServBus\Model\AdvertismentUser;
use FacturaScripts\Test\Traits\LogErrorsTrait;
use PHPUnit\Framework\TestCase;

final class AdvertismentUserTest extends TestCase
{
    use LogErrorsTrait;

    protected function setUp(): void
    {
        // instanciamos al usuario para que se cree la tabla y no de error de foreign key
        new User();
    }

    /**
     * comprobamos que al dar de baja hay que
     * pasar el motivo de la baja obligatoriamente
     */
    public function testRequiereMotivoBaja(): void
    {
        $advertismentUser = new AdvertismentUser();
        $advertismentUser->nombre = 'test';
        $this->assertTrue($advertismentUser->save());

        // borramos los mensajes anteriores
        MiniLog::clear();

        // damos de baja
        $advertismentUser->activo = false;

        // compboramos
        $this->assertFalse($advertismentUser->save());
        $this->assertEquals('record-is-not-active-specify-reason', MiniLog::read()[0]['original']);

        // ahora pasamo el motivo de la baja

        // borramos los mensajes anteriores
        MiniLog::clear();

        $advertismentUser->activo = false;
        $advertismentUser->motivobaja = 'test-motivo-baja';

        // compboramos
        $this->assertTrue($advertismentUser->save());
        $this->assertEmpty(MiniLog::read());
    }

    protected function tearDown(): void
    {
        $this->logErrors();
    }
}
