<?php

namespace app\controllers;

use app\models\mainModel;

use app\library\fpdf\FPDF;
use app\library\pdf\PresupuestoPDF;

class presupuestoController extends mainModel
{

    public function registrarPresupuestoControlador()
    {

        $cliente_id = $this->limpiarCadena($_POST['cliente_id'] ?? '');
        $presupuesto_total = 0;

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
            $presupuesto_total += $precio * $cantidad;
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

        $presupuesto_datos_reg = [
            [
                "campo_nombre" => "cliente_id",
                "campo_marcador" => ":Cliente_id",
                "campo_valor" => $cliente_id
            ],
            [
                "campo_nombre" => "presupuesto_total",
                "campo_marcador" => ":Presupuesto_total",
                "campo_valor" => $presupuesto_total
            ],
            [
                "campo_nombre" => "presupuesto_fecha",
                "campo_marcador" => ":Presupuesto_fecha",
                "campo_valor" => date("Y-m-d H:i:s")
            ]
        ];

        $registrar_presupuesto = $this->guardarDatos("presupuesto", $presupuesto_datos_reg);
        $presupuesto_id = $registrar_presupuesto['id'];

        if (!is_numeric($presupuesto_id) || $presupuesto_id <= 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Error al guardar",
                "texto" => "No se pudo obtener el ID del presupuesto",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        $detalles_guardados = 0;

        foreach ($productos as $producto) {
            $detalle_presupuesto_datos_reg = [
                [
                    "campo_nombre" => "presupuesto_id",
                    "campo_marcador" => ":Presupuesto_id",
                    "campo_valor" => $presupuesto_id
                ],
                [
                    "campo_nombre" => "producto_id",
                    "campo_marcador" => ":Producto_id",
                    "campo_valor" => $producto['id']
                ],
                [
                    "campo_nombre" => "detalle_presupuesto_cantidad",
                    "campo_marcador" => ":Detalle_presupuesto_cantidad",
                    "campo_valor" => $producto['cantidad']
                ],
                [
                    "campo_nombre" => "detalle_presupuesto_precio_unitario",
                    "campo_marcador" => ":Detalle_presupuesto_precio_unitario",
                    "campo_valor" => $producto['precio']
                ],
                [
                    "campo_nombre" => "detalle_presupuesto_subtotal",
                    "campo_marcador" => ":Detalle_presupuesto_subtotal",
                    "campo_valor" => $producto['subtotal']
                ],
            ];

            $registrar_detalle_presupuesto = $this->guardarDatos("detalle_presupuesto", $detalle_presupuesto_datos_reg);
            if ($registrar_detalle_presupuesto['stmt']->rowCount() == 1) {
                $detalles_guardados++;
            }
        };


        if ($registrar_presupuesto['stmt']->rowCount() == 1 && $detalles_guardados == count($productos)) {
            $alerta = [
                "tipo" => "limpiar",
                "titulo" => "Presupuesto registrado",
                "texto" => "El presupuesto se registro con exito",
                "icono" => "success"
            ];
        } else {

            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No se pudo registrar el presupuesto, por favor intente nuevamente",
                "icono" => "error"
            ];
        }

        return json_encode($alerta);
    }

    public function listarPresupuestoControlador($pagina, $registros, $url, $busqueda)
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
                                    f.presupuesto_id,
                                    f.presupuesto_fecha,
                                    f.presupuesto_total,
                                    c.cliente_id,
                                    c.cliente_nombre,
                                    c.cliente_apellido,
                                    c.cliente_telefono,
                                    c.cliente_email
                                FROM presupuesto f
                                INNER JOIN cliente c ON f.cliente_id = c.cliente_id
                                WHERE (
                                    c.cliente_nombre LIKE '%$busqueda%'
                                    OR c.cliente_apellido LIKE '%$busqueda%'
                                    OR c.cliente_email LIKE '%$busqueda%'
                                    OR c.cliente_telefono LIKE '%$busqueda%'
                                    OR f.presupuesto_id LIKE '%$busqueda%'
                                    OR f.presupuesto_fecha LIKE '%$busqueda%'
                                )
                                ORDER BY f.presupuesto_id DESC
                                LIMIT $inicio,$registros
                            ";


