<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = new mysqli("localhost", "root", "", "tfg_pedidos_amazon");
    $conexion->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

$mensaje = "";
$mensaje_tipo = "ok";

$paises = ["España", "Francia", "Italia", "Alemania", "Reino Unido"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $numero_orden         = trim($_POST["numero_orden"]);
    $grupo_destino        = $_POST["grupo_destino"];
    $destino              = trim($_POST["destino"]);
    $codigo_vendedor      = trim($_POST["codigo_vendedor"]);
    $tipo_codigo_vendedor = $_POST["tipo_codigo_vendedor"];
    $fecha_orden          = $_POST["fecha_orden"];
    $estado               = trim($_POST["estado"]);
    $observaciones        = trim($_POST["observaciones"]);
    $window_start         = $_POST["window_start"];
    $window_end           = $_POST["window_end"];
    $numero_palets        = intval($_POST["numero_palets"]);

    // Validar que el país es uno de los permitidos
    if (!in_array($grupo_destino, $paises)) {
        $mensaje_tipo = "error";
        $mensaje = "País no válido.";
    } else {

        try {
            $stmt = $conexion->prepare("
                INSERT INTO ordenes 
                (numero_orden, grupo_destino, destino, codigo_vendedor, tipo_codigo_vendedor, fecha_orden, estado, observaciones, window_start, window_end, numero_palets)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "ssssssssssi",
                $numero_orden,
                $grupo_destino,
                $destino,
                $codigo_vendedor,
                $tipo_codigo_vendedor,
                $fecha_orden,
                $estado,
                $observaciones,
                $window_start,
                $window_end,
                $numero_palets
            );

            $stmt->execute();
            $mensaje = "Orden guardada correctamente.";

        } catch (mysqli_sql_exception $e) {
            $mensaje_tipo = "error";
            $mensaje = "Error al guardar la orden: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva orden</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="app">
    <aside class="sidebar">
        <h2>Logística Amazon</h2>
        <nav>
            <a href="index.php">Inicio</a>
            <a href="ordenes.php">Órdenes</a>
            <a href="nueva_orden.php" class="activo">Nueva orden</a>
            <a href="buscar.php">Buscar orden</a>
            <a href="asn.php">ASN</a>
            <a href="facturas.php">Facturas</a>
            <a href="envios.php">Envíos</a>
        </nav>
    </aside>

    <main class="contenido">
        <header class="cabecera">
            <h1>Nueva orden</h1>
            <p>Registrar una nueva orden</p>
        </header>

        <?php if ($mensaje != ""): ?>
            <div class="mensaje <?php echo $mensaje_tipo === 'error' ? 'mensaje-error' : ''; ?>">
                <?php echo h($mensaje); ?>
            </div>
        <?php endif; ?>

        <form class="formulario" method="POST">

            <div class="campo">
                <label>PO</label>
                <input type="text" name="numero_orden" required>
            </div>

            <div class="campo">
                <label>País</label>
                <select name="grupo_destino" required>
                    <option value="">Selecciona un país</option>
                    <?php foreach ($paises as $pais): ?>
                        <option value="<?php echo h($pais); ?>">
                            <?php echo h($pais); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label>Destino</label>
                <input type="text" name="destino" required>
            </div>

            <div class="campo">
                <label>Vendor code</label>
                <input type="text" name="codigo_vendedor" required>
            </div>

            <div class="campo">
                <label>Tipo</label>
                <select name="tipo_codigo_vendedor">
                    <option value="ordering">ordering</option>
                    <option value="normal">normal</option>
                </select>
            </div>

            <div class="campo">
                <label>Fecha orden</label>
                <input type="date" name="fecha_orden">
            </div>

            <div class="campo">
                <label>Estado</label>
                <input type="text" name="estado" value="pendiente">
            </div>

            <div class="campo">
                <label>Window start</label>
                <input type="date" name="window_start">
            </div>

            <div class="campo">
                <label>Window end</label>
                <input type="date" name="window_end">
            </div>

            <div class="campo">
                <label>Nº palets</label>
                <input type="number" name="numero_palets" min="0">
            </div>

            <div class="campo campo-completo">
                <label>Observaciones</label>
                <textarea name="observaciones"></textarea>
            </div>

            <div class="campo-completo">
                <button class="boton-guardar">Guardar orden</button>
            </div>

        </form>
    </main>
</div>

</body>
</html>