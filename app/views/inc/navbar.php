<div class="d-flex">
    <!-- Sidebar -->
    <nav class="d-flex flex-column flex-shrink-0 p-3 text-bg-dark" style="width: 250px; min-height: 100vh;">
        <a href="<?php echo APP_URL; ?>dashboard/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <i class="bi bi-speedometer2 fs-4 me-2"></i>
            <span class="fs-5 fw-bold">PRESUPUESTADOR</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">

            <!-- Presupuestos -->
            <li class="nav-item">
                <a class="nav-link text-white dropdown-toggle" data-bs-toggle="collapse" href="#submenuPresupuestos" role="button" aria-expanded="false" aria-controls="submenuPresupuestos">
                    <i class="bi bi-file-earmark-text fs-5 me-2"></i>
                    Presupuestos
                </a>
                <div class="collapse ps-3" id="submenuPresupuestos">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                        <li><a href="<?php echo APP_URL; ?>presupuestoNew/" class="nav-link text-white"><i class="bi bi-file-earmark-plus me-2"></i>Crear Presupuesto</a></li>
                        <li><a href="<?php echo APP_URL; ?>presupuestoList/" class="nav-link text-white"><i class="bi bi-journal-text me-2"></i>Listar Presupuestos</a></li>
                        <li><a href="<?php echo APP_URL; ?>presupuestoSearch/" class="nav-link text-white"><i class="bi bi-search me-2"></i>Buscar Presupuestos</a></li>
                    </ul>
                </div>
            </li>

            <!-- Clientes -->
            <li class="nav-item mt-3">
                <a class="nav-link text-white dropdown-toggle" data-bs-toggle="collapse" href="#submenuClientes" role="button" aria-expanded="false" aria-controls="submenuClientes">
                    <i class="bi bi-person-lines-fill fs-5 me-2"></i>
                    Clientes
                </a>
                <div class="collapse ps-3" id="submenuClientes">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                        <li><a href="<?php echo APP_URL; ?>clientNew/" class="nav-link text-white"><i class="bi bi-person-plus me-2"></i>Guardar Cliente</a></li>
                        <li><a href="<?php echo APP_URL; ?>clientList/" class="nav-link text-white"><i class="bi bi-people-fill me-2"></i>Listar Clientes</a></li>
                        <li><a href="<?php echo APP_URL; ?>clientSearch/" class="nav-link text-white"><i class="bi bi-search me-2"></i>Buscar Cliente</a></li>
                    </ul>
                </div>
            </li>

            <!-- Productos -->
            <li class="nav-item mt-3">
                <a class="nav-link text-white dropdown-toggle" data-bs-toggle="collapse" href="#submenuProductos" role="button" aria-expanded="false" aria-controls="submenuProductos">
                    <i class="bi bi-box2-heart fs-5 me-2"></i>
                    Productos
                </a>
                <div class="collapse ps-3" id="submenuProductos">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                        <li><a href="<?php echo APP_URL; ?>productNew/" class="nav-link text-white"><i class="bi bi-box2 me-2"></i>Guardar Producto</a></li>
                        <li><a href="<?php echo APP_URL; ?>productList/" class="nav-link text-white"><i class="bi bi-boxes me-2"></i>Listar Productos</a></li>
                        <li><a href="<?php echo APP_URL; ?>productSearch/" class="nav-link text-white"><i class="bi bi-search me-2"></i>Buscar Productos</a></li>
                    </ul>
                </div>
            </li>

            <!-- Configuración -->
            <li class="nav-item mt-4">
                <a href="<?php echo APP_URL; ?>configBoard/1" class="nav-link text-white">
                    <i class="bi bi-gear-fill fs-5 me-2"></i>
                    Configurar Usuario
                </a>
            </li>

        </ul>
        <hr>
    </nav>

    <!-- Contenido principal -->
    <div class="flex-grow-1 p-4">
        <!-- Aquí va el contenido de cada vista -->