            $consulta_total = "SELECT COUNT(f.presupuesto_id)
                                FROM presupuesto f
                                INNER JOIN cliente c ON f.cliente_id = c.cliente_id
                                WHERE (
                                    c.cliente_nombre LIKE '%$busqueda%'
                                    OR c.cliente_apellido LIKE '%$busqueda%'
                                    OR c.cliente_email LIKE '%$busqueda%'
                                    OR c.cliente_telefono LIKE '%$busqueda%'
                                    OR f.presupuesto_id LIKE '%$busqueda%'
                                    OR f.presupuesto_fecha LIKE '%$busqueda%'
                                )";
        } else {

            $consulta_datos = "SELECT
                                    f.presupuesto_id,
                                    f.presupuesto_fecha,
                                    f.presupuesto_total,
                                    c.cliente_id,
                                    c.cliente_nombre,
                                    c.cliente_apellido,
                                    c.cliente_telefono,
                                    c.cliente_email
                                FROM presupuesto f
                                INNER JOIN cliente c
                                    ON f.cliente_id = c.cliente_id
                                ORDER BY f.presupuesto_id DESC
                                LIMIT $inicio,$registros";

            $consulta_total = "SELECT COUNT(f.presupuesto_id) FROM presupuesto f INNER JOIN cliente c ON f.cliente_id = c.cliente_id;";
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
                <th>#Numero de Presupuesto</th>
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
                <td>' . $rows['presupuesto_id'] . '</td>
                <td>' . $rows['presupuesto_fecha'] . '</td>
                <td>' . $rows['cliente_nombre'] . ' ' . $rows['cliente_apellido'] . '</td>
                <td>' . $rows['cliente_telefono'] . '</td>
                <td>' . $rows['presupuesto_total'] . '</td>
                <td>
                    <a href="' . APP_URL . 'presupuestoUpdate/' . $rows['presupuesto_id'] . '/" class="btn btn-success btn-sm">Actualizar</a>
                </td>
                <td>
                    <form class="FormularioAjax" action="' . APP_URL . 'app/ajax/presupuestoAjax.php" method="POST" autocomplete="off">
                        <input type="hidden" name="modulo_presupuesto" value="eliminar">
                        <input type="hidden" name="presupuesto_id" value="' . $rows['presupuesto_id'] . '">
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </td>
                <td>
                    <form class="" action="' . APP_URL . 'app/ajax/presupuestoAjax.php" method="POST" autocomplete="off" target="_blank">
                        <input type="hidden" name="modulo_presupuesto" value="generarPDF">
                        <input type="hidden" name="presupuesto_id" value="' . $rows['presupuesto_id'] . '">
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
            $tabla .= '<p class="text-end">Mostrando presupuestos <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p>';
            $tabla .= $this->paginadorTablas($pagina, $numeroPaginas, $url, 7);
        }

        return $tabla;
    }

    public function eliminarPresupuestoControlador()
    {

        $id = $this->limpiarCadena($_POST['presupuesto_id']);

        $datos = $this->ejecutarConsulta("SELECT * FROM presupuesto WHERE presupuesto_id='$id'");
        if ($datos->rowCount() <= 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No hemos encontrado el presupuesto en el sistema",
                "icono" => "error"
            ];
            return json_encode($alerta);
        } else {
            $datos = $datos->fetch();
        }

        $eliminarDetallePresupuesto = $this->eliminarRegistro("detalle_presupuesto", "presupuesto_id", $id);

        if ($eliminarDetallePresupuesto->rowCount() >= 1) {


            $eliminarPresupuesto = $this->eliminarRegistro("presupuesto", "presupuesto_id", $id);

            if ($eliminarPresupuesto->rowCount() == 1) {
                $alerta = [
                    "tipo" => "recargar",
                    "titulo" => "Presupuesto eliminado",
                    "texto" => "El presupuesto N°" . $datos['presupuesto_id'] . " ha sido eliminado del sistema correctamente",
                    "icono" => "success"
                ];
            } else {
                $alerta = [
                    "tipo" => "simple",
                    "titulo" => "Ocurrió un error inesperado",
                    "texto" => "No hemos podido eliminar el presupuesto N°" . $datos['presupuesto_id'] . " del sistema, por favor intente nuevamente",
                    "icono" => "error"
                ];
            }
        } else {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No hemos podido eliminar el presupuesto N°" . $datos['presupuesto_id'] . " del sistema, por favor intente nuevamente",
                "icono" => "error"
            ];
        }

        return json_encode($alerta);
    }

    public function actualizarPresupuestoControlador()
    {

        $id = $this->limpiarCadena($_POST['presupuesto_id']);

        $datos = $this->ejecutarConsulta("SELECT * FROM presupuesto WHERE presupuesto_id='$id'");
        if ($datos->rowCount() <= 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No hemos encontrado el presupuesto en el sistema",
                "icono" => "error"
            ];
            return json_encode($alerta);
        } else {
            $datos = $datos->fetch();
        }

        $cliente_id = $this->limpiarCadena($_POST['cliente_id'] ?? '');
        $presupuesto_total = 0;

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
            $presupuesto_total += $precio * $cantidad;
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

        $presupuesto_datos_up = [
            [
                "campo_nombre" => "cliente_id",
                "campo_marcador" => ":Cliente_id",
                "campo_valor" => $cliente_id
            ],
            [
                "campo_nombre" => "presupuesto_total",
                "campo_marcador" => ":Presupuesto_total",
                "campo_valor" => $presupuesto_total
            ],
            [
                "campo_nombre" => "presupuesto_fecha",
                "campo_marcador" => ":Presupuesto_fecha",
                "campo_valor" => date("Y-m-d H:i:s")
            ]
        ];


        $condicion = [
            "condicion_campo" => "presupuesto_id",
            "condicion_marcador" => ":ID",
            "condicion_valor" => $id
        ];

        $actualizar_presupuesto = $this->actualizarDatos("presupuesto", $presupuesto_datos_up, $condicion);

        $presupuesto_id = $id;

        if (!is_numeric($presupuesto_id) || $presupuesto_id <= 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Error al guardar",
                "texto" => "No se pudo obtener el ID del presupuesto",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        $detalles_actualizados = 0;

        $this->eliminarRegistro("detalle_presupuesto", "presupuesto_id", $id);

        foreach ($productos as $producto) {
            $detalle_presupuesto_datos_up = [
                [
                    "campo_nombre" => "presupuesto_id",
                    "campo_marcador" => ":Presupuesto_id",
                    "campo_valor" => $presupuesto_id
                ],
                [
                    "campo_nombre" => "producto_id",
                    "campo_marcador" => ":Producto_id",
                    "campo_valor" => $producto['id']
                ],
                [
                    "campo_nombre" => "detalle_presupuesto_cantidad",
                    "campo_marcador" => ":Detalle_presupuesto_cantidad",
                    "campo_valor" => $producto['cantidad']
                ],
                [
                    "campo_nombre" => "detalle_presupuesto_precio_unitario",
                    "campo_marcador" => ":Detalle_presupuesto_precio_unitario",
                    "campo_valor" => $producto['precio']
                ],
                [
                    "campo_nombre" => "detalle_presupuesto_subtotal",
                    "campo_marcador" => ":Detalle_presupuesto_subtotal",
                    "campo_valor" => $producto['subtotal']
                ],
            ];


            $condicion = [
                "condicion_campo" => "detalle_presupuesto_id",
                "condicion_marcador" => ":ID",
                "condicion_valor" => $id
            ];

            $actualizar_detalle_presupuesto = $this->guardarDatos("detalle_presupuesto", $detalle_presupuesto_datos_up);
            if ($actualizar_detalle_presupuesto["stmt"]->rowCount() == 1) {
                $detalles_actualizados++;
            }
        };


        if ($actualizar_presupuesto->rowCount() == 1 && $detalles_actualizados == count($productos)) {
            $alerta = [
                "tipo" => "recargar",
                "titulo" => "Presupuesto actualizado",
                "texto" => "El presupuesto se actualizo con exito",
                "icono" => "success"
            ];
        } else {

            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No se pudo actualizar el presupuesto, por favor intente nuevamente",
                "icono" => "error"
            ];
        }

        return json_encode($alerta);
    }

    public function generarPresupuestoControlador()
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

        $id = $this->limpiarCadena($_POST['presupuesto_id']);

        $checkPresupuesto = $this->ejecutarConsulta("SELECT * FROM presupuesto WHERE presupuesto_id='$id'");
        if ($checkPresupuesto->rowCount() <= 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No hemos encontrado el presupuesto en el sistema",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        $sql = "SELECT f.presupuesto_id, f.cliente_id, f.presupuesto_fecha, f.presupuesto_total,
               d.detalle_presupuesto_id, d.producto_id, d.detalle_presupuesto_cantidad,
               d.detalle_presupuesto_precio_unitario, d.detalle_presupuesto_subtotal,
               p.producto_codigo, p.producto_nombre, p.producto_precio, p.producto_descripcion,
               c.cliente_nombre, c.cliente_apellido, c.cliente_telefono, c.cliente_email
        FROM presupuesto f
        INNER JOIN detalle_presupuesto d ON f.presupuesto_id = d.presupuesto_id
        INNER JOIN producto p ON d.producto_id = p.producto_id
        INNER JOIN cliente c ON f.cliente_id = c.cliente_id
        WHERE f.presupuesto_id = $id;";

        $datosPresupuesto = $this->ejecutarConsulta($sql);
        if ($datosPresupuesto->rowCount() <= 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No hemos encontrado los detalles del presupuesto en el sistema",
                "icono" => "error"
            ];
            return json_encode($alerta);
        } else {
            $datosPresupuesto = $datosPresupuesto->fetchAll();
        }

        $datosPresupuestoGeneral = $datosPresupuesto[0];

        $logoPath = dirname(__DIR__) . '/views/fotos/' . $datosNegocio['configuracion_logo'];

        $pdf = new PresupuestoPDF('P', 'mm', 'A4');
        $pdf->AliasNbPages();
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->configurarEmpresa($datosNegocio, $logoPath);
        $pdf->configurarDocumento($datosPresupuestoGeneral['presupuesto_id'], $datosPresupuestoGeneral['presupuesto_fecha']);
        $pdf->AddPage();

        $pdf->bloqueCliente($datosPresupuestoGeneral);
        $pdf->tablaEncabezado();
        $pdf->tablaFilas($datosPresupuesto);
        $pdf->bloqueTotales($datosPresupuestoGeneral['presupuesto_total']);
        $pdf->seccionesEstaticas();

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="presupuesto_' . $id . '.pdf"');

        $pdf->Output('I'); // 'I' = inline, lo abre en el navegador
        exit;
    }
}
