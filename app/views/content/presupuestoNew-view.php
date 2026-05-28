<div class="container-fluid mb-4">
    <h1 class="h3">Facturación</h1>
    <h2 class="h5 text-muted">Crear nueva factura</h2>
</div>

<div class="container py-4">
    <form action="<?php echo APP_URL; ?>app/ajax/facturaAjax.php" method="POST" class="FormularioAjax" autocomplete="off">

        <input type="hidden" name="modulo_factura" value="crear">

        <!-- Cliente -->
        <div class="mb-4">
            <label for="cliente_id" class="form-label">Cliente</label>
            <select class="form-select" name="cliente_id" id="cliente_id" required>
                <option value="" selected disabled>Seleccione un cliente</option>
                <?php

                $clientes = $insMain->seleccionarDatos("Normal", "cliente", "*", "");
                foreach ($clientes as $cliente) {
                    echo '<option value="' . $cliente['cliente_id'] . '">' . $cliente['cliente_nombre'] . ' ' . $cliente['cliente_apellido'] . '</option>';
                }
                ?>
            </select>
        </div>

        <!-- Buscador de productos -->
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
                <button class="btn btn-primary form-control">Agregar</button>
            </div>
        </div>

        <!-- Tabla de productos -->
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
                <tbody></tbody>
            </table>
        </div>
        <!-- Total -->
        <div class="text-end mb-4">
            <h5>Total: $<span id="total">0.00</span></h5>
            <input type="hidden" name="factura_total" id="factura_total">
        </div>

        <!-- Botón -->
        <div class="text-center">
            <button type="submit" class="btn btn-success rounded-pill">Guardar factura</button>
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

    let total = 0;

    document.querySelector('.btn-primary').addEventListener('click', function(e) {
        e.preventDefault();

        const productoId = document.getElementById('producto_id').value;
        const cantidad = parseInt(document.getElementById('producto_cantidad').value);

        if (!productoId || isNaN(cantidad) || cantidad <= 0) {
            alert('Seleccioná un producto y una cantidad válida.');
            return;
        }

        const producto = productos[productoId];
        const subtotal = producto.precio * cantidad;
        total += subtotal;

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
            <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarProducto(this, ${subtotal})">Eliminar</button></td>
        `;

        tabla.appendChild(fila);

        document.getElementById('total').textContent = total.toFixed(2);
        document.getElementById('factura_total').value = total.toFixed(2);

        document.getElementById('producto_id').value = '';
        document.getElementById('producto_cantidad').value = '';
    });

    function eliminarProducto(btn, subtotal) {
        btn.closest('tr').remove();
        total -= subtotal;

        document.getElementById('total').textContent = total.toFixed(2);
        document.getElementById('factura_total').value = total.toFixed(2);
    }
</script>