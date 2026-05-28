<?php

require_once "../../config/app.php";
require_once  "../views/inc/session_start.php";
require_once "../../autoload.php";

use app\controllers\invoiceController;

if (isset($_POST["modulo_factura"])) {

    $insFactura = new invoiceController();

    if ($_POST["modulo_factura"] == "crear") {
        echo $insFactura->registrarFacturaControlador();
    }
    if ($_POST["modulo_factura"] == "eliminar") {
        echo $insFactura->eliminarFacturaControlador();
    }
    if ($_POST["modulo_factura"] == "actualizar") {
        echo $insFactura->actualizarFacturaControlador();
    }
    if ($_POST["modulo_factura"] == "generarPDF") {
        echo $insFactura->generarFacturaControlador();
    }
} else {
    session_destroy();
    header("Location: " . APP_URL . "dashboard/");
}
