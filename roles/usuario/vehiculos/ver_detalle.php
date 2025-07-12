<?php
session_start();
require_once('../../../conecct/conex.php');
include '../../../includes/validarsession.php';

$id = $_GET['id'] ?? null;
$data = json_decode(file_get_contents("respuesta.json"), true);
$multas = $data['data']['multas'] ?? [];

$multa = null;
foreach ($multas as $item) {
    if ($item['numeroComparendo'] == $id || $item['numeroResolucion'] == $id) {
        $multa = $item;
        break;
    }
}

// Función para determinar la clase del estado
function getEstadoClass($estado) {
    $estado = strtolower($estado);
    if (strpos($estado, 'pagado') !== false) return 'pagado';
    if (strpos($estado, 'vencido') !== false) return 'vencido';
    return 'pendiente';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Multa - <?= $id ?></title>
    <link rel="shortcut icon" href="../../css/img/logo_sinfondo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos para la página de detalles de multa */
.contenedor {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
  font-family: "Poppins", sans-serif;
  background-color: #f8f9fa;
  min-height: 100vh;
}

.header-detalle {
  text-align: center;
  margin-bottom: 30px;
  padding: 20px;
  background: #d32f2f;
  color: white;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.header-detalle h1 {
  margin: 0;
  font-size: 28px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
}

.header-detalle .multa-id {
  font-size: 16px;
  opacity: 0.9;
  margin-top: 8px;
  font-weight: 400;
}

/* Contenedor principal de información */
.detalle-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 30px;
}

/* Cards de información */
.info-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  border-left: 4px solid #667eea;
  transition: all 0.3s ease;
}

.info-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
}

.info-card.vehiculo {
  border-left-color: #3498db;
}

.info-card.infractor {
  border-left-color: #e74c3c;
}

.info-card.infraccion {
  border-left-color: #f39c12;
  grid-column: 1 / -1;
}

.info-card.financiero {
  border-left-color: #27ae60;
  grid-column: 1 / -1;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 2px solid #f1f3f4;
}

.card-header h3 {
  margin: 0;
  color: #2c3e50;
  font-size: 18px;
  font-weight: 600;
}

.card-header i {
  font-size: 20px;
  color: #667eea;
}

/* Items de información */
.info-item {
  display: flex;
  align-items: flex-start;
  margin-bottom: 16px;
  padding: 12px;
  background-color: #f8f9fa;
  border-radius: 8px;
  transition: background-color 0.3s ease;
}

.info-item:hover {
  background-color: #e9ecef;
}

.info-item:last-child {
  margin-bottom: 0;
}

.info-label {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 140px;
  font-weight: 600;
  color: #495057;
  font-size: 14px;
}

.info-label i {
  font-size: 14px;
  color: #6c757d;
  width: 16px;
}

.info-value {
  flex: 1;
  color: #2c3e50;
  font-weight: 500;
  word-break: break-word;
}

/* Valores especiales */
.valor-monetario {
  color: #e74c3c;
  font-weight: 700;
  font-size: 16px;
}

.estado-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.estado-badge.pendiente {
  background-color: #fff3cd;
  color: #856404;
  border: 1px solid #ffeaa7;
}

.estado-badge.pagado {
  background-color: #d1ecf1;
  color: #0c5460;
  border: 1px solid #bee5eb;
}

