<?php

namespace FacturaScripts\Plugins\OpenServBus\Extension\Model;

use Closure;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Plugins\OpenServBus\Model\Collaborator;

class Proveedor
{
    public function save(): Closure
    {
        return function () {
            // Rellenamos el nombre del proveedor en otras tablas
            $sql = "UPDATE collaborators SET collaborators.nombre = '" . $this->nombre . "' WHERE collaborators.codproveedor = " . $this->codproveedor . ";";
            self::$dataBase->exec($sql);

            // obtenemos los colaboradores que tienen este proveedor como proveedor
            $collaboratorModel = new Collaborator();
            $where = [new DataBaseWhere('codproveedor', $this->codproveedor)];
            foreach ($collaboratorModel->all($where, [], 0, 0) as $collaborator) {
                $collaborator->actualizarNombreColaboradorEn();
            }
        };
    }
}