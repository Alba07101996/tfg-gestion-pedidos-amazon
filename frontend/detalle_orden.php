<?php
$conexion = new mysqli("localhost", "root", "", "tfg_pedidos_amazon");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if (!isset($_GET["id"])) {
    die("No se ha recibido el id de la orden.");
}

$orden_id = intval($_GET["id"]);

$sqlOrden = "SELECT * FROM ordenes WHERE id = $orden_id";
$resultadoOrden = $conexion->query($sqlOrden);

if ($resultadoOrden->num_rows == 0) {
    die("La orden no existe.");
}

$orden = $resultadoOrden->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de orden</title>
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
            <h1>Detalle de la orden</h1>
            <p>Información completa de la orden seleccionada</p>
        </header>

        <section class="panel-seccion">
            <h2>Datos generales</h2>
            <div class="detalle-grid">
                <div class="detalle-item"><strong>PO:</strong> <?php echo $orden["numero_orden"]; ?></div>
                <div class="detalle-item"><strong>Grupo destino:</strong> <?php echo $orden["grupo_destino"]; ?></div>
                <div class="detalle-item"><strong>Destino:</strong> <?php echo $orden["destino"]; ?></div>
                <div class="detalle-item"><strong>Vendor code:</strong> <?php echo $orden["codigo_vendedor"]; ?></div>
                <div class="detalle-item"><strong>Tipo:</strong> <?php echo $orden["tipo_codigo_vendedor"]; ?></div>
                <div class="detalle-item"><strong>Fecha orden:</strong> <?php echo $orden["fecha_orden"]; ?></div>
                <div class="detalle-item"><strong>Estado:</strong> <?php echo $orden["estado"]; ?></div>
                <div class="detalle-item"><strong>Window start:</strong> <?php echo $orden["window_start"]; ?></div>
                <div class="detalle-item"><strong>Window end:</strong> <?php echo $orden["window_end"]; ?></div>
                <div class="detalle-item"><strong>Nº palets:</strong> <?php echo $orden["numero_palets"]; ?></div>
                <div class="detalle-item detalle-item-completo"><strong>Observaciones:</strong> <?php echo $orden["observaciones"]; ?></div>
            </div>
        </section>

        <section class="panel-seccion">
            <h2>Líneas de producto</h2>
            <div class="cards">
                <?php
                $sqlLineas = "
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
                    WHERE op.orden_id = $orden_id
                    ORDER BY op.id DESC
                ";

                $resultadoLineas = $conexion->query($sqlLineas);

                if ($resultadoLineas->num_rows > 0) {
                    while ($linea = $resultadoLineas->fetch_assoc()) {
                        echo "<div class='card'>";
                        echo "<h3>{$linea['sku']} - {$linea['formato']}</h3>";
                        echo "<p><strong>Producto:</strong> {$linea['producto']}</p>";
                        echo "<p><strong>Cantidad:</strong> {$linea['cantidad']}</p>";
                        echo "<p><strong>Palets línea:</strong> {$linea['numero_palets']}</p>";
                        echo "<p><strong>Uds/palet:</strong> {$linea['unidades_por_palet']}</p>";
                        echo "<p><strong>Tipo paletizado:</strong> {$linea['tipo_paletizado']}</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='sin-lineas'>La orden no tiene líneas de producto.</p>";
                }
                ?>
            </div>
        </section>

        <section class="panel-seccion">
            <h2>Palets de la orden</h2>
            <div class="cards">
                <?php
                $sqlPalets = "
                    SELECT DISTINCT p.id, p.codigo_palet, p.tipo_palet, p.destino, p.estado, p.observaciones
                    FROM palets p
                    JOIN palet_lineas pl ON p.id = pl.palet_id
                    WHERE pl.orden_id = $orden_id
                    ORDER BY p.id DESC
                ";

                $resultadoPalets = $conexion->query($sqlPalets);

                if ($resultadoPalets->num_rows > 0) {
                    while ($palet = $resultadoPalets->fetch_assoc()) {
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
                                pr.sku,
                                pr.nombre AS producto,
                                f.formato,
                                pl.cantidad
                            FROM palet_lineas pl
                            JOIN formatos_producto f ON pl.formato_id = f.id
                            JOIN productos pr ON f.producto_sku = pr.sku
                            WHERE pl.palet_id = $palet_id
                              AND pl.orden_id = $orden_id
                        ";

                        $resultadoContenido = $conexion->query($sqlContenido);

                        if ($resultadoContenido->num_rows > 0) {
                            while ($contenido = $resultadoContenido->fetch_assoc()) {
                                echo "<div class='linea-item'>";
                                echo "<p><strong>SKU:</strong> {$contenido['sku']}</p>";
                                echo "<p><strong>Producto:</strong> {$contenido['producto']}</p>";
                                echo "<p><strong>Formato:</strong> {$contenido['formato']}</p>";
                                echo "<p><strong>Cantidad:</strong> {$contenido['cantidad']}</p>";
                                echo "</div>";
                            }
                        } else {
                            echo "<p class='sin-lineas'>Sin contenido asociado.</p>";
                        }

                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='sin-lineas'>La orden no tiene palets asociados.</p>";
                }
                ?>
            </div>
        </section>

        <section class="panel-seccion">
            <h2>ASN relacionados</h2>
            <div class="cards">
                <?php
                $sqlAsn = "
                    SELECT a.numero_asn, a.codigo_vendedor, a.destino, a.fecha_asn, a.estado
                    FROM asn_ordenes ao
                    JOIN asns a ON ao.asn_id = a.id
                    WHERE ao.orden_id = $orden_id
                    ORDER BY a.id DESC
                ";

                $resultadoAsn = $conexion->query($sqlAsn);

                if ($resultadoAsn->num_rows > 0) {
                    while ($asn = $resultadoAsn->fetch_assoc()) {
                        echo "<div class='card'>";
                        echo "<h3>ASN: {$asn['numero_asn']}</h3>";
                        echo "<p><strong>Vendor:</strong> {$asn['codigo_vendedor']}</p>";
                        echo "<p><strong>Destino:</strong> {$asn['destino']}</p>";
                        echo "<p><strong>Fecha ASN:</strong> {$asn['fecha_asn']}</p>";
                        echo "<p><strong>Estado:</strong> {$asn['estado']}</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='sin-lineas'>La orden no tiene ASN asociados.</p>";
                }
                ?>
            </div>
        </section>

        <section class="panel-seccion">
            <h2>Facturas relacionadas</h2>
            <div class="cards">
                <?php
                $sqlFacturas = "
                    SELECT f.numero_factura, f.destino, f.fecha_factura, f.importe_total, f.observaciones
                    FROM factura_ordenes fo
                    JOIN facturas f ON fo.factura_id = f.id
                    WHERE fo.orden_id = $orden_id
                    ORDER BY f.id DESC
                ";

                $resultadoFacturas = $conexion->query($sqlFacturas);

                if ($resultadoFacturas->num_rows > 0) {
                    while ($factura = $resultadoFacturas->fetch_assoc()) {
                        echo "<div class='card'>";
                        echo "<h3>Factura: {$factura['numero_factura']}</h3>";
                        echo "<p><strong>Destino:</strong> {$factura['destino']}</p>";
                        echo "<p><strong>Fecha:</strong> {$factura['fecha_factura']}</p>";
                        echo "<p><strong>Importe:</strong> {$factura['importe_total']}</p>";
                        echo "<p><strong>Observaciones:</strong> {$factura['observaciones']}</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='sin-lineas'>La orden no tiene facturas asociadas.</p>";
                }
                ?>
            </div>
        </section>

        <section class="panel-seccion">
            <h2>Envíos relacionados</h2>
            <div class="cards">
                <?php
                $sqlEnvios = "
                    SELECT transportista, tracking, estado_envio, fecha_envio, fecha_entrega
                    FROM envios
                    WHERE orden_id = $orden_id
                    ORDER BY id DESC
                ";

                $resultadoEnvios = $conexion->query($sqlEnvios);

                if ($resultadoEnvios->num_rows > 0) {
                    while ($envio = $resultadoEnvios->fetch_assoc()) {
                        echo "<div class='card'>";
                        echo "<h3>{$envio['transportista']}</h3>";
                        echo "<p><strong>Tracking:</strong> {$envio['tracking']}</p>";
                        echo "<p><strong>Estado:</strong> {$envio['estado_envio']}</p>";
                        echo "<p><strong>Fecha envío:</strong> {$envio['fecha_envio']}</p>";
                        echo "<p><strong>Fecha entrega:</strong> {$envio['fecha_entrega']}</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='sin-lineas'>La orden no tiene envíos asociados.</p>";
                }
                ?>
            </div>
        </section>

    </main>
</div>

</body>
</html>

