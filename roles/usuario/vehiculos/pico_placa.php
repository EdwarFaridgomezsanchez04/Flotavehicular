<?php
session_start();
require_once('../../../conecct/conex.php');
require_once('../../../includes/validarsession.php');

$db = new Database();
$con = $db->conectar();

$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    header('Location: ../../login/login.php');
    exit;
}

// Fetch nombre_completo and foto_perfil if not in session
$nombre_completo = $_SESSION['nombre_completo'] ?? null;
$foto_perfil = $_SESSION['foto_perfil'] ?? null;
if (!$nombre_completo || !$foto_perfil) {
    $user_query = $con->prepare("SELECT nombre_completo, foto_perfil FROM usuarios WHERE documento = :documento");
    $user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
    $user_query->execute();
    $user = $user_query->fetch(PDO::FETCH_ASSOC);
    $nombre_completo = $user['nombre_completo'] ?? 'Usuario';
    $foto_perfil = $user['foto_perfil'] ?: '/Flotavehicular/roles/usuario/css/img/perfil.jpg';
    $_SESSION['nombre_completo'] = $nombre_completo;
    $_SESSION['foto_perfil'] = $foto_perfil;
}

// Consultar las placas distintas
$sql = "SELECT DISTINCT v.placa
        FROM vehiculos v
        WHERE v.Documento = :documento";

$stmt = $con->prepare($sql);
$stmt->bindParam(':documento', $documento, PDO::PARAM_STR);
$stmt->execute();
$placas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flotax AGC - Pico y Placa</title>
    <link rel="stylesheet" href="../css/stylos_pico_placa.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Incluir el header -->
    <?php include('../header.php'); ?>

    <div class="container">
        <h1>Pico y Placa</h1>
        <p class="instructions">Selecciona una placa para consultar los días restringidos.</p>

        <div class="input-group">
            <div class="input-box">
                <label for="placa">Seleccione una placa:</label>
                <div class="input_field_placa" id="grupo_placa">
                    <select id="placa" name="placa">
                        <option value="">-- Seleccione --</option>
                        <?php foreach ($placas as $fila): ?>
                            <option value="<?= htmlspecialchars($fila['placa']) ?>"><?= htmlspecialchars($fila['placa']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="bi bi-car-front-fill"></i>
                </div>
            </div>
        </div>

        <div id="resultado" class="resultado"></div>
        <button id="btnInfoPicoPlaca" style="margin-top:15px; background:#d32f2f; color:#fff; padding:10px 20px; border:none; border-radius:8px; font-weight:bold; font-family:'Poppins', sans-serif; cursor:pointer; box-shadow:0 4px 10px rgba(0,0,0,0.2); transition:all 0.3s ease;">
            ¿Cómo funciona el Pico y Placa?
        </button>
    </div>

    <!-- Modal informativo sobre Pico y Placa -->
    <div id="modalInfoPicoPlaca" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); backdrop-filter:blur(4px); z-index:9999; display:flex; align-items:center; justify-content:center;">
        <div style="background:#fff; padding:25px; border-radius:12px; max-width:400px; text-align:left; font-family:'Poppins', sans-serif; position:relative; box-shadow: 0 0 15px rgba(0,0,0,0.3); animation:fadeIn 0.3s ease;">
            <button onclick="cerrarModalInfo()" style="position:absolute; top:10px; right:10px; border:none; background:none; font-size:22px; cursor:pointer; color:#555;">×</button>
            <h3 style="margin-top:0; color:#007bff;">¿Cómo funciona el Pico y Placa?</h3>
            <p>En Ibagué, el <strong>Pico y Placa</strong> es una medida para mejorar la movilidad y reducir el tráfico. Se basa en el <strong>último dígito de la placa</strong> del vehículo y el día de la semana.</p>
            <ul style="padding-left:20px;">
                <li><strong>Lunes:</strong> 1 y 2</li>
                <li><strong>Martes:</strong> 3 y 4</li>
                <li><strong>Miércoles:</strong> 5 y 6</li>
                <li><strong>Jueves:</strong> 7 y 8</li>
                <li><strong>Viernes:</strong> 9 y 0</li>
            </ul>
            <p><strong>Horarios de restricción:</strong><br>
            Mañana: <strong>6:00 a.m. - 9:00 a.m.</strong><br>
            Tarde: <strong>5:00 p.m. - 8:00 p.m.</strong></p>
            <p style="margin-top:10px;">Consulta más detalles en el sitio oficial:</p>
            <a href="https://www.ibague.gov.co/pico-y-placa" target="_blank" style="color:#007bff; font-weight:bold;">Más información aquí</a>
        </div>
    </div>

    <style>
        

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .resultado {
            margin-top: 20px;
            padding: 15px;
            width: fit-content;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: none;
        }
        

        #btnInfoPicoPlaca:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0,0,0,0.25);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>

    <script>
        // Script para consultar días restringidos
        document.getElementById('placa').addEventListener('change', function () {
            const placa = this.value;
            const resultado = document.getElementById('resultado');

            if (!placa) {
                resultado.style.display = 'none'
                resultado.innerHTML = '';
                return;
            }

            fetch('../AJAX/obtener_dias.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'placa=' + encodeURIComponent(placa)
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    resultado.style.display = 'none'
                    resultado.innerHTML = '<p style="color:red;">' + data.error + '</p>';
                } else if (data.length === 0) {
                    resultado.style.display = 'block'
                    resultado.innerHTML = '<p>No hay días restringidos para esta placa.</p>';
                } else {
                    let html = '<h3>Días restringidos:</h3>';
                    data.forEach(item => {
                        html += `<p><strong>${item.dia}</strong> - Dígitos: ${item.digitos_restringidos}</p>`;
                    });
                    resultado.style.display = 'block'
                    resultado.innerHTML = html;
                    
                }
            })
            .catch(err => {
                resultado.innerHTML = '<p style="color:red;">Error: ' + err.message + '</p>';
            });
        });

        // Script para el modal informativo
        const btnInfo = document.getElementById("btnInfoPicoPlaca");
        const modalInfo = document.getElementById("modalInfoPicoPlaca");

        btnInfo.addEventListener("click", function () {
            modalInfo.style.display = "flex";
        });

        function cerrarModalInfo() {
            modalInfo.style.display = "none";
        }
    </script>

    <?php
      include('../../../includes/auto_logout_modal.php');
    ?>
</body>
</html>