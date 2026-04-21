<?php
$conexion = new mysqli("localhost", "root", "", "tfg_pedidos_amazon");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if (!isset($_GET["id"])) {
    die("No se ha recibido el id de la orden.");
}

$orden_id = intval($_GET["id"]);
$mensaje = "";

// Obtener datos de la orden
$sqlOrden = "SELECT * FROM ordenes WHERE id = $orden_id";
$resultadoOrden = $conexion->query($sqlOrden);

if ($resultadoOrden->num_rows == 0) {
    die("La orden no existe.");
}

$orden = $resultadoOrden->fetch_assoc();

// Guardar línea de producto
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formato_id = intval($_POST["formato_id"]);
    $cantidad = intval($_POST["cantidad"]);
    $numero_palets = intval($_POST["numero_palets"]);
    $unidades_por_palet = intval($_POST["unidades_por_palet"]);
    $tipo_paletizado = $_POST["tipo_paletizado"];

    $sqlInsert = "INSERT INTO ordenes_productos 
        (orden_id, formato_id, cantidad, numero_palets, unidades_por_palet, tipo_paletizado)
        VALUES 
        ($orden_id, $formato_id, $cantidad, $numero_palets, $unidades_por_palet, '$tipo_paletizado')";

    if ($conexion->query($sqlInsert) === TRUE) {
        $mensaje = "Producto añadido correctamente a la orden.";
    } else {
        $mensaje = "Error: " . $conexion->error;
    }
}

// Obtener formatos para el select
$sqlFormatos = "
    SELECT 
        f.id,
        p.sku,
        p.nombre,
        f.formato
    FROM formatos_producto f
    JOIN productos p ON f.producto_sku = p.sku
    ORDER BY p.sku, f.formato
";
$resultadoFormatos = $conexion->query($sqlFormatos);

// Obtener líneas ya añadidas
$sqlLineas = "
    SELECT 
        op.id,
        p.sku,
        p.nombre AS producto,
        f.formato,
        op.cantidad,
        op.numero_palets,
        op.unidades_por_palet,
        op.tipo_paletizado
    FROM ordenes_productos op
    JOIN formatos_producto f ON op.formato_id = f.id
    JOIN productos p ON f.producto_sku = p.sku
    WHERE op.orden_id = $orden_id
    ORDER BY op.id DESC
";
$resultadoLineas = $conexion->query($sqlLineas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir productos a la orden</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="app">
    <aside class="sidebar">
        <h2>Logística Amazon</h2>
        <nav>
            <a href="index.html">Inicio</a>
            <a href="ordenes.php" class="activo">Órdenes</a>
            <a href="nueva_orden.php">Nueva orden</a>
            <a href="buscar.html">Buscar orden</a>
            <a href="asn.html">ASN</a>
            <a href="facturas.html">Facturas</a>
            <a href="envios.html">Envíos</a>
        </nav>
    </aside>

    <main class="contenido">
        <header class="cabecera">
            <h1>Añadir productos a la orden</h1>
            <p>
                PO: <strong><?php echo $orden["numero_orden"]; ?></strong> |
                Destino: <strong><?php echo $orden["destino"]; ?></strong> |
                Vendor: <strong><?php echo $orden["codigo_vendedor"]; ?></strong>
            </p>
        </header>

        <?php if ($mensaje != ""): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form class="formulario" method="POST">
            <div class="campo campo-completo">
                <label>Producto / formato</label>
                <select name="formato_id" required>
                    <option value="">Selecciona un formato</option>
                    <?php while ($formato = $resultadoFormatos->fetch_assoc()) { ?>
                        <option value="<?php echo $formato["id"]; ?>">
                            <?php echo $formato["sku"] . " - " . $formato["nombre"] . " - " . $formato["formato"]; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="campo">
                <label>Cantidad</label>
                <input type="number" name="cantidad" required>
            </div>

            <div class="campo">
                <label>Nº palets</label>
                <input type="number" name="numero_palets">
            </div>

            <div class="campo">
                <label>Unidades por palet</label>
                <input type="number" name="unidades_por_palet">
            </div>

            <div class="campo">
                <label>Tipo paletizado</label>
                <select name="tipo_paletizado">
                    <option value="ordering">ordering</option>
                    <option value="normal">normal</option>
                </select>
            </div>

            <div class="campo-completo">
                <button type="submit" class="boton-guardar">Añadir producto</button>
            </div>
        </form>

        <section class="cabecera" style="margin-top: 30px;">
            <h2>Líneas de la orden</h2>
        </section>

        <div class="cards">
            <?php
            if ($resultadoLineas->num_rows > 0) {
                while ($linea = $resultadoLineas->fetch_assoc()) {
                    echo "<div class='card'>";
                    echo "<h3>" . $linea['sku'] . " - " . $linea['formato'] . "</h3>";
                    echo "<p><strong>Producto:</strong> " . $linea['producto'] . "</p>";
                    echo "<p><strong>Cantidad:</strong> " . $linea['cantidad'] . "</p>";
                    echo "<p><strong>Palets:</strong> " . $linea['numero_palets'] . "</p>";
                    echo "<p><strong>Uds/palet:</strong> " . $linea['unidades_por_palet'] . "</p>";
                    echo "<p><strong>Tipo:</strong> " . $linea['tipo_paletizado'] . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p class='sin-lineas'>Todavía no hay productos añadidos a esta orden.</p>";
            }
            ?>
        </div>
    </main>
</div>

</body>
</html>