<?php
$id = $insMain->limpiarCadena($url[1]);
$datos_factura = $insMain->seleccionarDatos("Unico", "factura", "factura_id", $id);
$datos_detalle_factura = $insMain->seleccionarDatos("Todos", "detalle_factura", "factura_id", $id);
?>

<div class="container-fluid mb-4">
    <h1 class="h3">Factura</h1>
    <h2 class="h5 text-muted">Actualizar factura</h2>
</div>

<div class="container py-4">
    <?php include_once "./app/views/inc/btn_back.php"; ?>
    <?php
    if ($datos_factura->rowCount() == 1) {
        $datos_factura = $datos_factura->fetch();
    ?>
        <form action="<?php echo APP_URL; ?>app/ajax/facturaAjax.php" method="POST" class="FormularioAjax" autocomplete="off">

            <input type="hidden" name="modulo_factura" value="actualizar">
            <input type="hidden" name="factura_id" value="<?php echo $datos_factura['factura_id']; ?>">

            <div class="mb-4">
                <label for="cliente_id" class="form-label">Cliente</label>
                <select class="form-select" name="cliente_id" id="cliente_id" required>
                    <option value="" disabled>Seleccione un cliente</option>
                    <?php
                    $clientes = $insMain->seleccionarDatos("Normal", "cliente", "*", "");
                    $clientes = $clientes->fetchAll();

                    foreach ($clientes as $cliente) {
                        $selected = ($cliente['cliente_id'] == $datos_factura['cliente_id']) ? "selected" : "";
                        echo '<option value="' . $cliente['cliente_id'] . '" ' . $selected . '>'
                            . $cliente['cliente_nombre'] . ' ' . $cliente['cliente_apellido']
                            . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="producto_id" class="form-label">Producto</label>
                    <select class="form-select" name="producto_id" id="producto_id">
                        <option value="" selected disabled>Seleccione un producto</option>
                        <?php
                        $productos = $insMain->seleccionarDatos("Normal", "producto", "*", "");
                        foreach ($productos as $producto) {
                            echo '<option value="' . $producto['producto_id'] . '">' . $producto['producto_nombre'] . ' $' . $producto['producto_precio'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="producto_cantidad" class="form-label">Producto Cantidad</label>
                    <input type="number" class="form-control" name="producto_cantidad" id="producto_cantidad">
                </div>
                <div class="col-md-4">
                    <label class="form-label">AGREGAR A LA FACTURA:</label>
                    <button id="btn-agregar" class="btn btn-primary form-control">Agregar</button>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle text-center" id="tabla-productos">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        foreach ($datos_detalle_factura as $ddf) {
                            $producto = $insMain->seleccionarDatos("Unico", "producto", "producto_id", $ddf['producto_id']);
                            $producto = $producto->fetch();

                            $subtotal = $producto['producto_precio'] * $ddf['detalle_factura_cantidad'];
                            $total += $subtotal;

                            echo '<tr>
                                    <td>
                                        <input type="hidden" name="producto_id[]" value="' . $producto['producto_id'] . '">
                                        <input type="hidden" name="producto_codigo[]" value="' . $producto['producto_codigo'] . '">' . $producto['producto_codigo'] . '
                                    </td>
                                    <td><input type="hidden" name="producto_nombre[]" value="' . $producto['producto_nombre'] . '">' . $producto['producto_nombre'] . '</td>
                                    <td><input type="hidden" name="producto_precio[]" value="' . $producto['producto_precio'] . '">$' . number_format($producto['producto_precio'], 2, '.', '') . '</td>
                                    <td><input type="hidden" name="producto_cantidad[]" value="' . $ddf['detalle_factura_cantidad'] . '">' . $ddf['detalle_factura_cantidad'] . '</td>
                                    <td>$' . number_format($subtotal, 2) . '</td>
                                    <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarProducto(this)">Eliminar</button></td>
                                </tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="text-end mb-4">
                <h5>Total: $<span id="total"><?php echo number_format($total, 2); ?></span></h5>
                <input type="hidden" name="factura_total" id="factura_total" value="<?php echo number_format($total, 2); ?>">
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-success rounded-pill">Actualizar factura</button>
            </div>
        </form>
</div>
<script>
    const productos = <?php
                        $productos = $insMain->seleccionarDatos("Normal", "producto", "*", "");
                        $jsArray = [];
                        foreach ($productos as $p) {
                            $jsArray[$p['producto_id']] = [
                                'codigo' => $p['producto_codigo'],
                                'nombre' => $p['producto_nombre'],
                                'precio' => floatval($p['producto_precio'])
                            ];
                        }
                        echo json_encode($jsArray);
                        ?>;

    function recalcularTotal() {
        let nuevoTotal = 0;
        document.querySelectorAll('#tabla-productos tbody tr').forEach(fila => {
            const precio = parseFloat(fila.querySelector('input[name="producto_precio[]"]').value) || 0;
            const cantidad = parseInt(fila.querySelector('input[name="producto_cantidad[]"]').value) || 0;
            nuevoTotal += precio * cantidad;
        });
        document.getElementById('total').textContent = nuevoTotal.toFixed(2);
        document.getElementById('factura_total').value = nuevoTotal.toFixed(2);
    }

    document.getElementById('btn-agregar').addEventListener('click', function(e) {
        e.preventDefault();

        const productoId = document.getElementById('producto_id').value;
        const cantidad = parseInt(document.getElementById('producto_cantidad').value);

        if (!productoId || isNaN(cantidad) || cantidad <= 0) {
            Swal.fire({
                icon: "error",
                title: "Error al seleccionar un producto",
                text: "Seleccioná un producto y una cantidad válida.",
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        const producto = productos[productoId];
        const subtotal = producto.precio * cantidad;

        const tabla = document.querySelector('#tabla-productos tbody');
        const fila = document.createElement('tr');

        fila.innerHTML = `
        <td>
        <input type="hidden" name="producto_id[]" value="${productoId}">
        <input type="hidden" name="producto_codigo[]" value="${producto.codigo}">${producto.codigo}</td>
        <td><input type="hidden" name="producto_nombre[]" value="${producto.nombre}">${producto.nombre}</td>
        <td><input type="hidden" name="producto_precio[]" value="${producto.precio.toFixed(2)}">$${producto.precio.toFixed(2)}</td>
        <td><input type="hidden" name="producto_cantidad[]" value="${cantidad}">${cantidad}</td>
        <td>$${subtotal.toFixed(2)}</td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarProducto(this)">Eliminar</button></td>
    `;

        tabla.appendChild(fila);
        recalcularTotal();
        document.getElementById('producto_id').value = '';
        document.getElementById('producto_cantidad').value = '';
    });

    function eliminarProducto(btn) {
        const fila = btn.closest('tr');
        const subtotal = parseFloat(
            fila.querySelector('td:nth-child(5)').textContent.replace('$', '')
        ) || 0;
        fila.remove();
        recalcularTotal();
    }
</script>
<?php
    } else {
        include "./app/views/inc/error_alert.php";
    }
?>