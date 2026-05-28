<?php

namespace app\controllers;

use app\models\mainModel;

class searchController extends mainModel
{
    public function modulosBusquedaControlador($modulo)
    {

        $listaModulos = ['clientSearch', 'productSearch', 'presupuestoSearch'];

        if (in_array($modulo, $listaModulos)) {
            return false;
        } else {
            return true;
        }
    }

    public function iniciarBuscadorControlador()
    {

        $url = $this->limpiarCadena($_POST['modulo_url']);
        $texto = $this->limpiarCadena($_POST['txt_buscador']);

        if ($this->modulosBusquedaControlador($url)) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No podemos procesar la petición en este momento",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        if ($texto == "") {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "Introduce un termino de busqueda",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        if ($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,30}", $texto)) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "El termino de busqueda no coincide con el formato solicitado",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        $_SESSION[$url] = $texto;

        $alerta = [
            "tipo" => "redireccionar",
            "url" => APP_URL . $url . "/"
        ];

        return json_encode($alerta);
    }


    public function eliminarBuscadorControlador()
    {

        $url = $this->limpiarCadena($_POST['modulo_url']);

        if ($this->modulosBusquedaControlador($url)) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No podemos procesar la petición en este momento",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        unset($_SESSION[$url]);

        $alerta = [
            "tipo" => "redireccionar",
            "url" => APP_URL . $url . "/"
        ];

        return json_encode($alerta);
    }
}
