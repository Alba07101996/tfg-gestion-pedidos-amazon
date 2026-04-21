<?php
$conexion = new mysqli("localhost", "root", "", "tfg_pedidos_amazon");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Órdenes</title>
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
            <h1>Órdenes</h1>
            <p>Listado de órdenes registradas</p>
        </header>

        <div class="cards">

            <?php
            $sqlOrdenes = "SELECT * FROM ordenes ORDER BY id DESC";
            $resultadoOrdenes = $conexion->query($sqlOrdenes);

            while ($orden = $resultadoOrdenes->fetch_assoc()) {

                echo "<div class='card'>";

                echo "<h3>PO: " . $orden['numero_orden'] . "</h3>";
                echo "<p><strong>Destino:</strong> " . $orden['destino'] . "</p>";
                echo "<p><strong>Vendor:</strong> " . $orden['codigo_vendedor'] . "</p>";
                echo "<p><strong>Palets:</strong> " . $orden['numero_palets'] . "</p>";
                echo "<p><strong>Estado:</strong> " . $orden['estado'] . "</p>";

                echo "<div class='lineas-box'>";
                echo "<h4>Líneas de producto</h4>";

                $idOrden = $orden['id'];

                $sqlLineas = "
                    SELECT 
                        p.sku,
                        p.nombre AS producto,
                        f.formato,
                        op.cantidad
                    FROM ordenes_productos op
                    LEFT JOIN formatos_producto f ON op.formato_id = f.id
                    LEFT JOIN productos p ON f.producto_sku = p.sku
                    WHERE op.orden_id = $idOrden
                ";

                $resultadoLineas = $conexion->query($sqlLineas);

                if ($resultadoLineas->num_rows > 0) {
                    while ($linea = $resultadoLineas->fetch_assoc()) {
                        echo "<div class='linea-item'>";
                        echo "<p><strong>SKU:</strong> " . $linea['sku'] . "</p>";
                        echo "<p><strong>Producto:</strong> " . $linea['producto'] . "</p>";
                        echo "<p><strong>Formato:</strong> " . $linea['formato'] . "</p>";
                        echo "<p><strong>Unidades:</strong> " . $linea['cantidad'] . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='sin-lineas'>Sin productos todavía</p>";
                }

                echo "</div>";

                // BOTONES
                echo "<div class='acciones-card'>";
                
                echo "<a href='agregar_producto_orden.php?id=" . $orden['id'] . "' class='boton-card boton-ver'>Añadir productos</a>";

                echo "<a href='detalle_orden.php?id=" . $orden['id'] . "' class='boton-card boton-ver'>Ver</a>";

                echo "<a href='editar_orden.php?id=" . $orden['id'] . "' class='boton-card boton-editar'>Editar</a>";

                echo "<a href='eliminar_orden.php?id=" . $orden['id'] . "' class='boton-card boton-eliminar' onclick='return confirm(\"¿Eliminar orden?\")'>Eliminar</a>";

                echo "</div>";

                echo "</div>";
            }
            ?>

        </div>
    </main>
</div>

</body>
</html>