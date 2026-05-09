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

$transportistas = ["Dachser", "Amazon Freight"];
$servicios = ["Red", "Grupaje"];

if (isset($_POST["crear_envio"])) {
    $orden_ids = $_POST["orden_ids"] ?? [];
    $transportista = trim($_POST["transportista"]);
    $tipo_servicio = trim($_POST["tipo_servicio"]);
    $tracking = trim($_POST["tracking"]);
    $url_dachser = trim($_POST["url_dachser"] ?? "");
    $estado_envio = trim($_POST["estado_envio"]);
    $fecha_envio = $_POST["fecha_envio"];
    $fecha_entrega = $_POST["fecha_entrega"];

    if ($transportista !== "Dachser") {
        $url_dachser = "";
    }

    if (empty($orden_ids)) {
        $mensaje_tipo = "error";
        $mensaje = "Debes seleccionar al menos una orden.";
    } else {
        try {
            $ids_limpios = array_map("intval", $orden_ids);
            $placeholders = implode(",", array_fill(0, count($ids_limpios), "?"));
            $tipos = str_repeat("i", count($ids_limpios));

            $stmtCheck = $conexion->prepare("
                SELECT DISTINCT destino 
                FROM ordenes 
                WHERE id IN ($placeholders)
            ");
            $stmtCheck->bind_param($tipos, ...$ids_limpios);
            $stmtCheck->execute();
            $destinos = $stmtCheck->get_result()->fetch_all(MYSQLI_ASSOC);

            if (count($destinos) > 1) {
                throw new Exception("Todas las órdenes del envío deben tener el mismo destino.");
            }

            $destino_envio = $destinos[0]["destino"];

            $stmtEnvio = $conexion->prepare("
                INSERT INTO envios 
                (transportista, tipo_servicio, tracking, url_dachser, estado_envio, fecha_envio, fecha_entrega)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmtEnvio->bind_param(
                "sssssss",
                $transportista,
                $tipo_servicio,
                $tracking,
                $url_dachser,
                $estado_envio,
                $fecha_envio,
                $fecha_entrega
            );

            $stmtEnvio->execute();
            $envio_id = $conexion->insert_id;

            $stmtRel = $conexion->prepare("
                INSERT INTO envio_ordenes (envio_id, orden_id)
                VALUES (?, ?)
            ");

            foreach ($ids_limpios as $oid) {
                $stmtRel->bind_param("ii", $envio_id, $oid);
                $stmtRel->execute();
            }

            $mensaje = "Envío registrado correctamente para destino $destino_envio.";
        } catch (mysqli_sql_exception $e) {
            $mensaje_tipo = "error";

            if ($e->getCode() == 1062) {
                $mensaje = "Una o más órdenes ya están asignadas a otro envío.";
            } else {
                $mensaje = "Error SQL: " . $e->getMessage();
            }
        } catch (Exception $e) {
            $mensaje_tipo = "error";
            $mensaje = $e->getMessage();
        }
    }
}

$ordenes = $conexion->query("
    SELECT o.id, o.numero_orden, o.destino, o.grupo_destino
    FROM ordenes o
    LEFT JOIN envio_ordenes eo ON o.id = eo.orden_id
    WHERE eo.orden_id IS NULL
    ORDER BY o.destino ASC, o.id DESC
");

$ordenes_array = [];
while ($o = $ordenes->fetch_assoc()) {
    $ordenes_array[] = $o;
}

$envios = $conexion->query("SELECT * FROM envios ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Envíos</title>
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
            <a href="asn.php">ASN</a>
            <a href="facturas.php">Facturas</a>
            <a href="envios.php" class="activo">Envíos</a>
        </nav>
    </aside>

    <main class="contenido">

        <header class="cabecera">
            <h1>Envíos</h1>
            <p>Registro y seguimiento de envíos</p>
        </header>

        <?php if ($mensaje != ""): ?>
            <div class="mensaje <?php echo $mensaje_tipo === 'error' ? 'mensaje-error' : ''; ?>">
                <?php echo h($mensaje); ?>
            </div>
        <?php endif; ?>

        <section class="panel-seccion">
            <h2>Registrar envío</h2>

            <form class="formulario" method="POST">
                <input type="hidden" name="crear_envio" value="1">

                <div class="campo campo-completo">
                    <label>Órdenes del envío</label>

                    <div class="lista-ordenes">
                        <?php if (empty($ordenes_array)): ?>
                            <p class="sin-lineas">No hay órdenes disponibles sin envío asignado.</p>
                        <?php else: ?>
                            <?php foreach ($ordenes_array as $o): ?>
                                <label class="orden-checkbox">
                                    <input type="checkbox" name="orden_ids[]" value="<?php echo h($o["id"]); ?>">
                                    <span>
                                        <strong><?php echo h($o["numero_orden"]); ?></strong>
                                        — <?php echo h($o["grupo_destino"]); ?>
                                        — <?php echo h($o["destino"]); ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="campo">
                    <label>Transportista</label>
                    <select name="transportista" required>
                        <option value="">Selecciona transportista</option>
                        <?php foreach ($transportistas as $t): ?>
                            <option value="<?php echo h($t); ?>"><?php echo h($t); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label>Tipo de servicio</label>
                    <select name="tipo_servicio" required>
                        <option value="">Selecciona tipo</option>
                        <?php foreach ($servicios as $s): ?>
                            <option value="<?php echo h($s); ?>"><?php echo h($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label>Nº envío / tracking</label>
                    <input type="text" name="tracking" placeholder="Ej: 123456789">
                </div>

                <div class="campo campo-completo">
                    <label>URL seguimiento Dachser</label>
                    <input type="url" name="url_dachser" placeholder="Solo se guardará si el transportista es Dachser">
                </div>

                <div class="campo">
                    <label>Estado</label>
                    <select name="estado_envio">
                        <option value="pendiente">Pendiente</option>
                        <option value="en_transito">En tránsito</option>
                        <option value="entregado">Entregado</option>
                        <option value="incidencia">Incidencia</option>
                    </select>
                </div>

                <div class="campo">
                    <label>Fecha envío</label>
                    <input type="date" name="fecha_envio">
                </div>

                <div class="campo">
                    <label>Fecha entrega estimada</label>
                    <input type="date" name="fecha_entrega">
                </div>

                <div class="campo-completo">
                    <button type="submit" class="boton-guardar">Registrar envío</button>
                </div>
            </form>
        </section>

        <section class="panel-seccion">
            <h2>Envíos registrados</h2>

            <div class="cards">
                <?php if ($envios->num_rows === 0): ?>
                    <p class="sin-lineas">No hay envíos registrados todavía.</p>
                <?php else: ?>
                    <?php while ($e = $envios->fetch_assoc()): ?>
                        <div class="card">
                            <h3>Envío #<?php echo h($e["id"]); ?></h3>
                            <p><strong>Transportista:</strong> <?php echo h($e["transportista"]); ?></p>
                            <p><strong>Servicio:</strong> <?php echo h($e["tipo_servicio"]); ?></p>
                            <p><strong>Nº envío:</strong> <?php echo h($e["tracking"]) ?: "—"; ?></p>
                            <p><strong>Estado:</strong> <?php echo h($e["estado_envio"]); ?></p>
                            <p><strong>Fecha envío:</strong> <?php echo h($e["fecha_envio"]) ?: "—"; ?></p>
                            <p><strong>Entrega estimada:</strong> <?php echo h($e["fecha_entrega"]) ?: "—"; ?></p>

                            <?php if (!empty($e["url_dachser"])): ?>
                                <p>
                                    <strong>Seguimiento Dachser:</strong>
                                    <a href="<?php echo h($e["url_dachser"]); ?>" target="_blank">
                                        Ver en Dachser →
                                    </a>
                                </p>
                            <?php endif; ?>

                            <div class="lineas-box">
                                <h4>Órdenes incluidas</h4>

                                <?php
                                $envio_id = intval($e["id"]);
                                $stmt = $conexion->prepare("
                                    SELECT o.numero_orden, o.destino, o.grupo_destino, o.codigo_vendedor
                                    FROM envio_ordenes eo
                                    JOIN ordenes o ON eo.orden_id = o.id
                                    WHERE eo.envio_id = ?
                                    ORDER BY o.id ASC
                                ");
                                $stmt->bind_param("i", $envio_id);
                                $stmt->execute();
                                $ordenesEnvio = $stmt->get_result();

                                if ($ordenesEnvio->num_rows > 0):
                                    while ($o = $ordenesEnvio->fetch_assoc()):
                                ?>
                                    <div class="linea-item">
                                        <p><strong>PO:</strong> <?php echo h($o["numero_orden"]); ?></p>
                                        <p><strong>Destino:</strong> <?php echo h($o["destino"]); ?></p>
                                        <p><strong>País:</strong> <?php echo h($o["grupo_destino"]); ?></p>
                                        <p><strong>Vendor:</strong> <?php echo h($o["codigo_vendedor"]); ?></p>
                                    </div>
                                <?php
                                    endwhile;
                                else:
                                    echo "<p class='sin-lineas'>Sin órdenes asociadas.</p>";
                                endif;
                                ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </section>

    </main>
</div>

</body>
</html>