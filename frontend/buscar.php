<?php
$conexion = new mysqli("localhost", "root", "", "tfg_pedidos_amazon");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$busqueda = "";
$resultado = null;

if (isset($_GET["q"])) {
    $busqueda = $conexion->real_escape_string($_GET["q"]);

    $sql = "
        SELECT DISTINCT o.*
        FROM ordenes o

        LEFT JOIN factura_ordenes fo ON o.id = fo.orden_id
        LEFT JOIN facturas f ON fo.factura_id = f.id

        LEFT JOIN envios e ON o.id = e.orden_id

        WHERE 
            o.numero_orden LIKE '%$busqueda%'
            OR o.destino LIKE '%$busqueda%'
            OR o.codigo_vendedor LIKE '%$busqueda%'
            OR f.numero_factura LIKE '%$busqueda%'
            OR e.tracking LIKE '%$busqueda%'

        ORDER BY o.id DESC
    ";

    $resultado = $conexion->query($sql);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar orden</title>
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
            <a href="buscar.php" class="activo">Buscar</a>
            <a href="asn.php">ASN</a>
            <a href="facturas.php">Facturas</a>
            <a href="envios.php">Envíos</a>
        </nav>
    </aside>

    <main class="contenido">
        <h1>Buscar orden</h1>

        <form method="GET" class="formulario-busqueda">
            <input type="text" name="q" placeholder="PO, destino, vendor, factura o tracking..." value="<?php echo htmlspecialchars($busqueda); ?>">
            <button class="boton-guardar">Buscar</button>
        </form>

        <div class="cards">

        <?php if ($resultado !== null): ?>

            <?php if ($resultado->num_rows > 0): ?>
                <?php while ($orden = $resultado->fetch_assoc()): ?>
                    
                    <div class="card">
                        <h3>PO: <?php echo $orden["numero_orden"]; ?></h3>
                        <p><strong>Destino:</strong> <?php echo $orden["destino"]; ?></p>
                        <p><strong>Vendor:</strong> <?php echo $orden["codigo_vendedor"]; ?></p>
                        <p><strong>Estado:</strong> <?php echo $orden["estado"]; ?></p>

                        <div class="acciones-card">
                            <a href="detalle_orden.php?id=<?php echo $orden['id']; ?>" class="boton-card boton-ver">Ver</a>
                            <a href="palets_orden.php?id=<?php echo $orden['id']; ?>" class="boton-card boton-ver">Palets</a>
                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <p class="sin-lineas">No se han encontrado resultados</p>
            <?php endif; ?>

        <?php endif; ?>

        </div>

    </main>
</div>

</body>
</html>