<?php

namespace FacturaScripts\Plugins\OpenServBus\Model;

trait OpenServBusModelTrait
{
    protected function comprobarSiActivo(): bool
    {
        if ($this->activo) {
            $this->fechabaja = null;
            $this->userbaja = null;
            $this->motivobaja = null;
            return true;
        }

        $this->fechabaja = $this->fechamodificacion;
        $this->userbaja = $this->usermodificacion;

        if (empty($this->motivobaja)) {
            $this->toolBox()->i18nLog()->error('Si el registro no está activo, debe especificar el motivo.');
            return false;
        }

        return false;
    }
}