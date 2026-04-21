<?php
$conexion = new mysqli("localhost", "root", "", "tfg_pedidos_amazon");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

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

    $sql = "INSERT INTO ordenes 
    (numero_orden, grupo_destino, destino, codigo_vendedor, tipo_codigo_vendedor, fecha_orden, estado, observaciones, window_start, window_end, numero_palets)
    VALUES 
    ('$numero_orden', '$grupo_destino', '$destino', '$codigo_vendedor', '$tipo_codigo_vendedor', '$fecha_orden', '$estado', '$observaciones', '$window_start', '$window_end', '$numero_palets')";

    if ($conexion->query($sql) === TRUE) {
        $mensaje = "Orden guardada correctamente";
    } else {
        $mensaje = "Error: " . $conexion->error;
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
            <a href="index.html">Inicio</a>
            <a href="ordenes.php">Órdenes</a>
            <a href="nueva_orden.php" class="activo">Nueva orden</a>
            <a href="buscar.html">Buscar orden</a>
            <a href="asn.html">ASN</a>
            <a href="facturas.html">Facturas</a>
            <a href="envios.html">Envíos</a>
        </nav>
    </aside>

    <main class="contenido">
        <header class="cabecera">
            <h1>Nueva orden</h1>
            <p>Registrar una nueva orden</p>
        </header>

        <?php if ($mensaje != ""): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form class="formulario" method="POST">

            <div class="campo">
                <label>PO</label>
                <input type="text" name="numero_orden" required>
            </div>

            <div class="campo">
                <label>Grupo destino</label>
                <input type="text" name="grupo_destino">
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
                <input type="number" name="numero_palets">
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