<div class="container-fluid mb-4">
    <h1 class="h3">Factura</h1>
    <h2 class="h5 text-muted">Buscar factura</h2>
</div>

<div class="container py-4">

    <?php

    use app\controllers\invoiceController;

    $insFactura = new invoiceController();
    if (!isset($_SESSION[$url[0]]) && empty($_SESSION[$url[0]])) {
    ?>

        <div class="row">
            <div class="col">
                <form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off">
                    <input type="hidden" name="modulo_buscador" value="buscar">
                    <input type="hidden" name="modulo_url" value="<?php echo $url[0]; ?>">

                    <div class="input-group">
                        <input
                            type="text"
                            class="form-control"
                            name="txt_buscador"
                            placeholder="¿Qué estás buscando?"
                            pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,30}"
                            maxlength="30"
                            required>
                        <button class="btn btn-info text-white" type="submit">Buscar</button>
                    </div>
                </form>
            </div>
        </div>

    <?php } else { ?>

        <div class="row">
            <div class="col">
                <form class="text-center my-5 FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off">
                    <input type="hidden" name="modulo_buscador" value="eliminar">
                    <input type="hidden" name="modulo_url" value="<?php echo $url[0]; ?>">

                    <p>Estás buscando <strong>“<?php echo $_SESSION[$url[0]]; ?>”</strong></p>
                    <button type="submit" class="btn btn-danger rounded-pill mt-3">Eliminar búsqueda</button>
                </form>
            </div>
        </div>

    <?php
        echo $insFactura->listarFacturaControlador($url[1], 10, $url[0], $_SESSION[$url[0]]);
    }
    ?>

</div>