<?php
$conexion = new mysqli("localhost", "root", "", "tfg_pedidos_amazon");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if (!isset($_GET["id"])) {
    die("No se ha recibido el id de la orden.");
}

$id = intval($_GET["id"]);
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero_orden = $_POST["numero_orden"];
    $grupo_destino = $_POST["grupo_destino"];
    $destino = $_POST["destino"];
    $codigo_vendedor = $_POST["codigo_vendedor"];
    $tipo_codigo_vendedor = $_POST["tipo_codigo_vendedor"];
    $fecha_orden = $_POST["fecha_orden"];
    $estado = $_POST["estado"];
    $observaciones = $_POST["observaciones"];
    $window_start = $_POST["window_start"];
    $window_end = $_POST["window_end"];
    $numero_palets = $_POST["numero_palets"];

    $sql = "
        UPDATE ordenes SET
            numero_orden = '$numero_orden',
            grupo_destino = '$grupo_destino',
            destino = '$destino',
            codigo_vendedor = '$codigo_vendedor',
            tipo_codigo_vendedor = '$tipo_codigo_vendedor',
            fecha_orden = '$fecha_orden',
            estado = '$estado',
            observaciones = '$observaciones',
            window_start = '$window_start',
            window_end = '$window_end',
            numero_palets = '$numero_palets'
        WHERE id = $id
    ";

    if ($conexion->query($sql)) {
        $mensaje = "Orden actualizada correctamente";
    } else {
        $mensaje = "Error al actualizar: " . $conexion->error;
    }
}

$resultado = $conexion->query("SELECT * FROM ordenes WHERE id = $id");

if ($resultado->num_rows == 0) {
    die("La orden no existe.");
}

$orden = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar orden</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="app">
    <aside class="sidebar">
        <h2>Logística Amazon</h2>
        <nav>
            <a href="index.php">Inicio</a>
            <a href="ordenes.php" class="activo">Órdenes</a>
            <a href="nueva_orden.php">Nueva orden</a>
            <a href="buscar.php">Buscar orden</a>
            <a href="asn.php">ASN</a>
            <a href="facturas.php">Facturas</a>
            <a href="envios.php">Envíos</a>
        </nav>
    </aside>

    <main class="contenido">
        <header class="cabecera">
            <h1>Editar orden</h1>
            <p>Modificar los datos principales de la orden</p>
        </header>

        <?php if ($mensaje != ""): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <section class="panel-seccion">
            <form class="formulario" method="POST">
                <div class="campo">
                    <label>PO</label>
                    <input type="text" name="numero_orden" value="<?php echo $orden['numero_orden']; ?>" required>
                </div>

                <div class="campo">
                    <label>Grupo destino</label>
                    <input type="text" name="grupo_destino" value="<?php echo $orden['grupo_destino']; ?>">
                </div>

                <div class="campo">
                    <label>Destino</label>
                    <input type="text" name="destino" value="<?php echo $orden['destino']; ?>" required>
                </div>

                <div class="campo">
                    <label>Vendor code</label>
                    <input type="text" name="codigo_vendedor" value="<?php echo $orden['codigo_vendedor']; ?>" required>
                </div>

                <div class="campo">
                    <label>Tipo</label>
                    <select name="tipo_codigo_vendedor">
                        <option value="ordering" <?php if ($orden['tipo_codigo_vendedor'] == 'ordering') echo 'selected'; ?>>ordering</option>
                        <option value="normal" <?php if ($orden['tipo_codigo_vendedor'] == 'normal') echo 'selected'; ?>>normal</option>
                    </select>
                </div>

                <div class="campo">
                    <label>Fecha orden</label>
                    <input type="date" name="fecha_orden" value="<?php echo $orden['fecha_orden']; ?>">
                </div>

                <div class="campo">
                    <label>Estado</label>
                    <input type="text" name="estado" value="<?php echo $orden['estado']; ?>">
                </div>

                <div class="campo">
                    <label>Window start</label>
                    <input type="date" name="window_start" value="<?php echo $orden['window_start']; ?>">
                </div>

                <div class="campo">
                    <label>Window end</label>
                    <input type="date" name="window_end" value="<?php echo $orden['window_end']; ?>">
                </div>

                <div class="campo">
                    <label>Nº palets</label>
                    <input type="number" name="numero_palets" value="<?php echo $orden['numero_palets']; ?>">
                </div>

                <div class="campo campo-completo">
                    <label>Observaciones</label>
                    <textarea name="observaciones"><?php echo $orden['observaciones']; ?></textarea>
                </div>

                <div class="campo-completo">
                    <button type="submit" class="boton-guardar">Guardar cambios</button>
                    <a href="ordenes.php" class="boton-card boton-ver">Volver</a>
                </div>
            </form>
        </section>
    </main>
</div>

</body>
</html>