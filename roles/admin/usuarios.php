<?php
session_start();
require_once('../../conecct/conex.php');
include '../../includes/validarsession.php';
$db = new Database();
$con = $db->conectar();
$code = $_SESSION['documento'];
$sql = $con->prepare("SELECT*FROM usuarios
    INNER JOIN roles ON usuarios.id_rol = roles.id_rol 
    INNER JOIN estado_usuario ON usuarios.id_estado_usuario = estado_usuario.id_estado
    WHERE documento= :code");
$sql->bindParam(':code', $code);
$sql->execute();
$fila = $sql->fetch();


// Check for documento in session
$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    header('Location: ../../login.php');
    exit;
}


?>
<?php
$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    header('Location: ../../login.php');
    exit;
}

// Fetch nombre_completo and foto_perfil if not in session
$nombre_completo = $_SESSION['nombre_completo'] ?? null;
$foto_perfil = $_SESSION['foto_perfil'] ?? null;
if (!$nombre_completo || !$foto_perfil) {
    $user_query = $con->prepare("SELECT * FROM usuarios WHERE documento = :documento");
    $user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
    $user_query->execute();
    $user = $user_query->fetch(PDO::FETCH_ASSOC);
    $nombre_completo = $user['nombre_completo'] ?? 'Usuario';
    $foto_perfil = $user['foto_perfil'] ?: 'css/img/perfil.jpg';
    $_SESSION['nombre_completo'] = $nombre_completo;
    $_SESSION['foto_perfil'] = $foto_perfil;
}
    ?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel de Administrador</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="css/usuarios.css" />
  

</head>
<body>
  <?php include 'menu.php'; ?> <!-- Sidebar fuera del contenido principal -->

  <div class="content">
    <div class="buscador mb-3">
      <input type="text" id="buscar" class="form-control" placeholder="Buscar por nombre, documento o correo" onkeyup="filtrarTabla()">
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-bordered" id="tablaUsuarios">
        <thead class="text-center">
    
                <tr>
                    <th>#</th>
                    <th>Documento</th>
                    <th>Nombre Completo</th>
                    <th>Email</th>
                    <th>Telefono</th>
                    <th>Estado</th>
                    <th>Rol</th>
                    <th>Accion</th>
             

                </tr>
            </thead>
            <tbody>
                <?php
                $sql = $con->prepare("SELECT*FROM usuarios
                                        INNER JOIN roles ON usuarios.id_rol = roles.id_rol 
                                        INNER JOIN estado_usuario ON usuarios.id_estado_usuario = estado_usuario.id_estado");
                $sql->execute();
                $fila = $sql->fetchAll(PDO::FETCH_ASSOC);
                $count = 1;
                foreach ($fila as $resu) {
                ?>
                <tr class="text-center">    
                  <td><?php echo $count++; ?></td>
                  <td><?php echo htmlspecialchars($resu['documento']); ?></td>
                  <td><?php echo htmlspecialchars($resu['nombre_completo']); ?></td>
                  <td><?php echo htmlspecialchars($resu['email']); ?></td>
                  <td><?php echo htmlspecialchars($resu['telefono']); ?></td>
                  <td><?php echo htmlspecialchars($resu['tipo_stade']); ?></td>
                  <td><?php echo htmlspecialchars($resu['tip_rol']); ?></td>
                  <td>
                    <div class="d-flex justify-content-center action-buttons">
                      <button class="text-primary me-2 edit-user" data-id="<?php echo htmlspecialchars($resu['documento']); ?>">
                        <i class="bi bi-pencil-square action-icon" title="Editar"></i>
                      </button>
                      <button class="text-danger delete-user" data-id="<?php echo htmlspecialchars($resu['documento']); ?>">
                        <i class="bi bi-trash action-icon" title="Eliminar"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
        <nav>
      <ul class="pagination justify-content-center" id="paginacion"></ul>
    </nav>
  <div class="boton-agregar">
        <a id="btnAgregarUsuario" href="agregar_usuario.php" class="boton">
            <i class="bi bi-plus-circle"></i> <i class="bi bi-search"></i>Agregar Usuario
        </a>
    </div>
    </div>
  </div>
  <?php include 'modals_usuarios/usuario_modals.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="modals_usuarios/usuarios_scripts.js"></script>
  
<script>

        function filtrarTabla() {
        const input = document.getElementById('buscar');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('tablaUsuarios');
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let match = false;

            for (let j = 0; j < cells.length; j++) {
                if (cells[j]) {
                    const text = cells[j].textContent || cells[j].innerText;
                    if (text.toLowerCase().indexOf(filter) > -1) {
                        match = true;
                        break;
                    }
                }
            }

            rows[i].style.display = match ? '' : 'none';
        }
    }


  const filasPorPagina = 5; // Cambia este valor si deseas más/menos filas por página

  function configurarPaginacion() {
    const tabla = document.getElementById('tablaUsuarios');
    const filas = tabla.querySelectorAll('tbody tr');
    const totalFilas = filas.length;
    const totalPaginas = Math.ceil(totalFilas / filasPorPagina);
    const paginacion = document.getElementById('paginacion');

    function mostrarPagina(pagina) {
      let inicio = (pagina - 1) * filasPorPagina;
      let fin = inicio + filasPorPagina;

      filas.forEach((fila, index) => {
        fila.style.display = (index >= inicio && index < fin) ? '' : 'none';
      });

      // actualizar botones activos
      const botones = paginacion.querySelectorAll('li');
      botones.forEach(btn => btn.classList.remove('active'));
      if (botones[pagina - 1]) botones[pagina - 1].classList.add('active');
    }

    function crearBotones() {
      paginacion.innerHTML = '';
      for (let i = 1; i <= totalPaginas; i++) {
        const li = document.createElement('li');
        li.className = 'page-item' + (i === 1 ? ' active' : '');
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = i;
        a.addEventListener('click', function (e) {
          e.preventDefault();
          mostrarPagina(i);
        });
        li.appendChild(a);
        paginacion.appendChild(li);
      }
    }

    crearBotones();
    mostrarPagina(1);
  }

  window.addEventListener('DOMContentLoaded', configurarPaginacion);

  </script>
</body>
</html>
