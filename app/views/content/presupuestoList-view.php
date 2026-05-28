<div class="container-fluid mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h1 class="h3 mb-0">Presupuestos</h1>
      <small class="text-muted">Lista de presupuestos</small>
    </div>
    <a href="<?php echo APP_URL; ?>presupuestoNew/" class="btn btn-primary">
      <i class="bi bi-person-plus"></i> Nuevo presupuesto
    </a>
  </div>
</div>

<div class="container py-4">
  <?php

  use app\controllers\presupuestoController;

  $insPresupuesto = new presupuestoController();
  echo $insPresupuesto->listarPresupuestoControlador($url[1], 10, $url[0], "");
  ?>
</div>