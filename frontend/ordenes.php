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
            <a href="nueva_orden.html">Nueva orden</a>
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
            $resultado = $conexion->query("SELECT * FROM ordenes");

            while ($fila = $resultado->fetch_assoc()) {
                echo "<div class='card'>";
                echo "<h3>PO: " . $fila['numero_orden'] . "</h3>";
                echo "<p><strong>Destino:</strong> " . $fila['destino'] . "</p>";
                echo "<p><strong>Vendor:</strong> " . $fila['codigo_vendedor'] . "</p>";
                echo "<p><strong>Palets:</strong> " . $fila['numero_palets'] . "</p>";
                echo "</div>";
            }
            ?>
        </div>
    </main>
</div>

</body>
</html>