<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

use FacturaScripts\Core\Session;

trait OpenServBusModelTrait
{
    protected function comprobarSiActivo(): bool
    {
        if ($this->activo) {
            $this->fechabaja = null;
            $this->fechamodificacion = null;
            $this->userbaja = null;
            $this->usermodificacion = null;
            $this->motivobaja = null;
            return true;
        }

        $this->fechabaja = date(static::DATETIME_STYLE);
        $this->userbaja = Session::get('user')->nick ?? null;

        if (empty($this->motivobaja)) {
            $this->toolBox()->i18nLog()->error('record-is-not-active-specify-reason');
            return false;
        }

        return true;
    }
}