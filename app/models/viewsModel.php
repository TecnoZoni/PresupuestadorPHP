<?php

namespace app\models;

class viewsModel
{

    protected function obtenerVistasModelo($vista)
    {
        $listaBlanca = ["dashboard", "clientNew", "clientList", "clientSearch", "clientUpdate", "productSearch", "productNew", "productUpdate", "productList", "configBoard", "configPhoto", "presupuestoNew", "presupuestoList", "presupuestoSearch", "presupuestoUpdate"];

        if (in_array($vista, $listaBlanca)) {
            if (is_file("./app/views/content/" . $vista . "-view.php")) {
                $contenido = "./app/views/content/" . $vista . "-view.php";
            } else {
                $contenido = "404";
            }
        } else {
            $contenido = "404";
        }
        return $contenido;
    }
}
