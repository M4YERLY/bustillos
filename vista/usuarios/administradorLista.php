<?php
ob_start();
session_start();
include("../../modelo/conexion.php"); // Incluir la conexión a la base de datos
include("../../componentes/header.php"); // Incluir el header

// Verificar si el usuario está autenticado como admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Conectar a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Funcionalidad de búsqueda
$busqueda = $_GET['busqueda'] ?? '';

// Consultas para obtener los datos de usuarios según el filtro de búsqueda
$query_admin = "
    SELECT a.id, a.nombre, a.apellido_paterno, a.apellido_materno, a.email, a.telefono, a.foto_perfil, u.username
    FROM administradores a 
    JOIN usuarios u ON a.usuario_id = u.id
    WHERE a.nombre LIKE :busqueda OR a.apellido_paterno LIKE :busqueda OR a.apellido_materno LIKE :busqueda
";
$query_emp = "
    SELECT e.id, e.nombre, e.apellido_paterno, e.apellido_materno, e.email, e.telefono, e.foto_perfil, e.salario, u.username
    FROM empleados e 
    JOIN usuarios u ON e.usuario_id = u.id
    WHERE e.nombre LIKE :busqueda OR e.apellido_paterno LIKE :busqueda OR e.apellido_materno LIKE :busqueda
";
$query_client = "
    SELECT c.id, c.nombre, c.apellido_paterno, c.apellido_materno, c.email, c.telefono, c.foto_perfil, u.username, c.razon_social, c.nit
    FROM clientes c 
    JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.nombre LIKE :busqueda OR c.apellido_paterno LIKE :busqueda OR c.apellido_materno LIKE :busqueda
";

$stmt_admin = $conn->prepare($query_admin);
$stmt_emp = $conn->prepare($query_emp);
$stmt_client = $conn->prepare($query_client);

// Vincular parámetro de búsqueda
$param_busqueda = '%' . $busqueda . '%';
$stmt_admin->bindParam(':busqueda', $param_busqueda);
$stmt_emp->bindParam(':busqueda', $param_busqueda);
$stmt_client->bindParam(':busqueda', $param_busqueda);

$stmt_admin->execute();
$stmt_emp->execute();
$stmt_client->execute();

$administradores = $stmt_admin->fetchAll(PDO::FETCH_ASSOC);
$empleados = $stmt_emp->fetchAll(PDO::FETCH_ASSOC);
$clientes = $stmt_client->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .profile-img {
        width: 90px;
        height: 90px;
        object-fit: cover;
        border-radius: 50%;
    }

    .search-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .search-bar .search-input {
        flex: 1;
        margin-right: 10px;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h2 class="text-primary text-center mt-4">Lista de Usuarios</h2>

        <!-- Barra de búsqueda -->
        <div class="search-bar">
            <form method="GET" action="" class="d-flex">
                <input type="text" name="busqueda" class="form-control search-input" placeholder="Buscar usuarios..." value="<?= htmlspecialchars($busqueda) ?>">
                <button type="submit" class="btn btn-primary">Buscar</button>
            </form>
            <div>
                <a href="usuarioRegistro.php" class="btn btn-success">Agregar Usuario</a>
                <a href="../pdf/usuarioLista.php" class="btn btn-danger">Generar PDF</a>
            </div>
        </div>

        <!-- Tabla de Administradores -->
        <h3 class="text-secondary text-center mt-4">Administradores</h3>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Nombre de Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($administradores as $admin): ?>
                        <tr>
                            <td><img src="../../<?= htmlspecialchars($admin['foto_perfil']) ?>" alt="Foto de Perfil" class="profile-img"></td>
                            <td><?= htmlspecialchars($admin['nombre'] . ' ' . $admin['apellido_paterno'] . ' ' . $admin['apellido_materno']) ?></td>
                            <td><?= htmlspecialchars($admin['email']) ?></td>
                            <td><?= htmlspecialchars($admin['telefono']) ?></td>
                            <td><?= htmlspecialchars($admin['username']) ?></td>
                            <td>
                                <a href="administradorEdicion.php?id=<?= $admin['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="administradorEliminacion.php?id=<?= $admin['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este registro?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabla de Empleados -->
        <h3 class="text-secondary text-center mt-4">Empleados</h3>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Salario</th>
                        <th>Nombre de Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empleados as $emp): ?>
                        <tr>
                            <td><img src="../../<?= htmlspecialchars($emp['foto_perfil']) ?>" alt="Foto de Perfil" class="profile-img"></td>
                            <td><?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido_paterno'] . ' ' . $emp['apellido_materno']) ?></td>
                            <td><?= htmlspecialchars($emp['email']) ?></td>
                            <td><?= htmlspecialchars($emp['telefono']) ?></td>
                            <td><?= htmlspecialchars($emp['salario']) ?></td>
                            <td><?= htmlspecialchars($emp['username']) ?></td>
                            <td>
                                <a href="empleadoEdicion.php?id=<?= $emp['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="empleadoEliminacion.php?id=<?= $emp['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este registro?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabla de Clientes -->
        <h3 class="text-secondary text-center mt-4">Clientes</h3>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Nombre de Usuario</th>
                        <th>Razón Social</th>
                        <th>NIT</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $client): ?>
                        <tr>
                            <td><img src="../../<?= htmlspecialchars($client['foto_perfil']) ?>" alt="Foto de Perfil" class="profile-img"></td>
                            <td><?= htmlspecialchars($client['nombre'] . ' ' . $client['apellido_paterno'] . ' ' . $client['apellido_materno']) ?></td>
                            <td><?= htmlspecialchars($client['email']) ?></td>
                            <td><?= htmlspecialchars($client['telefono']) ?></td>
                            <td><?= htmlspecialchars($client['username']) ?></td>
                            <td><?= htmlspecialchars($client['razon_social']) ?></td>
                            <td><?= htmlspecialchars($client['nit']) ?></td>
                            <td>
                                <a href="clienteEdicion.php?id=<?= $client['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="clienteEliminacion.php?id=<?= $client['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este registro?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include("../../componentes/footer.php"); ?>
