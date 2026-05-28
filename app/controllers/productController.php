<?php

namespace app\controllers;

use app\models\mainModel;


class productController extends mainModel
{
    public function registrarProductoControlador()
    {

        $codigo = $this->limpiarCadena($_POST['producto_codigo']);
        $nombre = $this->limpiarCadena($_POST['producto_nombre']);
        $precio = $this->limpiarCadena($_POST['producto_precio']);
        $descripcion = $this->limpiarCadena($_POST['producto_descripcion']);

        if ($codigo == "" || $nombre == "" || $precio == "") {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No has llenado todos los campos que son obligatorios",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }


        if ($this->verificarDatos("[A-Za-z0-9-]{1,20}", $codigo)) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "El CODIGO no coincide con el formato solicitado",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        if ($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ .-]{3,40}", $nombre)) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "El NOMBRE no coincide con el formato solicitado",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        if ($this->verificarDatos("\d+(\.\d{1,2})?", $precio)) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "El PRECIO no coincide con el formato solicitado",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        $check_codigo = $this->ejecutarConsulta("SELECT producto_codigo FROM producto WHERE producto_codigo='$codigo'");
        if ($check_codigo->rowCount() > 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "El CODIGO que acaba de ingresar ya se encuentra registrado en el sistema, por favor verifique e intente nuevamente",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        $producto_datos_reg = [
            [
                "campo_nombre" => "producto_codigo",
                "campo_marcador" => ":Codigo",
                "campo_valor" => $codigo
            ],
            [
                "campo_nombre" => "producto_nombre",
                "campo_marcador" => ":Nombre",
                "campo_valor" => $nombre
            ],
            [
                "campo_nombre" => "producto_precio",
                "campo_marcador" => ":Precio",
                "campo_valor" => $precio
            ],
            [
                "campo_nombre" => "producto_descripcion",
                "campo_marcador" => ":Descripcion",
                "campo_valor" => $descripcion
            ]
        ];

        $registrar_producto = $this->guardarDatos("producto", $producto_datos_reg);

        if ($registrar_producto['stmt']->rowCount() == 1) {
            $alerta = [
                "tipo" => "limpiar",
                "titulo" => "Producto registrado",
                "texto" => "El Producto " . $nombre . " se registro con exito",
                "icono" => "success"
            ];
        } else {

            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No se pudo registrar el Producto, por favor intente nuevamente",
                "icono" => "error"
            ];
        }

        return json_encode($alerta);
    }

    public function listarProductoControlador($pagina, $registros, $url, $busqueda)
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

            $consulta_datos = "SELECT * FROM producto WHERE (producto_codigo LIKE '%$busqueda%' OR producto_nombre LIKE '%$busqueda%' OR producto_descripcion LIKE '%$busqueda%' OR producto_precio LIKE '%$busqueda%') ORDER BY producto_nombre ASC LIMIT $inicio,$registros";

            $consulta_total = "SELECT COUNT(producto_id) FROM producto WHERE (producto_codigo LIKE '%$busqueda%' OR producto_nombre LIKE '%$busqueda%' OR producto_descripcion LIKE '%$busqueda%' OR producto_precio LIKE '%$busqueda%')";
        } else {

            $consulta_datos = "SELECT * FROM producto ORDER BY producto_nombre ASC LIMIT $inicio,$registros";

            $consulta_total = "SELECT COUNT(producto_id) FROM producto";
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
                <th>#</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Descripción</th>
                <th colspan="2">Opciones</th>
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
                <td>' . $contador . '</td>
                <td>' . $rows['producto_codigo'] . '</td>
                <td>' . $rows['producto_nombre'] . '</td>
                <td>$' . number_format($rows['producto_precio'], 2, ',', '.') . '</td>
                <td>' . $rows['producto_descripcion'] . '</td>
                <td>
                    <a href="' . APP_URL . 'productUpdate/' . $rows['producto_id'] . '/" class="btn btn-success btn-sm">Actualizar</a>
                </td>
                <td>
                    <form class="FormularioAjax" action="' . APP_URL . 'app/ajax/productoAjax.php" method="POST" autocomplete="off">
                        <input type="hidden" name="modulo_producto" value="eliminar">
                        <input type="hidden" name="producto_id" value="' . $rows['producto_id'] . '">
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
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
                <td colspan="7">
                    <a href="' . $url . '1/" class="btn btn-primary mt-4 mb-4">
                        Haga clic acá para recargar el listado
                    </a>
                </td>
            </tr>
        ';
            } else {
                $tabla .= '
            <tr>
                <td colspan="7">No hay registros en el sistema</td>
            </tr>
        ';
            }
        }

        $tabla .= '</tbody></table></div>';

        if ($total > 0 && $pagina <= $numeroPaginas) {
            $tabla .= '<p class="text-end">Mostrando productos <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p>';
            $tabla .= $this->paginadorTablas($pagina, $numeroPaginas, $url, 7);
        }

        return $tabla;
    }

    public function eliminarProductoControlador()
    {

        $id = $this->limpiarCadena($_POST['producto_id']);

        $datos = $this->ejecutarConsulta("SELECT * FROM producto WHERE producto_id='$id'");
        if ($datos->rowCount() <= 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No hemos encontrado el producto en el sistema",
                "icono" => "error"
            ];
            return json_encode($alerta);
        } else {
            $datos = $datos->fetch();
        }

        $eliminarProducto = $this->eliminarRegistro("producto", "producto_id", $id);

        if ($eliminarProducto->rowCount() == 1) {

            $alerta = [
                "tipo" => "recargar",
                "titulo" => "Producto eliminado",
                "texto" => "El producto " . $datos['producto_nombre'] . " ha sido eliminado del sistema correctamente",
                "icono" => "success"
            ];
        } else {

            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No hemos podido eliminar el producto " . $datos['producto_nombre'] . " del sistema, por favor intente nuevamente",
                "icono" => "error"
            ];
        }

        return json_encode($alerta);
    }

    public function actualizarProductoControlador()
    {

        $id = $this->limpiarCadena($_POST['producto_id']);


        $datos = $this->ejecutarConsulta("SELECT * FROM producto WHERE producto_id='$id'");
        if ($datos->rowCount() <= 0) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No hemos encontrado el producto en el sistema",
                "icono" => "error"
            ];
            return json_encode($alerta);
        } else {
            $datos = $datos->fetch();
        }

        $codigo = $this->limpiarCadena($_POST['producto_codigo']);
        $nombre = $this->limpiarCadena($_POST['producto_nombre']);
        $precio = $this->limpiarCadena($_POST['producto_precio']);
        $descripcion = $this->limpiarCadena($_POST['producto_descripcion']);

        if ($codigo == "" || $nombre == "" || $precio == "") {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No has llenado todos los campos que son obligatorios",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }


        if ($this->verificarDatos("[A-Za-z0-9-]{1,20}", $codigo)) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "El CODIGO no coincide con el formato solicitado",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        if ($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ .-]{3,40}", $nombre)) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "El NOMBRE no coincide con el formato solicitado",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        if ($this->verificarDatos("\d+(\.\d{1,2})?", $precio)) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "El PRECIO no coincide con el formato solicitado",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        if ($datos['producto_codigo'] != $codigo) {
            $check_codigo = $this->ejecutarConsulta("SELECT producto_codigo FROM producto WHERE producto_codigo='$codigo'");
            if ($check_codigo->rowCount() > 0) {
                $alerta = [
                    "tipo" => "simple",
                    "titulo" => "Ocurrió un error inesperado",
                    "texto" => "El codigo que acaba de ingresar ya se encuentra registrado en el sistema, por favor verifique e intente nuevamente",
                    "icono" => "error"
                ];
                return json_encode($alerta);
            }
        }


        $producto_datos_up = [
            [
                "campo_nombre" => "producto_codigo",
                "campo_marcador" => ":Codigo",
                "campo_valor" => $codigo
            ],
            [
                "campo_nombre" => "producto_nombre",
                "campo_marcador" => ":Nombre",
                "campo_valor" => $nombre
            ],
            [
                "campo_nombre" => "producto_precio",
                "campo_marcador" => ":Precio",
                "campo_valor" => $precio
            ],
            [
                "campo_nombre" => "producto_descripcion",
                "campo_marcador" => ":Descripcion",
                "campo_valor" => $descripcion
            ]
        ];

        $condicion = [
            "condicion_campo" => "producto_id",
            "condicion_marcador" => ":ID",
            "condicion_valor" => $id
        ];

        if ($this->actualizarDatos("producto", $producto_datos_up, $condicion)) {

            $alerta = [
                "tipo" => "recargar",
                "titulo" => "Producto actualizado",
                "texto" => "Los datos del producto " . $datos['producto_nombre'] . " se actualizaron correctamente",
                "icono" => "success"
            ];
        } else {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "No hemos podido actualizar los datos del producto " . $datos['producto_nombre'] . ", por favor intente nuevamente",
                "icono" => "error"
            ];
        }

        return json_encode($alerta);
    }
}
