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

// Obtener orden
$sqlOrden = "SELECT * FROM ordenes WHERE id = $orden_id";
$resultadoOrden = $conexion->query($sqlOrden);

if ($resultadoOrden->num_rows == 0) {
    die("La orden no existe.");
}

$orden = $resultadoOrden->fetch_assoc();

// CREAR PALET
if (isset($_POST["crear_palet"])) {
    $codigo_palet = trim($_POST["codigo_palet"]);
    $tipo_palet = trim($_POST["tipo_palet"]);

    $sql = "INSERT INTO palets (codigo_palet, tipo_palet, destino, grupo_destino, estado)
            VALUES ('$codigo_palet', '$tipo_palet', '{$orden['destino']}', '{$orden['grupo_destino']}', 'pendiente')";

    if ($conexion->query($sql)) {
        $mensaje = "Palet creado correctamente";
    } else {
        if ($conexion->errno == 1062) {
            $mensaje = "⚠️ Ese código de palet ya existe";
        } else {
            die("Error SQL: " . $conexion->error);
        }
    }
}

// AÑADIR LÍNEA
if (isset($_POST["anadir_linea"])) {
    $palet_id = intval($_POST["palet_id"]);
    $formato_id = intval($_POST["formato_id"]);
    $cantidad = intval($_POST["cantidad"]);

    $sql = "INSERT INTO palet_lineas (palet_id, orden_id, formato_id, cantidad)
            VALUES ($palet_id, $orden_id, $formato_id, $cantidad)";

    if ($conexion->query($sql)) {
        $mensaje = "Línea añadida correctamente";
    } else {
        die("Error SQL: " . $conexion->error);
    }
}

// FORMATOS
$formatos = $conexion->query("
    SELECT f.id, p.sku, p.nombre, f.formato
    FROM formatos_producto f
    JOIN productos p ON f.producto_sku = p.sku
");

// PALETS (SIN DUPLICADOS)
$palets = $conexion->query("
    SELECT MIN(id) as id, codigo_palet, tipo_palet
    FROM palets
    WHERE destino = '{$orden['destino']}'
    GROUP BY codigo_palet, tipo_palet
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Palets</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="app">
    <aside class="sidebar">
        <h2>Logística Amazon</h2>
        <nav>
            <a href="index.html">Inicio</a>
            <a href="ordenes.php" class="activo">Órdenes</a>
        </nav>
    </aside>

    <main class="contenido">

        <h1>Palets de la orden</h1>
        <p><strong><?php echo $orden["numero_orden"]; ?></strong></p>

        <?php if ($mensaje != "") echo "<p>$mensaje</p>"; ?>

        <!-- CREAR PALET -->
        <h2>Nuevo palet</h2>
        <form method="POST">
            <input type="hidden" name="crear_palet">
            <input type="text" name="codigo_palet" placeholder="Código palet" required>
            <select name="tipo_palet">
                <option value="normal">normal</option>
                <option value="ordering">ordering</option>
            </select>
            <button>Crear</button>
        </form>

        <!-- AÑADIR PRODUCTO -->
        <h2>Añadir producto a palet</h2>
        <form method="POST">
            <input type="hidden" name="anadir_linea">

            <select name="palet_id" required>
                <option>Selecciona palet</option>
                <?php while($p = $palets->fetch_assoc()) { ?>
                    <option value="<?php echo $p["id"]; ?>">
                        <?php echo $p["codigo_palet"] . " (" . $p["tipo_palet"] . ")"; ?>
                    </option>
                <?php } ?>
            </select>

            <select name="formato_id" required>
                <option>Producto</option>
                <?php while($f = $formatos->fetch_assoc()) { ?>
                    <option value="<?php echo $f["id"]; ?>">
                        <?php echo $f["sku"] . " - " . $f["formato"]; ?>
                    </option>
                <?php } ?>
            </select>

            <input type="number" name="cantidad" placeholder="Cantidad" required>
            <button>Añadir</button>
        </form>

    </main>
</div>

</body>
</html>