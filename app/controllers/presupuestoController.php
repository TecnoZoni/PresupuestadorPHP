<?php

namespace app\controllers;

use app\models\mainModel;

use app\library\fpdf\FPDF;

class invoiceController extends mainModel
{

    public function registrarFacturaControlador()
    {

        $cliente_id = $this->limpiarCadena($_POST['cliente_id'] ?? '');
        $factura_total = 0;

        $ids   = $_POST['producto_id'] ?? [];
        $codigos   = $_POST['producto_codigo'] ?? [];
        $nombres   = $_POST['producto_nombre'] ?? [];
        $precios   = $_POST['producto_precio'] ?? [];
        $cantidades = $_POST['producto_cantidad'] ?? [];

        $productos = [];

        for ($i = 0; $i < count($codigos); $i++) {
            $id = $this->limpiarCadena($ids[$i]);
            $codigo = $this->limpiarCadena($codigos[$i]);
            $nombre = $this->limpiarCadena($nombres[$i]);
            $precio = floatval($precios[$i]);
            $cantidad = intval($cantidades[$i]);

            if ($codigo && $nombre && $precio > 0 && $cantidad > 0) {
                $productos[] = [
                    'id' => $id,
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'precio' => $precio,
                    'cantidad' => $cantidad,
                    'subtotal' => $precio * $cantidad
                ];
            }
            $factura_total += $precio * $cantidad;
        }

        if ($cliente_id == "" || empty($ids) || empty($codigos) || empty($nombres) || empty($precios) || empty($cantidades)) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No has llenado todos los campos que son obligatorios",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        $check_cliente = $this->ejecutarConsulta("SELECT cliente_id FROM cliente WHERE cliente_id='$cliente_id'");
        if ($check_cliente->rowCount() == 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "El CLIENTE ingresado no se encuentra registrado, por favor elija otro",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        $factura_datos_reg = [
            [
                "campo_nombre" => "cliente_id",
                "campo_marcador" => ":Cliente_id",
                "campo_valor" => $cliente_id
            ],
            [
                "campo_nombre" => "factura_total",
                "campo_marcador" => ":Factura_total",
                "campo_valor" => $factura_total
            ],
            [
                "campo_nombre" => "factura_fecha",
                "campo_marcador" => ":Factura_fecha",
                "campo_valor" => date("Y-m-d H:i:s")
            ]
        ];

        $registrar_factura = $this->guardarDatos("factura", $factura_datos_reg);
        $factura_id = $registrar_factura['id'];

        if (!is_numeric($factura_id) || $factura_id <= 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Error al guardar",
                "texto" => "No se pudo obtener el ID de la factura",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        $detalles_guardados = 0;

        foreach ($productos as $producto) {
            $detalle_factura_datos_reg = [
                [
                    "campo_nombre" => "factura_id",
                    "campo_marcador" => ":Factura_id",
                    "campo_valor" => $factura_id
                ],
                [
                    "campo_nombre" => "producto_id",
                    "campo_marcador" => ":Producto_id",
                    "campo_valor" => $producto['id']
                ],
                [
                    "campo_nombre" => "detalle_factura_cantidad",
                    "campo_marcador" => ":Detalle_factura_cantidad",
                    "campo_valor" => $producto['cantidad']
                ],
                [
                    "campo_nombre" => "detalle_factura_precio_unitario",
                    "campo_marcador" => ":Detalle_factura_precio_unitario",
                    "campo_valor" => $producto['precio']
                ],
                [
                    "campo_nombre" => "detalle_factura_subtotal",
                    "campo_marcador" => ":Detalle_factura_subtotal",
                    "campo_valor" => $producto['subtotal']
                ],
            ];

            $registrar_detalle_factura = $this->guardarDatos("detalle_factura", $detalle_factura_datos_reg);
            if ($registrar_detalle_factura['stmt']->rowCount() == 1) {
                $detalles_guardados++;
            }
        };


        if ($registrar_factura['stmt']->rowCount() == 1 && $detalles_guardados == count($productos)) {
            $alerta = [
                "tipo" => "limpiar",
                "titulo" => "Factura registrada",
                "texto" => "La factura se registro con exito",
                "icono" => "success"
            ];
        } else {

            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No se pudo registrar la factura, por favor intente nuevamente",
                "icono" => "error"
            ];
        }

        return json_encode($alerta);
    }

    public function listarFacturaControlador($pagina, $registros, $url, $busqueda)
    {

        $pagina = $this->limpiarCadena($pagina);
        $registros = $this->limpiarCadena($registros);

        $url = $this->limpiarCadena($url);
        $url = APP_URL . $url . "/";

        $busqueda = $this->limpiarCadena($busqueda);
        $tabla = "";

        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        if (isset($busqueda) && $busqueda != "") {

            $consulta_datos = "SELECT 
                                    f.factura_id,
                                    f.factura_fecha,
                                    f.factura_total,
                                    c.cliente_id,
                                    c.cliente_nombre,
                                    c.cliente_apellido,
                                    c.cliente_telefono,
                                    c.cliente_email
                                FROM factura f
                                INNER JOIN cliente c ON f.cliente_id = c.cliente_id
                                WHERE (
                                    c.cliente_nombre LIKE '%$busqueda%' 
                                    OR c.cliente_apellido LIKE '%$busqueda%' 
                                    OR c.cliente_email LIKE '%$busqueda%' 
                                    OR c.cliente_telefono LIKE '%$busqueda%' 
                                    OR f.factura_id LIKE '%$busqueda%' 
                                    OR f.factura_fecha LIKE '%$busqueda%'
                                )
                                ORDER BY f.factura_id DESC
                                LIMIT $inicio,$registros
                            ";


            $consulta_total = "SELECT COUNT(f.factura_id)
                                FROM factura f
                                INNER JOIN cliente c ON f.cliente_id = c.cliente_id
                                WHERE (
                                    c.cliente_nombre LIKE '%$busqueda%' 
                                    OR c.cliente_apellido LIKE '%$busqueda%' 
                                    OR c.cliente_email LIKE '%$busqueda%' 
                                    OR c.cliente_telefono LIKE '%$busqueda%' 
                                    OR f.factura_id LIKE '%$busqueda%' 
                                    OR f.factura_fecha LIKE '%$busqueda%'
                                )";
        } else {

            $consulta_datos = "SELECT 
                                    f.factura_id,
                                    f.factura_fecha,
                                    f.factura_total,
                                    c.cliente_id,
                                    c.cliente_nombre,
                                    c.cliente_apellido,
                                    c.cliente_telefono,
                                    c.cliente_email
                                FROM factura f
                                INNER JOIN cliente c 
                                    ON f.cliente_id = c.cliente_id
                                ORDER BY f.factura_id DESC                                
                                LIMIT $inicio,$registros";

            $consulta_total = "SELECT COUNT(f.factura_id) FROM factura f INNER JOIN cliente c ON f.cliente_id = c.cliente_id;";
        }

        $datos = $this->ejecutarConsulta($consulta_datos);
        $datos = $datos->fetchAll();

        $total = $this->ejecutarConsulta($consulta_total);
        $total = (int) $total->fetchColumn();

        $numeroPaginas = ceil($total / $registros);

        $tabla .= '
    <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th>#Numero de Factura</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th>Total</th>
                <th colspan="3">Opciones</th>
            </tr>
        </thead>
        <tbody>
';

        if ($total >= 1 && $pagina <= $numeroPaginas) {
            $contador = $inicio + 1;
            $pag_inicio = $inicio + 1;
            foreach ($datos as $rows) {
                $tabla .= '
            <tr>
                <td>' . $rows['factura_id'] . '</td>
                <td>' . $rows['factura_fecha'] . '</td>
                <td>' . $rows['cliente_nombre'] . ' ' . $rows['cliente_apellido'] . '</td>
                <td>' . $rows['cliente_telefono'] . '</td>
                <td>' . $rows['factura_total'] . '</td>
                <td>
                    <a href="' . APP_URL . 'invoiceUpdate/' . $rows['factura_id'] . '/" class="btn btn-success btn-sm">Actualizar</a>
                </td>
                <td>
                    <form class="FormularioAjax" action="' . APP_URL . 'app/ajax/facturaAjax.php" method="POST" autocomplete="off">
                        <input type="hidden" name="modulo_factura" value="eliminar">
                        <input type="hidden" name="factura_id" value="' . $rows['factura_id'] . '">
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </td>
                <td>
                    <form class="" action="' . APP_URL . 'app/ajax/facturaAjax.php" method="POST" autocomplete="off" target="_blank">
                        <input type="hidden" name="modulo_factura" value="generarPDF">
                        <input type="hidden" name="factura_id" value="' . $rows['factura_id'] . '">
                        <button type="submit" class="btn btn-outline-danger btn-sm">Generar PDF</button>
                    </form>
                </td>
            </tr>
        ';
                $contador++;
            }
            $pag_final = $contador - 1;
        } else {
            if ($total >= 1) {
                $tabla .= '
            <tr>
                <td colspan="8">
                    <a href="' . $url . '1/" class="btn btn-primary mt-4 mb-4">
                        Haga clic acá para recargar el listado
                    </a>
                </td>
            </tr>
        ';
            } else {
                $tabla .= '
            <tr>
                <td colspan="8">No hay registros en el sistema</td>
            </tr>
        ';
            }
        }

        $tabla .= '</tbody></table></div>';

        if ($total > 0 && $pagina <= $numeroPaginas) {
            $tabla .= '<p class="text-end">Mostrando facturas <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p>';
            $tabla .= $this->paginadorTablas($pagina, $numeroPaginas, $url, 7);
        }

        return $tabla;
    }

    public function eliminarFacturaControlador()
    {

        $id = $this->limpiarCadena($_POST['factura_id']);

        $datos = $this->ejecutarConsulta("SELECT * FROM factura WHERE factura_id='$id'");
        if ($datos->rowCount() <= 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No hemos encontrado la factura en el sistema",
                "icono" => "error"
            ];
            return json_encode($alerta);
        } else {
            $datos = $datos->fetch();
        }

        $eliminarDetalleFactura = $this->eliminarRegistro("detalle_factura", "factura_id", $id);

        if ($eliminarDetalleFactura->rowCount() >= 1) {


            $eliminarFactura = $this->eliminarRegistro("factura", "factura_id", $id);

            if ($eliminarFactura->rowCount() == 1) {
                $alerta = [
                    "tipo" => "recargar",
                    "titulo" => "Factura eliminada",
                    "texto" => "La factura N°" . $datos['factura_id'] . " ha sido eliminado del sistema correctamente",
                    "icono" => "success"
                ];
            } else {
                $alerta = [
                    "tipo" => "simple",
                    "titulo" => "Ocurrió un error inesperado",
                    "texto" => "No hemos podido eliminar la factura N°" . $datos['factura_id'] . " del sistema, por favor intente nuevamente",
                    "icono" => "error"
                ];
            }
        } else {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No hemos podido eliminar la factura N°" . $datos['factura_id'] . " del sistema, por favor intente nuevamente",
                "icono" => "error"
            ];
        }

