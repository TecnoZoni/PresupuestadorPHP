<?php

require_once "../../config/app.php";
require_once  "../views/inc/session_start.php";
require_once "../../autoload.php";

use app\controllers\presupuestoController;

if (isset($_POST["modulo_presupuesto"])) {

    $insPresupuesto = new presupuestoController();

    if ($_POST["modulo_presupuesto"] == "crear") {
        echo $insPresupuesto->registrarPresupuestoControlador();
    }
    if ($_POST["modulo_presupuesto"] == "eliminar") {
        echo $insPresupuesto->eliminarPresupuestoControlador();
    }
    if ($_POST["modulo_presupuesto"] == "actualizar") {
        echo $insPresupuesto->actualizarPresupuestoControlador();
    }
    if ($_POST["modulo_presupuesto"] == "generarPDF") {
        echo $insPresupuesto->generarPresupuestoControlador();
    }
} else {
    session_destroy();
    header("Location: " . APP_URL . "dashboard/");
}
