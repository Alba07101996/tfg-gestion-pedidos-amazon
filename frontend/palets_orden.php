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

// Crear nuevo palet
if (isset($_POST["crear_palet"])) {
    $codigo_palet = trim($_POST["codigo_palet"]);
    $tipo_palet = trim($_POST["tipo_palet"]);
    $destino = $orden["destino"];
    $grupo_destino = $orden["grupo_destino"];
    $estado = "pendiente";
    $observaciones = trim($_POST["observaciones_palet"]);

    $sqlInsertPalet = "INSERT INTO palets (codigo_palet, tipo_palet, destino, grupo_destino, estado, observaciones)
                       VALUES ('$codigo_palet', '$tipo_palet', '$destino', '$grupo_destino', '$estado', '$observaciones')";

    if ($conexion->query($sqlInsertPalet) === TRUE) {
        $mensaje = "Palet creado correctamente.";
    } else {
        die("Error SQL al crear palet: " . $conexion->error);
    }
}

// Añadir línea a palet
if (isset($_POST["anadir_linea"])) {
    $palet_id = intval($_POST["palet_id"]);
    $formato_id = intval($_POST["formato_id"]);
    $cantidad = intval($_POST["cantidad"]);

    $sqlInsertLinea = "INSERT INTO palet_lineas (palet_id, orden_id, formato_id, cantidad)
                       VALUES ($palet_id, $orden_id, $formato_id, $cantidad)";

    if ($conexion->query($sqlInsertLinea) === TRUE) {
        $mensaje = "Línea añadida al palet correctamente.";
    } else {
        die("Error SQL al añadir línea: " . $conexion->error);
    }
}

// Obtener formatos disponibles
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar palets</title>
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
            <h1>Gestionar palets de la orden</h1>
            <p>
                PO: <strong><?php echo $orden["numero_orden"]; ?></strong> |
                Destino: <strong><?php echo $orden["destino"]; ?></strong> |
                Vendor: <strong><?php echo $orden["codigo_vendedor"]; ?></strong>
            </p>
        </header>

        <?php if ($mensaje != ""): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <section class="panel-seccion">
            <h2>Crear nuevo palet</h2>

            <form class="formulario" method="POST">
                <input type="hidden" name="crear_palet" value="1">

                <div class="campo">
                    <label>Código palet</label>
                    <input type="text" name="codigo_palet" required>
                </div>

                <div class="campo">
                    <label>Tipo palet</label>
                    <select name="tipo_palet">
                        <option value="ordering">ordering</option>
                        <option value="normal">normal</option>
                    </select>
                </div>

                <div class="campo campo-completo">
                    <label>Observaciones</label>
                    <textarea name="observaciones_palet"></textarea>
                </div>

                <div class="campo-completo">
                    <button type="submit" class="boton-guardar">Crear palet</button>
                </div>
            </form>
        </section>

        <section class="panel-seccion">
            <h2>Añadir contenido a un palet</h2>

            <form class="formulario" method="POST">
                <input type="hidden" name="anadir_linea" value="1">

                <div class="campo">
                    <label>Palet</label>
                    <select name="palet_id" required>
                        <option value="">Selecciona un palet</option>
                        <?php
                        $resultadoPaletsSelect = $conexion->query("SELECT * FROM palets WHERE destino = '{$orden['destino']}' ORDER BY id DESC");
                        while ($palet = $resultadoPaletsSelect->fetch_assoc()) {
                            echo "<option value='{$palet['id']}'>{$palet['codigo_palet']} ({$palet['tipo_palet']})</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="campo">
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

                <div class="campo-completo">
                    <button type="submit" class="boton-guardar">Añadir línea al palet</button>
                </div>
            </form>
        </section>

        <section class="panel-seccion">
            <h2>Palets de la orden</h2>

            <div class="cards">
                <?php
                $resultadoPaletsVista = $conexion->query("SELECT * FROM palets WHERE destino = '{$orden['destino']}' ORDER BY id DESC");

                if ($resultadoPaletsVista->num_rows > 0) {
                    while ($palet = $resultadoPaletsVista->fetch_assoc()) {
                        echo "<div class='card'>";
                        echo "<h3>{$palet['codigo_palet']}</h3>";
                        echo "<p><strong>Tipo:</strong> {$palet['tipo_palet']}</p>";
                        echo "<p><strong>Destino:</strong> {$palet['destino']}</p>";
                        echo "<p><strong>Estado:</strong> {$palet['estado']}</p>";

                        echo "<div class='lineas-box'>";
                        echo "<h4>Contenido del palet</h4>";

                        $palet_id = $palet["id"];

                        $sqlContenido = "
                            SELECT 
                                p.sku,
                                p.nombre AS producto,
                                f.formato,
                                pl.cantidad
                            FROM palet_lineas pl
                            JOIN formatos_producto f ON pl.formato_id = f.id
                            JOIN productos p ON f.producto_sku = p.sku
                            WHERE pl.palet_id = $palet_id
                              AND pl.orden_id = $orden_id
                        ";

                        $resultadoContenido = $conexion->query($sqlContenido);

                        if ($resultadoContenido->num_rows > 0) {
                            while ($linea = $resultadoContenido->fetch_assoc()) {
                                echo "<div class='linea-item'>";
                                echo "<p><strong>SKU:</strong> {$linea['sku']}</p>";
                                echo "<p><strong>Producto:</strong> {$linea['producto']}</p>";
                                echo "<p><strong>Formato:</strong> {$linea['formato']}</p>";
                                echo "<p><strong>Cantidad:</strong> {$linea['cantidad']}</p>";
                                echo "</div>";
                            }
                        } else {
                            echo "<p class='sin-lineas'>Este palet todavía no tiene líneas para esta orden.</p>";
                        }

                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='sin-lineas'>Todavía no hay palets creados.</p>";
                }
                ?>
            </div>
        </section>
    </main>
</div>

</body>
</html>
