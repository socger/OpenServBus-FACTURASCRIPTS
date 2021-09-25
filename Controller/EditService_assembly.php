<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditService_assembly extends EditController {
    
    public function getModelClassName() {
        return 'Service_assembly';
    }
    
    // Para presentar la pantalla del controlador
    // Estará en el el menú principal bajo \\OpenServBus\Archivos\Cocheras
    public function getPageData(): array {
        $pageData = parent::getPageData();

        $pagedata['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'Montaje de servicios';

        $pageData['icon'] = 'fas fa-business-time';
        

        return $pageData;
    }

    // function loadData es para cargar con datos las diferentes pestañas que tuviera el controlador
    protected function loadData($viewName, $view) {
        switch ($viewName) {
            // Pestaña con el mismo nombre que este controlador EditXxxxx
            case 'EditService_assembly': 
                parent::loadData($viewName, $view);
                
                // Guardamos que usuario pulsará guardar
                $this->views[$viewName]->model->user_nick = $this->user->nick;

                // Guardamos cuando el usuario pulsará guardar
             // $this->views[$viewName]->model->user_fecha = date('d-m-Y');
                $this->views[$viewName]->model->user_fecha = date("Y-m-d H:i:s");
                
                $this->prepararFechasParaVista($viewName);
                $this->prepararHorasParaVista($viewName);
                
                if ($this->views[$viewName]->model->salida_desde_nave_sn === true) {
                    $this->views[$viewName]->model->salida_desde_nave_text = 'SI';
                } else {
                    $this->views[$viewName]->model->salida_desde_nave_text = 'NO';
                }
                
                if ($this->views[$viewName]->model->fuera_del_municipio === true) {
                    $this->views[$viewName]->model->fuera_del_municipio_text = 'SI';
                } else {
                    $this->views[$viewName]->model->fuera_del_municipio_text = 'NO';
                }
                
                if ($this->views[$viewName]->model->facturar_SN === true) {
                    $this->views[$viewName]->model->facturar_SN_text = 'SI';
                } else {
                    $this->views[$viewName]->model->facturar_SN_text = 'NO';
                }
                
                if ($this->views[$viewName]->model->facturar_agrupando === true) {
                    $this->views[$viewName]->model->facturar_agrupando_text = 'SI';
                } else {
                    $this->views[$viewName]->model->facturar_agrupando_text = 'NO';
                }
                
                if ($this->views[$viewName]->model->activo === true) {
                    $this->views[$viewName]->model->activo_text = 'SI';
                } else {
                    $this->views[$viewName]->model->activo_text = 'NO';
                }
                
                $this->readOnlyFields($viewName);
                break;
        }
    }


    // ** *************************************** ** //
    // ** FUNCIONES CREADAS PARA ESTE CONTROLADOR ** //
    // ** *************************************** ** //
    private function readOnlyField($viewName, $fieldName)
    {
        $column = $this->views[$viewName]->columnForField($fieldName);
        $column->widget->readonly = 'true';
    }

    private function displayNoneField($viewName, $fieldName)
    {
        $column = $this->views[$viewName]->columnForField($fieldName);
        $column->display = 'none';
    }

    private function readOnlyAllCommonFields($viewName)
    {
        // Los campos comunes entre discrecionales y regulares = true ... esto se
        // hará siempre que sea un discrecional sin facturar. En regulares sin facturar no
        // EN discrecional o regular facturados siempre se hará
        $this->readOnlyField($viewName, 'plazas');
        $this->readOnlyField($viewName, 'idvehicle_type');
        $this->readOnlyField($viewName, 'hoja_ruta_origen');
        $this->readOnlyField($viewName, 'hoja_ruta_destino');
        $this->readOnlyField($viewName, 'hoja_ruta_expediciones');
        $this->readOnlyField($viewName, 'hoja_ruta_contratante');
        $this->readOnlyField($viewName, 'hoja_ruta_tipoidfiscal');
        $this->readOnlyField($viewName, 'hoja_ruta_cifnif');
        $this->readOnlyField($viewName, 'idservice_type');
        $this->readOnlyField($viewName, 'idempresa');
        $this->readOnlyField($viewName, 'importe');
        $this->readOnlyField($viewName, 'codimpuesto');
        $this->readOnlyField($viewName, 'importe_enextranjero');
        $this->readOnlyField($viewName, 'codimpuesto_enextranjero');
        $this->readOnlyField($viewName, 'codsubcuenta_km_nacional');
        $this->readOnlyField($viewName, 'codsubcuenta_km_extranjero');
        $this->readOnlyField($viewName, 'inicio_horaAnt');
        $this->readOnlyField($viewName, 'inicio_dia');
        $this->readOnlyField($viewName, 'inicio_hora');
        $this->readOnlyField($viewName, 'fin_dia');
        $this->readOnlyField($viewName, 'fin_hora');
        $this->readOnlyField($viewName, 'idvehicle');
        $this->readOnlyField($viewName, 'iddriver_1');
        $this->readOnlyField($viewName, 'driver_alojamiento_1');
        $this->readOnlyField($viewName, 'driver_observaciones_1');
        $this->readOnlyField($viewName, 'iddriver_2');
        $this->readOnlyField($viewName, 'driver_alojamiento_2');
        $this->readOnlyField($viewName, 'driver_observaciones_2');
        $this->readOnlyField($viewName, 'iddriver_3');
        $this->readOnlyField($viewName, 'driver_alojamiento_3');
        $this->readOnlyField($viewName, 'driver_observaciones_3');
        $this->readOnlyField($viewName, 'observaciones');
        $this->readOnlyField($viewName, 'observaciones_montaje');
        $this->readOnlyField($viewName, 'observaciones_drivers');
        $this->readOnlyField($viewName, 'observaciones_vehiculo');
        $this->readOnlyField($viewName, 'observaciones_facturacion');
        $this->readOnlyField($viewName, 'observaciones_liquidacion');
        $this->readOnlyField($viewName, 'motivobaja');
        $this->readOnlyField($viewName, 'idhelper');
    }

    private function displayOnlyFieldsForDiscretionalServ($viewName)
    {
        // Es un discrecional, por lo que se ponen invisibles estos campos
        $this->displayNoneField($viewName, 'cod_servicio');
        $this->displayNoneField($viewName, 'fuera_del_municipio');
        $this->displayNoneField($viewName, 'facturar_SN');
        $this->displayNoneField($viewName, 'facturar_agrupando');
        $this->displayNoneField($viewName, 'salida_desde_nave_sn');
        $this->displayNoneField($viewName, 'activo');
    }

    private function displayOnlyFieldsForRegularServ($viewName)
    {
        // Es un regular, por lo que se ponen invisibles estos campos
        $this->displayNoneField($viewName, 'idservice');
        $this->displayNoneField($viewName, 'fuera_del_municipio_text');
        $this->displayNoneField($viewName, 'facturar_SN_text');
        $this->displayNoneField($viewName, 'facturar_agrupando_text');
        $this->displayNoneField($viewName, 'salida_desde_nave_text');
        $this->displayNoneField($viewName, 'activo_text');
    }

    private function readOnlyFields($viewName)
    {
        
        if (!empty($this->views[$viewName]->model->idfactura)) 
        { // Está facturado el servicio
            $this->readOnlyAllCommonFields($viewName); // Da igual que sea discrecional o no, los campos comunes a readonly=true
            $this->displayOnlyFieldsForDiscretionalServ($viewName); // Se hacen invisibles los campos que sólo son para regulares
        } else {
            // No está facturado el servicio
            
            // Comprobamos si es discrecional o regular
            if (!empty($this->views[$viewName]->model->idservice)) 
            {
                // Discrecional
                $this->readOnlyAllCommonFields($viewName); // Los campos comunes a readonly=true
                $this->displayOnlyFieldsForDiscretionalServ($viewName);
            } else {
                // Regular, así que las columnas comunes las dejamos como estén en la vista
                
                // Se dejan
                $this->displayOnlyFieldsForRegularServ($viewName);
            }
        }
    }

    private function prepararFechasParaVista($viewName)
    {
        if (!empty($this->views[$viewName]->model->fecha_desde)){
            $this->views[$viewName]->model->inicio_dia = date("Y-m-d", strtotime($this->views[$viewName]->model->fecha_desde));
        } else {
            $this->views[$viewName]->model->inicio_dia = null;
        }

        if (!empty($this->views[$viewName]->model->fecha_hasta)){
            $this->views[$viewName]->model->fin_dia = date("Y-m-d", strtotime($this->views[$viewName]->model->fecha_hasta));
        } else {
            $this->views[$viewName]->model->fin_dia = null;
        }
    }

    private function prepararHorasParaVista($viewName)
    {
        if (!empty($this->views[$viewName]->model->hora_anticipacion)){
            $this->views[$viewName]->model->inicio_horaAnt = date("H:i:s", strtotime($this->views[$viewName]->model->hora_anticipacion));
        } else {
            $this->views[$viewName]->model->inicio_horaAnt = null;
        }
        
        if (!empty($this->views[$viewName]->model->hora_desde)){
            $this->views[$viewName]->model->inicio_hora = date("H:i:s", strtotime($this->views[$viewName]->model->hora_desde));
        } else {
            $this->views[$viewName]->model->inicio_hora = null;
        }

        if (!empty($this->views[$viewName]->model->hora_hasta)){
            $this->views[$viewName]->model->fin_hora = date("H:i:s", strtotime($this->views[$viewName]->model->hora_hasta));
        } else {
            $this->views[$viewName]->model->fin_hora = null;
        }
    }
    
}
