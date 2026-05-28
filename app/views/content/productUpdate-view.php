<?php
$id = $insMain->limpiarCadena($url[1]);
$datos = $insMain->seleccionarDatos("Unico", "producto", "producto_id", $id);
?>

<div class="container-fluid mb-4">
    <h1 class="h3">Producto</h1>
    <h2 class="h5 text-muted">Actualizar producto</h2>
</div>

<div class="container py-4">
    <?php include_once "./app/views/inc/btn_back.php"; ?>

    <?php
    if ($datos->rowCount() == 1) {
        $datos = $datos->fetch();
    ?>
        <form action="<?php echo APP_URL; ?>app/ajax/productoAjax.php" method="POST" autocomplete="off" class="FormularioAjax">

            <input type="hidden" name="modulo_producto" value="actualizar">
            <input type="hidden" name="producto_id" value="<?php echo $datos['producto_id']; ?>">

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="producto_codigo" class="form-label">Código</label>
                    <input type="text" class="form-control" id="producto_codigo" name="producto_codigo"
                        value="<?php echo $datos['producto_codigo']; ?>" placeholder="Ej: PRD-001" pattern="[A-Za-z0-9-]{1,20}" maxlength="20" required>
                </div>
                <div class="col-md-6">
                    <label for="producto_nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="producto_nombre" name="producto_nombre"
                        value="<?php echo $datos['producto_nombre']; ?>" placeholder="Nombre del producto" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ .-]{3,40}" maxlength="40" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="producto_precio" class="form-label">Precio</label>
                    <input type="text" class="form-control" id="producto_precio" name="producto_precio"
                        value="<?php echo $datos['producto_precio']; ?>" placeholder="Ej: 199.99" pattern="^\d+(\.\d{1,2})?$" maxlength="10" required>
                </div>
                <div class="col-md-6">
                    <label for="producto_descripcion" class="form-label">Descripción</label>
                    <input type="text" class="form-control" id="producto_descripcion" name="producto_descripcion"
                        value="<?php echo $datos['producto_descripcion']; ?>" placeholder="Breve descripción" maxlength="255">
                </div>
            </div>

            <div class="text-center">
                <button type="reset" class="btn btn-outline-primary rounded-pill me-2">Limpiar</button>
                <button type="submit" class="btn btn-success rounded-pill">Guardar producto</button>
            </div>
        </form>
    <?php
    } else {
        include "./app/views/inc/error_alert.php";
    }
    ?>
</div>