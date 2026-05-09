<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = new mysqli("localhost", "root", "", "tfg_pedidos_amazon");
    $conexion->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

$mensaje = "";

/* Crear ASN */
if (isset($_POST["crear_asn"])) {
    $numero_asn = $_POST["numero_asn"];
    $fecha_asn = $_POST["fecha_asn"];
    $estado = $_POST["estado"];
    $observaciones = $_POST["observaciones"];

    try {
        $stmt = $conexion->prepare("
            INSERT INTO asns (numero_asn, fecha_asn, estado, observaciones)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param("ssss", $numero_asn, $fecha_asn, $estado, $observaciones);
        $stmt->execute();

        $mensaje = "ASN creado correctamente";
    } catch (mysqli_sql_exception $e) {
        $mensaje = "Error al crear ASN: " . $e->getMessage();
    }
}

/* Asociar orden a ASN */
if (isset($_POST["asociar_orden"])) {
    $asn_id = intval($_POST["asn_id"]);
    $orden_id = intval($_POST["orden_id"]);

    try {
        $stmt = $conexion->prepare("
            INSERT INTO asn_ordenes (asn_id, orden_id)
            VALUES (?, ?)
        ");

        $stmt->bind_param("ii", $asn_id, $orden_id);
        $stmt->execute();

        $mensaje = "Orden asociada al ASN correctamente";
    } catch (mysqli_sql_exception $e) {
        $mensaje = "Error al asociar orden: " . $e->getMessage();
    }
}

$asns = $conexion->query("SELECT * FROM asns ORDER BY id DESC");
$ordenes = $conexion->query("SELECT * FROM ordenes ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ASN</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="app">
    <aside class="sidebar">
        <h2>Logística Amazon</h2>
        <nav>
            <a href="index.html">Inicio</a>
            <a href="ordenes.php">Órdenes</a>
            <a href="nueva_orden.php">Nueva orden</a>
            <a href="buscar.php">Buscar orden</a>
            <a href="asn.php" class="activo">ASN</a>
            <a href="facturas.php">Facturas</a>
            <a href="envios.php">Envíos</a>
        </nav>
    </aside>

    <main class="contenido">
        <header class="cabecera">
            <h1>ASN</h1>
            <p>Registro de ASN proporcionados por Amazon y asociación con órdenes</p>
        </header>

        <?php if ($mensaje != ""): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <section class="panel-seccion">
            <h2>Crear ASN</h2>

            <form class="formulario" method="POST">
                <input type="hidden" name="crear_asn" value="1">

                <div class="campo">
                    <label>Número ASN</label>
                    <input type="text" name="numero_asn" required>
                </div>

                <div class="campo">
                    <label>Fecha ASN</label>
                    <input type="date" name="fecha_asn">
                </div>

                <div class="campo">
                    <label>Estado</label>
                    <input type="text" name="estado" value="pendiente">
                </div>

                <div class="campo campo-completo">
                    <label>Observaciones</label>
                    <textarea name="observaciones"></textarea>
                </div>

                <div class="campo-completo">
                    <button type="submit" class="boton-guardar">Crear ASN</button>
                </div>
            </form>
        </section>

        <section class="panel-seccion">
            <h2>Asociar orden a ASN</h2>

            <form class="formulario" method="POST">
                <input type="hidden" name="asociar_orden" value="1">

                <div class="campo">
                    <label>ASN</label>
                    <select name="asn_id" required>
                        <option value="">Selecciona un ASN</option>
                        <?php
                        $asnsSelect = $conexion->query("SELECT * FROM asns ORDER BY id DESC");
                        while ($asn = $asnsSelect->fetch_assoc()) {
                            echo "<option value='" . $asn['id'] . "'>" . $asn['numero_asn'] . " - " . $asn['estado'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="campo">
                    <label>Orden</label>
                    <select name="orden_id" required>
                        <option value="">Selecciona una orden</option>
                        <?php while ($orden = $ordenes->fetch_assoc()) { ?>
                            <option value="<?php echo $orden['id']; ?>">
                                <?php echo $orden['numero_orden'] . " - " . $orden['destino'] . " - " . $orden['codigo_vendedor']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="campo-completo">
                    <button type="submit" class="boton-guardar">Asociar orden</button>
                </div>
            </form>
        </section>

        <section class="panel-seccion">
            <h2>ASN registrados</h2>

            <div class="cards">
                <?php while ($asn = $asns->fetch_assoc()) { ?>
                    <div class="card">
                        <h3>ASN: <?php echo $asn["numero_asn"]; ?></h3>
                        <p><strong>Fecha:</strong> <?php echo $asn["fecha_asn"]; ?></p>
                        <p><strong>Estado:</strong> <?php echo $asn["estado"]; ?></p>
                        <p><strong>Observaciones:</strong> <?php echo $asn["observaciones"]; ?></p>

                        <div class="lineas-box">
                            <h4>Órdenes asociadas</h4>

                            <?php
                            $idAsn = $asn["id"];

                            $ordenesAsn = $conexion->query("
                                SELECT 
                                    o.numero_orden,
                                    o.destino,
                                    o.codigo_vendedor,
                                    o.estado
                                FROM asn_ordenes ao
                                JOIN ordenes o ON ao.orden_id = o.id
                                WHERE ao.asn_id = $idAsn
                                ORDER BY o.id DESC
                            ");

                            if ($ordenesAsn->num_rows > 0) {
                                while ($o = $ordenesAsn->fetch_assoc()) {
                                    echo "<div class='linea-item'>";
                                    echo "<p><strong>PO:</strong> " . $o['numero_orden'] . "</p>";
                                    echo "<p><strong>Destino:</strong> " . $o['destino'] . "</p>";
                                    echo "<p><strong>Vendor:</strong> " . $o['codigo_vendedor'] . "</p>";
                                    echo "<p><strong>Estado orden:</strong> " . $o['estado'] . "</p>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<p class='sin-lineas'>Sin órdenes asociadas.</p>";
                            }
                            ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>
    </main>
</div>

</body>
</html>