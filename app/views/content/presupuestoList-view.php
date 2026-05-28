<div class="container-fluid mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h1 class="h3 mb-0">Facturas</h1>
      <small class="text-muted">Lista de facturas</small>
    </div>
    <a href="<?php echo APP_URL; ?>invoiceNew/" class="btn btn-primary">
      <i class="bi bi-person-plus"></i> Nueva factura
    </a>
  </div>
</div>

<div class="container py-4">
  <?php

  use app\controllers\invoiceController;

  $insFactura = new invoiceController();
  echo $insFactura->listarFacturaControlador($url[1], 10, $url[0], "");
  ?>
</div>