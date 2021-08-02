<?php

namespace FacturaScripts\Plugins\OpenServBus\Model; 

use FacturaScripts\Core\Model\Proveedor as ParentController;

class Proveedor extends ParentController {
    //use Base\ModelTrait;
    
    public function save()
    {
        $resultado = parent::save();
        if ($resultado == false){
            return false;
        }
        
        $this->actualizarNombreProveedorEn();
        return $resultado;
    }


    // ** ********************************** ** //
    // ** FUNCIONES CREADAS PARA ESTE MODELO ** //
    // ** ********************************** ** //
    private function actualizarNombreProveedorEn()
    {
        // Rellenamos el nombre del proveedor en otras tablas
        $sql = "UPDATE collaborators SET collaborators.nombre = '" . $this->nombre . "' WHERE collaborators.codproveedor = " . $this->codproveedor . ";";
        self::$dataBase->exec($sql);
    }
      
    
}
