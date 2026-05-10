<?php
require_once 'auth.php';
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

if (!isset($_GET["id"])) {
    die("No se ha recibido el id de la orden.");
}

$orden_id = intval($_GET["id"]);

$stmt = $conexion->prepare("SELECT * FROM ordenes WHERE id = ?");
$stmt->bind_param("i", $orden_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    die("La orden no existe.");
}

$orden = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de orden</title>
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
            <h1>Detalle de la orden</h1>
            <p>Información completa de la orden seleccionada</p>
        </header>

        <!-- ================================================ -->
        <!-- DATOS GENERALES                                   -->
        <!-- ================================================ -->
        <section class="panel-seccion">
            <h2>Datos generales</h2>
            <div class="detalle-grid">
                <div class="detalle-item"><strong>PO:</strong> <?php echo h($orden["numero_orden"]); ?></div>
                <div class="detalle-item"><strong>País:</strong> <?php echo h($orden["grupo_destino"]); ?></div>
                <div class="detalle-item"><strong>Destino:</strong> <?php echo h($orden["destino"]); ?></div>
                <div class="detalle-item"><strong>Vendor code:</strong> <?php echo h($orden["codigo_vendedor"]); ?></div>
                <div class="detalle-item"><strong>Tipo:</strong> <?php echo h($orden["tipo_codigo_vendedor"]); ?></div>
                <div class="detalle-item"><strong>Fecha orden:</strong> <?php echo h($orden["fecha_orden"]); ?></div>
                <div class="detalle-item"><strong>Estado:</strong> <?php echo h($orden["estado"]); ?></div>
                <div class="detalle-item"><strong>Window start:</strong> <?php echo h($orden["window_start"]); ?></div>
                <div class="detalle-item"><strong>Window end:</strong> <?php echo h($orden["window_end"]); ?></div>
                <div class="detalle-item"><strong>Nº palets:</strong> <?php echo h($orden["numero_palets"]); ?></div>
                <div class="detalle-item detalle-item-completo"><strong>Observaciones:</strong> <?php echo h($orden["observaciones"]); ?></div>
            </div>
        </section>

        <!-- ================================================ -->
        <!-- LÍNEAS DE PRODUCTO                                -->
        <!-- ================================================ -->
        <section class="panel-seccion">
            <h2>Líneas de producto</h2>
            <div class="cards">
                <?php
                $stmtL = $conexion->prepare("
                    SELECT 
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
                    WHERE op.orden_id = ?
                    ORDER BY op.id DESC
                ");
                $stmtL->bind_param("i", $orden_id);
                $stmtL->execute();
                $resLineas = $stmtL->get_result();

                if ($resLineas->num_rows > 0):
                    while ($linea = $resLineas->fetch_assoc()):
                ?>
                    <div class="card">
                        <h3><?php echo h($linea['sku']); ?> — <?php echo h($linea['formato']); ?></h3>
                        <p><strong>Producto:</strong> <?php echo h($linea['producto']); ?></p>
                        <p><strong>Cantidad:</strong> <?php echo h($linea['cantidad']); ?></p>
                        <p><strong>Palets línea:</strong> <?php echo h($linea['numero_palets']); ?></p>
                        <p><strong>Uds/palet:</strong> <?php echo h($linea['unidades_por_palet']); ?></p>
                        <p><strong>Tipo paletizado:</strong> <?php echo h($linea['tipo_paletizado']); ?></p>
                    </div>
                <?php
                    endwhile;
                else:
                    echo "<p class='sin-lineas'>La orden no tiene líneas de producto.</p>";
                endif;
                ?>
            </div>
        </section>

        <!-- ================================================ -->
        <!-- PALETS                                            -->
        <!-- ================================================ -->
        <section class="panel-seccion">
            <h2>Palets de la orden</h2>
            <div class="cards">
                <?php
                $stmtP = $conexion->prepare("
                    SELECT DISTINCT p.id, p.codigo_palet, p.tipo_palet, p.destino, p.estado, p.observaciones
                    FROM palets p
                    JOIN palet_lineas pl ON p.id = pl.palet_id
                    WHERE pl.orden_id = ?
                    ORDER BY p.id DESC
                ");
                $stmtP->bind_param("i", $orden_id);
                $stmtP->execute();
                $resPalets = $stmtP->get_result();

                if ($resPalets->num_rows > 0):
                    while ($palet = $resPalets->fetch_assoc()):
                        $palet_id = intval($palet["id"]);
                ?>
                    <div class="card">
                        <h3><?php echo h($palet['codigo_palet']); ?></h3>
                        <p><strong>Tipo:</strong> <?php echo h($palet['tipo_palet']); ?></p>
                        <p><strong>Destino:</strong> <?php echo h($palet['destino']); ?></p>
                        <p><strong>Estado:</strong> <?php echo h($palet['estado']); ?></p>

                        <div class="lineas-box">
                            <h4>Contenido del palet</h4>
                            <?php
                            $stmtC = $conexion->prepare("
                                SELECT 
                                    pr.sku,
                                    pr.nombre AS producto,
                                    f.formato,
                                    pl.cantidad
                                FROM palet_lineas pl
                                JOIN formatos_producto f ON pl.formato_id = f.id
                                JOIN productos pr ON f.producto_sku = pr.sku
                                WHERE pl.palet_id = ? AND pl.orden_id = ?
                            ");
                            $stmtC->bind_param("ii", $palet_id, $orden_id);
                            $stmtC->execute();
                            $resContenido = $stmtC->get_result();

                            if ($resContenido->num_rows > 0):
                                while ($contenido = $resContenido->fetch_assoc()):
                            ?>
                                <div class="linea-item">
                                    <p><strong>SKU:</strong> <?php echo h($contenido['sku']); ?></p>
                                    <p><strong>Producto:</strong> <?php echo h($contenido['producto']); ?></p>
                                    <p><strong>Formato:</strong> <?php echo h($contenido['formato']); ?></p>
                                    <p><strong>Cantidad:</strong> <?php echo h($contenido['cantidad']); ?></p>
                                </div>
                            <?php
                                endwhile;
                            else:
                                echo "<p class='sin-lineas'>Sin contenido asociado.</p>";
                            endif;
                            ?>
                        </div>
                    </div>
                <?php
                    endwhile;
                else:
                    echo "<p class='sin-lineas'>La orden no tiene palets asociados.</p>";
                endif;
                ?>
            </div>
        </section>

        <!-- ================================================ -->
        <!-- ASN                                               -->
        <!-- ================================================ -->
        <section class="panel-seccion">
            <h2>ASN relacionados</h2>
            <div class="cards">
                <?php
                $stmtA = $conexion->prepare("
                    SELECT a.numero_asn, a.fecha_asn, a.estado, a.observaciones
                    FROM asn_ordenes ao
                    JOIN asns a ON ao.asn_id = a.id
                    WHERE ao.orden_id = ?
                    ORDER BY a.id DESC
                ");
                $stmtA->bind_param("i", $orden_id);
                $stmtA->execute();
                $resAsn = $stmtA->get_result();

                if ($resAsn->num_rows > 0):
                    while ($asn = $resAsn->fetch_assoc()):
                ?>
                    <div class="card">
                        <h3>ASN: <?php echo h($asn['numero_asn']); ?></h3>
                        <p><strong>Fecha ASN:</strong> <?php echo h($asn['fecha_asn']); ?></p>
                        <p><strong>Estado:</strong> <?php echo h($asn['estado']); ?></p>
                        <p><strong>Observaciones:</strong> <?php echo h($asn['observaciones']); ?></p>
                    </div>
                <?php
                    endwhile;
                else:
                    echo "<p class='sin-lineas'>La orden no tiene ASN asociados.</p>";
                endif;
                ?>
            </div>
        </section>

        <!-- ================================================ -->
        <!-- FACTURAS                                          -->
        <!-- ================================================ -->
        <section class="panel-seccion">
            <h2>Facturas relacionadas</h2>
            <div class="cards">
                <?php
                $stmtF = $conexion->prepare("
                    SELECT f.numero_factura, f.destino, f.fecha_factura, f.importe_total, f.observaciones
                    FROM factura_ordenes fo
                    JOIN facturas f ON fo.factura_id = f.id
                    WHERE fo.orden_id = ?
                    ORDER BY f.id DESC
                ");
                $stmtF->bind_param("i", $orden_id);
                $stmtF->execute();
                $resFacturas = $stmtF->get_result();

                if ($resFacturas->num_rows > 0):
                    while ($factura = $resFacturas->fetch_assoc()):
                ?>
                    <div class="card">
                        <h3>Factura: <?php echo h($factura['numero_factura']); ?></h3>
                        <p><strong>Destino:</strong> <?php echo h($factura['destino']); ?></p>
                        <p><strong>Fecha:</strong> <?php echo h($factura['fecha_factura']); ?></p>
                        <p><strong>Importe:</strong> <?php echo h(number_format((float)$factura['importe_total'], 2, ',', '.')); ?> €</p>
                        <p><strong>Observaciones:</strong> <?php echo h($factura['observaciones']); ?></p>
                    </div>
                <?php
                    endwhile;
                else:
                    echo "<p class='sin-lineas'>La orden no tiene facturas asociadas.</p>";
                endif;
                ?>
            </div>
        </section>

        <!-- ================================================ -->
        <!-- ENVÍOS                                            -->
        <!-- ================================================ -->
        <section class="panel-seccion">
            <h2>Envíos relacionados</h2>
            <div class="cards">
                <?php
                $stmtE = $conexion->prepare("
                    SELECT e.id, e.transportista, e.tipo_servicio, e.tracking,
                           e.estado_envio, e.fecha_envio, e.fecha_entrega, e.url_dachser
                    FROM envio_ordenes eo
                    JOIN envios e ON eo.envio_id = e.id
                    WHERE eo.orden_id = ?
                    ORDER BY e.id DESC
                ");
                $stmtE->bind_param("i", $orden_id);
                $stmtE->execute();
                $resEnvios = $stmtE->get_result();

                if ($resEnvios->num_rows > 0):
                    while ($envio = $resEnvios->fetch_assoc()):
                ?>
                    <div class="card">
                        <h3><?php echo h($envio['transportista']); ?></h3>
                        <p><strong>Servicio:</strong> <?php echo h($envio['tipo_servicio']); ?></p>
                        <p><strong>Nº envío:</strong> <?php echo h($envio['tracking']) ?: '—'; ?></p>
                        <p><strong>Estado:</strong> <?php echo h($envio['estado_envio']); ?></p>
                        <p><strong>Fecha envío:</strong> <?php echo h($envio['fecha_envio']) ?: '—'; ?></p>
                        <p><strong>Fecha entrega:</strong> <?php echo h($envio['fecha_entrega']) ?: '—'; ?></p>
                        <?php if (!empty($envio['url_dachser'])): ?>
                            <p><strong>Seguimiento:</strong>
                                <a href="<?php echo h($envio['url_dachser']); ?>" target="_blank" rel="noopener noreferrer">
                                    Ver en Dachser →
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php
                    endwhile;
                else:
                    echo "<p class='sin-lineas'>La orden no tiene envíos asociados.</p>";
                endif;
                ?>
            </div>
        </section>

    </main>
</div>

</body>
</html>