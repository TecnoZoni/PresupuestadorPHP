<div class="container-fluid mb-4">
    <h1 class="h3">Panel principal</h1>
    <h2 class="h5 text-muted">Bienvenido al sistema de presupuestos</h2>
</div>

<div class="container py-4">
    <div class="row g-4 justify-content-center">

        <!-- Gestión de Presupuestos -->
        <div class="col-md-4">
            <a href="<?php echo APP_URL; ?>presupuestoList/" class="btn btn-outline-primary btn-lg w-100 py-4 d-flex flex-column align-items-center">
                <i class="bi bi-receipt-cutoff fs-1 mb-2"></i>
                <span>Gestión de Presupuestos</span>
            </a>
        </div>

        <!-- Gestión de Clientes -->
        <div class="col-md-4">
            <a href="<?php echo APP_URL; ?>clientList/" class="btn btn-outline-success btn-lg w-100 py-4 d-flex flex-column align-items-center">
                <i class="bi bi-people fs-1 mb-2"></i>
                <span>Gestión de Clientes</span>
            </a>
        </div>

        <!-- Gestión de Productos -->
        <div class="col-md-4">
            <a href="<?php echo APP_URL; ?>productList/" class="btn btn-outline-warning btn-lg w-100 py-4 d-flex flex-column align-items-center">
                <i class="bi bi-box-seam fs-1 mb-2"></i>
                <span>Gestión de Productos</span>
            </a>
        </div>

    </div>
</div>