        return json_encode($alerta);
    }

    public function actualizarFacturaControlador()
    {

        $id = $this->limpiarCadena($_POST['factura_id']);

        $datos = $this->ejecutarConsulta("SELECT * FROM factura WHERE factura_id='$id'");
        if ($datos->rowCount() <= 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No hemos encontrado la factura en el sistema",
                "icono" => "error"
            ];
            return json_encode($alerta);
        } else {
            $datos = $datos->fetch();
        }

        $cliente_id = $this->limpiarCadena($_POST['cliente_id'] ?? '');
        $factura_total = 0;

        $ids   = $_POST['producto_id'] ?? [];
        $codigos   = $_POST['producto_codigo'] ?? [];
        $nombres   = $_POST['producto_nombre'] ?? [];
        $precios   = $_POST['producto_precio'] ?? [];
        $cantidades = $_POST['producto_cantidad'] ?? [];

        $productos = [];

        for ($i = 0; $i < count($codigos); $i++) {
            $producto_id = $this->limpiarCadena($ids[$i]);
            $codigo = $this->limpiarCadena($codigos[$i]);
            $nombre = $this->limpiarCadena($nombres[$i]);
            $precio = floatval($precios[$i]);
            $cantidad = intval($cantidades[$i]);

            if ($codigo && $nombre && $precio > 0 && $cantidad > 0) {
                $productos[] = [
                    'id' => $producto_id,
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'precio' => $precio,
                    'cantidad' => $cantidad,
                    'subtotal' => $precio * $cantidad
                ];
            }
            $factura_total += $precio * $cantidad;
        }

        if ($cliente_id == "" || empty($ids) || empty($codigos) || empty($nombres) || empty($precios) || empty($cantidades)) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No has llenado todos los campos que son obligatorios",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        $check_cliente = $this->ejecutarConsulta("SELECT cliente_id FROM cliente WHERE cliente_id='$cliente_id'");
        if ($check_cliente->rowCount() == 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "El CLIENTE ingresado no se encuentra registrado, por favor elija otro",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        $factura_datos_up = [
            [
                "campo_nombre" => "cliente_id",
                "campo_marcador" => ":Cliente_id",
                "campo_valor" => $cliente_id
            ],
            [
                "campo_nombre" => "factura_total",
                "campo_marcador" => ":Factura_total",
                "campo_valor" => $factura_total
            ],
            [
                "campo_nombre" => "factura_fecha",
                "campo_marcador" => ":Factura_fecha",
                "campo_valor" => date("Y-m-d H:i:s")
            ]
        ];


        $condicion = [
            "condicion_campo" => "factura_id",
            "condicion_marcador" => ":ID",
            "condicion_valor" => $id
        ];

        $actualizar_factura = $this->actualizarDatos("factura", $factura_datos_up, $condicion);

        $factura_id = $id;

        if (!is_numeric($factura_id) || $factura_id <= 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Error al guardar",
                "texto" => "No se pudo obtener el ID de la factura",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        $detalles_actualizados = 0;

        $this->eliminarRegistro("detalle_factura", "factura_id", $id);

        foreach ($productos as $producto) {
            $detalle_factura_datos_up = [
                [
                    "campo_nombre" => "factura_id",
                    "campo_marcador" => ":Factura_id",
                    "campo_valor" => $factura_id
                ],
                [
                    "campo_nombre" => "producto_id",
                    "campo_marcador" => ":Producto_id",
                    "campo_valor" => $producto['id']
                ],
                [
                    "campo_nombre" => "detalle_factura_cantidad",
                    "campo_marcador" => ":Detalle_factura_cantidad",
                    "campo_valor" => $producto['cantidad']
                ],
                [
                    "campo_nombre" => "detalle_factura_precio_unitario",
                    "campo_marcador" => ":Detalle_factura_precio_unitario",
                    "campo_valor" => $producto['precio']
                ],
                [
                    "campo_nombre" => "detalle_factura_subtotal",
                    "campo_marcador" => ":Detalle_factura_subtotal",
                    "campo_valor" => $producto['subtotal']
                ],
            ];


            $condicion = [
                "condicion_campo" => "detalle_factura_id",
                "condicion_marcador" => ":ID",
                "condicion_valor" => $id
            ];

            $actualizar_detalle_factura = $this->guardarDatos("detalle_factura", $detalle_factura_datos_up);
            if ($actualizar_detalle_factura["stmt"]->rowCount() == 1) {
                $detalles_actualizados++;
            }
        };


        if ($actualizar_factura->rowCount() == 1 && $detalles_actualizados == count($productos)) {
            $alerta = [
                "tipo" => "recargar",
                "titulo" => "Factura actualizada",
                "texto" => "La factura se actualizo con exito",
                "icono" => "success"
            ];
        } else {

            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No se pudo actualizar la factura, por favor intente nuevamente",
                "icono" => "error"
            ];
        }

        return json_encode($alerta);
    }

    public function generarFacturaControlador()
    {

        $datosNegocio = $this->ejecutarConsulta("SELECT * FROM configuracion where configuracion_id= 1");
        if ($datosNegocio->rowCount() <= 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No hemos encontrado la configuracion del negocio en el sistema",
                "icono" => "error"
            ];
            return json_encode($alerta);
        } else {
            $datosNegocio = $datosNegocio->fetch();
        }

        $id = $this->limpiarCadena($_POST['factura_id']);

        $checkFactura = $this->ejecutarConsulta("SELECT * FROM factura WHERE factura_id='$id'");
        if ($checkFactura->rowCount() <= 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No hemos encontrado la factura en el sistema",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        $sql = "SELECT f.factura_id, f.cliente_id, f.factura_fecha, f.factura_total,
               d.detalle_factura_id, d.producto_id, d.detalle_factura_cantidad,
               d.detalle_factura_precio_unitario, d.detalle_factura_subtotal,
               p.producto_codigo, p.producto_nombre, p.producto_precio, p.producto_descripcion,
               c.cliente_nombre, c.cliente_apellido, c.cliente_telefono, c.cliente_email
        FROM factura f
        INNER JOIN detalle_factura d ON f.factura_id = d.factura_id
        INNER JOIN producto p ON d.producto_id = p.producto_id
        INNER JOIN cliente c ON f.cliente_id = c.cliente_id
        WHERE f.factura_id = $id;";

        $datosFactura = $this->ejecutarConsulta($sql);
        if ($datosFactura->rowCount() <= 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No hemos encontrado los detalles de la factura en el sistema",
                "icono" => "error"
            ];
            return json_encode($alerta);
        } else {
            $datosFactura = $datosFactura->fetchAll();
        }

        function convertirUTF8($texto)
        {
            return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
        }

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();

        $logoPath = "../views/fotos/" . $datosNegocio['configuracion_logo'];

        if (!empty($datosNegocio['configuracion_logo']) && file_exists($logoPath)) {
            $pdf->Image($logoPath, 10, 10, 40);
        }

        // Datos de la empresa
        $pdf->SetFont('Arial', 'B', 15);
        $pdf->SetXY(130, 10);
        $pdf->Cell(70, 5, "Datos de la empresa", 0, 1, 'L');

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(130, 20);
        $pdf->Cell(70, 5,   convertirUTF8("Nombre: " . $datosNegocio['configuracion_nombre']), 0, 1, 'L');
        $pdf->SetXY(130, 25);
        $pdf->Cell(70, 5,   convertirUTF8("Teléfono: " . $datosNegocio['configuracion_telefono']), 0, 1, 'L');
        $pdf->SetXY(130, 30);
        $pdf->Cell(70, 5, convertirUTF8("Email: " . $datosNegocio['configuracion_email']), 0, 1, 'L');
        $pdf->SetXY(130, 35);
        $pdf->Cell(70, 5, convertirUTF8("CUIT: " . $datosNegocio['configuracion_cuit']), 0, 1, 'L');
        $pdf->SetXY(130, 40);
        $pdf->Cell(70, 5, convertirUTF8("Dirección: " . $datosNegocio['configuracion_direccion']), 0, 1, 'L');

        $datosFacturaGeneral = $datosFactura[0];

        // Datos del cliente
        $pdf->SetXY(10, 60);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 6, "Datos del cliente", 0, 1);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 6, convertirUTF8("Nombre: " . $datosFacturaGeneral['cliente_nombre'] . " " . $datosFacturaGeneral['cliente_apellido']), 0, 1);
        $pdf->Cell(0, 6, convertirUTF8("Teléfono: " . $datosFacturaGeneral['cliente_telefono']), 0, 1);
        $pdf->Cell(0, 6, convertirUTF8("Email: " . $datosFacturaGeneral['cliente_email']), 0, 1);

        // Número y fecha de factura
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 6, convertirUTF8("Factura N° ") . $datosFacturaGeneral['factura_id'], 0, 1);
        $pdf->Cell(0, 6, "Fecha: " . $datosFacturaGeneral['factura_fecha'], 0, 1);

        // Tabla de productos
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(40, 7, convertirUTF8('Código'), 1);
        $pdf->Cell(60, 7, 'Producto', 1);
        $pdf->Cell(20, 7, 'Cant.', 1, 0, 'C');
        $pdf->Cell(35, 7, 'Precio Unit.', 1, 0, 'R');
        $pdf->Cell(35, 7, 'Subtotal', 1, 1, 'R');

        $pdf->SetFont('Arial', '', 11);

        foreach ($datosFactura as $row) {
            $pdf->Cell(40, 6, convertirUTF8($row['producto_codigo']), 1);
            $pdf->Cell(60, 6, convertirUTF8($row['producto_nombre']), 1);
            $pdf->Cell(20, 6, $row['detalle_factura_cantidad'], 1, 0, 'C');
            $pdf->Cell(35, 6, number_format($row['detalle_factura_precio_unitario'], 2), 1, 0, 'R');
            $pdf->Cell(35, 6, number_format($row['detalle_factura_subtotal'], 2), 1, 1, 'R');
        }

        // Total
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(155, 7, 'Total', 1);
        $pdf->Cell(35, 7, '$' . number_format($datosFacturaGeneral['factura_total'], 2), 1, 1, 'R');

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="factura_' . $id . '.pdf"');

        $pdf->Output('I'); // 'I' = inline, lo abre en el navegador
        exit;
    }
}