.estado-badge.vencido {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.placa-destacada {
  background: linear-gradient(135deg, #2c3e50, #34495e);
  color: white;
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 700;
  letter-spacing: 1px;
  display: inline-block;
}

/* Sección de infracción destacada */
.infraccion-detalle {
  background: linear-gradient(135deg, #fff7e6, #ffeaa7);
  border: 1px solid #f39c12;
  border-radius: 8px;
  padding: 16px;
  margin-top: 12px;
}

.infraccion-codigo {
  font-weight: 700;
  color: #d68910;
  font-size: 16px;
  margin-bottom: 8px;
}

.infraccion-descripcion {
  color: #7d6608;
  line-height: 1.5;
  font-style: italic;
}

/* Botones de acción */
.acciones {
  display: flex;
  gap: 15px;
  justify-content: center;
  margin-top: 30px;
  flex-wrap: wrap;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.btn-volver {
  background: linear-gradient(135deg, #6c757d, #495057);
  color: white;
}

.btn-volver:hover {
  background: linear-gradient(135deg, #495057, #343a40);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
  color: white;
  text-decoration: none;
}

.btn-imprimir {
  background: linear-gradient(135deg, #17a2b8, #138496);
  color: white;
}

.btn-imprimir:hover {
  background: linear-gradient(135deg, #138496, #0f6674);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
  color: white;
  text-decoration: none;
}

/* Mensaje de error */
.error-message {
  text-align: center;
  padding: 40px 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  border-left: 4px solid #e74c3c;
}

.error-message i {
  font-size: 48px;
  color: #e74c3c;
  margin-bottom: 16px;
  display: block;
}

.error-message h3 {
  color: #e74c3c;
  margin-bottom: 8px;
}

.error-message p {
  color: #6c757d;
  margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .contenedor {
    padding: 15px;
  }

  .detalle-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .info-card.infraccion,
  .info-card.financiero {
    grid-column: 1;
  }

  .header-detalle h1 {
    font-size: 24px;
    flex-direction: column;
    gap: 8px;
  }

  .info-item {
    flex-direction: column;
    gap: 8px;
  }

  .info-label {
    min-width: auto;
  }

  .acciones {
    flex-direction: column;
    align-items: center;
  }

  .btn {
    width: 100%;
    max-width: 250px;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .info-card {
    padding: 16px;
  }

  .header-detalle {
    padding: 16px;
  }

  .header-detalle h1 {
    font-size: 20px;
  }
}

/* Animaciones */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.info-card {
  animation: fadeInUp 0.6s ease-out;
}

.info-card:nth-child(1) {
  animation-delay: 0.1s;
}
.info-card:nth-child(2) {
  animation-delay: 0.2s;
}
.info-card:nth-child(3) {
  animation-delay: 0.3s;
}
.info-card:nth-child(4) {
  animation-delay: 0.4s;
}

/* Estilos para impresión */
@media print {
  .acciones {
    display: none;
  }

  .info-card {
    box-shadow: none;
    border: 1px solid #ddd;
    break-inside: avoid;
  }

  .header-detalle {
    background: #f8f9fa !important;
    color: #000 !important;
  }
}

    </style>

</head>
<body>
    <div class="contenedor">
        <?php if ($multa): ?>
            <!-- Header con información principal -->
            <div class="header-detalle">
                <h1>
                    <i class="bi bi-file-earmark-text"></i>
                    Detalles de la Multa
                </h1>
                <div class="multa-id">
                    ID: <?= htmlspecialchars($multa['numeroComparendo'] ?? $multa['numeroResolucion']) ?>
                </div>
            </div>

            <!-- Grid de información -->
            <div class="detalle-grid">
                <!-- Información del Vehículo -->
                <div class="info-card vehiculo">
                    <div class="card-header">
                        <i class="bi bi-car-front"></i>
                        <h3>Información del Vehículo</h3>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-credit-card-2-front"></i>
                            Placa:
                        </div>
                        <div class="info-value">
                            <span class="placa-destacada"><?= htmlspecialchars($multa['placa']) ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-geo-alt"></i>
                            Departamento:
                        </div>
                        <div class="info-value"><?= htmlspecialchars($multa['departamento']) ?></div>
                    </div>
                </div>

                <!-- Información del Infractor -->
                <div class="info-card infractor">
                    <div class="card-header">
                        <i class="bi bi-person"></i>
                        <h3>Información del Infractor</h3>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-person-badge"></i>
                            Nombre:
                        </div>
                        <div class="info-value">
                            <?= htmlspecialchars($multa['infractor']['nombre'] . " " . $multa['infractor']['apellido']) ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-calendar-date"></i>
                            Fecha Comparendo:
                        </div>
                        <div class="info-value"><?= htmlspecialchars($multa['fechaComparendo']) ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-info-circle"></i>
                            Estado:
                        </div>
                        <div class="info-value">
                            <span class="estado-badge <?= getEstadoClass($multa['estadoCartera']) ?>">
                                <?= htmlspecialchars($multa['estadoCartera']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Información de la Infracción -->
                <div class="info-card infraccion">
                    <div class="card-header">
                        <i class="bi bi-exclamation-triangle"></i>
                        <h3>Detalles de la Infracción</h3>
                    </div>
                    
                    <div class="infraccion-detalle">
                        <div class="infraccion-codigo">
                            Código: <?= htmlspecialchars($multa['infracciones'][0]['codigoInfraccion']) ?>
                        </div>
                        <div class="infraccion-descripcion">
                            <?= htmlspecialchars($multa['infracciones'][0]['descripcionInfraccion']) ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-file-earmark"></i>
                            Resolución:
                        </div>
                        <div class="info-value">
                            <?= htmlspecialchars($multa['numeroResolucion']) ?> del <?= htmlspecialchars($multa['fechaResolucion']) ?>
                        </div>
                    </div>
                </div>

                <!-- Información Financiera -->
                <div class="info-card financiero">
                    <div class="card-header">
                        <i class="bi bi-currency-dollar"></i>
                        <h3>Información Financiera</h3>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-cash"></i>
                            Valor Infracción:
                        </div>
                        <div class="info-value valor-monetario">
                            $<?= number_format($multa['infracciones'][0]['valorInfraccion'], 0, ',', '.') ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-credit-card"></i>
                            Total a Pagar:
                        </div>
                        <div class="info-value valor-monetario">
                            $<?= number_format($multa['valorPagar'], 0, ',', '.') ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="acciones">
                <a href="javascript:history.back()" class="btn btn-volver">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
                <button onclick="window.print()" class="btn btn-imprimir">
                    <i class="bi bi-printer"></i>
                    Imprimir
                </button>
            </div>

        <?php else: ?>
            <!-- Mensaje de error -->
            <div class="error-message">
                <i class="bi bi-exclamation-triangle"></i>
                <h3>Multa no encontrada</h3>
                <p>No se encontró la multa solicitada con el ID: <strong><?= htmlspecialchars($id) ?></strong></p>
                <div class="acciones" style="margin-top: 20px;">
                    <a href="javascript:history.back()" class="btn btn-volver">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php
      include('../../../includes/auto_logout_modal.php');
    ?>
</body>

</html>
