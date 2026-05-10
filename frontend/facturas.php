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

$prefijos = [
    "España"      => "AMZ-",
    "Francia"     => "AMZF-",
    "Italia"      => "AMZI-",
    "Alemania"    => "AMZA-",
    "Reino Unido" => "AMZUK-"
];

/* =========================================================
   CREAR FACTURA
========================================================= */
if (isset($_POST["crear_factura"])) {

    $orden_id        = intval($_POST["orden_id"]);
    $prefijo_factura = trim($_POST["prefijo_factura"]);
    $numero_manual   = trim($_POST["numero_factura"]);
    $numero_factura  = $prefijo_factura . $numero_manual;
    $fecha_factura   = $_POST["fecha_factura"];
    $importe_total   = $_POST["importe_total"];
    $observaciones   = $_POST["observaciones"];

    try {
        $stmt = $conexion->prepare("SELECT * FROM ordenes WHERE id = ?");
        $stmt->bind_param("i", $orden_id);
        $stmt->execute();
        $orden = $stmt->get_result()->fetch_assoc();

        if (!$orden) throw new Exception("La orden seleccionada no existe.");

        $destino       = $orden["destino"];
        $grupo_destino = $orden["grupo_destino"];

        $stmtFactura = $conexion->prepare("
            INSERT INTO facturas (numero_factura, destino, grupo_destino, fecha_factura, importe_total, observaciones)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmtFactura->bind_param("ssssds", $numero_factura, $destino, $grupo_destino, $fecha_factura, $importe_total, $observaciones);
        $stmtFactura->execute();
        $factura_id = $conexion->insert_id;

        $stmtRel = $conexion->prepare("INSERT INTO factura_ordenes (factura_id, orden_id) VALUES (?, ?)");
        $stmtRel->bind_param("ii", $factura_id, $orden_id);
        $stmtRel->execute();

        $mensaje = "Factura {$numero_factura} creada y orden asociada correctamente.";

    } catch (mysqli_sql_exception $e) {
        $mensaje_tipo = "error";
        $mensaje = $e->getCode() == 1062
            ? "Error: ese número de factura ya existe, o la orden ya tiene factura asignada."
            : "Error SQL: " . $e->getMessage();
    } catch (Exception $e) {
        $mensaje_tipo = "error";
        $mensaje = $e->getMessage();
    }
}

/* =========================================================
   AÑADIR ORDEN A FACTURA EXISTENTE
========================================================= */
if (isset($_POST["añadir_orden"])) {

    $factura_id = intval($_POST["factura_id"]);
    $orden_id   = intval($_POST["orden_id_añadir"]);

    try {
        $stmtF = $conexion->prepare("SELECT * FROM facturas WHERE id = ?");
        $stmtF->bind_param("i", $factura_id);
        $stmtF->execute();
        $factura = $stmtF->get_result()->fetch_assoc();
        if (!$factura) throw new Exception("La factura no existe.");

        $stmtO = $conexion->prepare("SELECT * FROM ordenes WHERE id = ?");
        $stmtO->bind_param("i", $orden_id);
        $stmtO->execute();
        $orden = $stmtO->get_result()->fetch_assoc();
        if (!$orden) throw new Exception("La orden no existe.");

        if ($orden["destino"] !== $factura["destino"]) {
            throw new Exception(
                "Destino incompatible: la orden es de '{$orden['destino']}' y la factura es de '{$factura['destino']}'."
            );
        }

        $stmtRel = $conexion->prepare("INSERT INTO factura_ordenes (factura_id, orden_id) VALUES (?, ?)");
        $stmtRel->bind_param("ii", $factura_id, $orden_id);
        $stmtRel->execute();

        $mensaje = "Orden añadida correctamente a la factura.";

    } catch (mysqli_sql_exception $e) {
        $mensaje_tipo = "error";
        $mensaje = $e->getCode() == 1062
            ? "Error: esa orden ya tiene una factura asignada."
            : "Error SQL: " . $e->getMessage();
    } catch (Exception $e) {
        $mensaje_tipo = "error";
        $mensaje = $e->getMessage();
    }
}

/* =========================================================
   DATOS
========================================================= */
$ordenes_disponibles = $conexion->query("
    SELECT o.*
    FROM ordenes o
    LEFT JOIN factura_ordenes fo ON o.id = fo.orden_id
    WHERE fo.orden_id IS NULL
    ORDER BY o.id DESC
");

$facturas = $conexion->query("SELECT * FROM facturas ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Facturas</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="app">

    <aside class="sidebar">
        <h2>Logística Amazon</h2>
        <nav>
            <a href="index.php">Inicio</a>
            <a href="ordenes.php">Órdenes</a>
            <a href="nueva_orden.php">Nueva orden</a>
            <a href="buscar.php">Buscar orden</a>
            <a href="asn.php">ASN</a>
            <a href="facturas.php" class="activo">Facturas</a>
            <a href="envios.php">Envíos</a>
        </nav>
    </aside>

    <main class="contenido">

        <header class="cabecera">
            <h1>Facturas</h1>
            <p>Gestión y relación de facturas con órdenes</p>
        </header>

        <?php if ($mensaje != ""): ?>
            <div class="mensaje <?php echo $mensaje_tipo === 'error' ? 'mensaje-error' : ''; ?>">
                <?php echo h($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- ===================================================== -->
        <!-- CREAR FACTURA                                          -->
        <!-- ===================================================== -->
        <section class="panel-seccion">
            <h2>Crear factura</h2>

            <form class="formulario" method="POST">
                <input type="hidden" name="crear_factura" value="1">
                <input type="hidden" name="prefijo_factura" id="prefijo_factura_input" value="">

                <div class="campo">
                    <label>Orden</label>
                    <select name="orden_id" id="orden_id" required onchange="actualizarPrefijo()">
                        <option value="">Selecciona una orden</option>

                        <?php
                        $ordenes_array = [];
                        while ($o = $ordenes_disponibles->fetch_assoc()):
                            $ordenes_array[] = $o;

                            // ✅ Se usa grupo_destino para buscar el prefijo correcto
                            $grupo_destino = $o["grupo_destino"] ?? "";
                            $prefijo       = $prefijos[$grupo_destino] ?? "";
                        ?>
                            <option
                                value="<?php echo h($o["id"]); ?>"
                                data-prefijo="<?php echo h($prefijo); ?>"
                                data-grupo="<?php echo h($grupo_destino); ?>"
                            >
                                <?php echo h($o["numero_orden"] . " — " . $grupo_destino . " — " . $o["destino"]); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="campo">
                    <label>Número de factura</label>
                    <div class="factura-input">
                        <span id="prefijo_visible" class="prefijo-badge">—</span>
                        <input
                            type="text"
                            name="numero_factura"
                            id="numero_factura"
                            placeholder="Selecciona una orden primero"
                            required
                            disabled
                        >
                    </div>
                    <small id="prefijo_info" style="color:#888; margin-top:4px; display:block;">
                        El prefijo se asigna automáticamente según el país de destino.
                    </small>
                </div>

                <div class="campo">
                    <label>Fecha factura</label>
                    <input type="date" name="fecha_factura">
                </div>

                <div class="campo">
                    <label>Importe total (€)</label>
                    <input type="number" step="0.01" name="importe_total" value="0.00">
                </div>

                <div class="campo campo-completo">
                    <label>Observaciones</label>
                    <textarea name="observaciones"></textarea>
                </div>

                <div class="campo-completo">
                    <button type="submit" class="boton-guardar">Crear factura</button>
                </div>
            </form>
        </section>

        <!-- ===================================================== -->
        <!-- AÑADIR ORDEN A FACTURA EXISTENTE                      -->
        <!-- ===================================================== -->
        <section class="panel-seccion">
            <h2>Añadir orden a factura existente</h2>

            <?php if (count($ordenes_array) === 0): ?>
                <p class="sin-lineas">No hay órdenes disponibles sin factura asignada.</p>
            <?php else: ?>

                <form class="formulario" method="POST">
                    <input type="hidden" name="añadir_orden" value="1">

                    <div class="campo">
                        <label>Factura</label>
                        <select name="factura_id" required>
                            <option value="">Selecciona una factura</option>
                            <?php
                            $facturas->data_seek(0);
                            while ($f = $facturas->fetch_assoc()):
                            ?>
                                <option value="<?php echo h($f["id"]); ?>">
                                    <?php echo h($f["numero_factura"] . " — " . $f["destino"]); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label>Orden</label>
                        <select name="orden_id_añadir" required>
                            <option value="">Selecciona una orden</option>
                            <?php foreach ($ordenes_array as $o): ?>
                                <option value="<?php echo h($o["id"]); ?>">
                                    <?php echo h($o["numero_orden"] . " — " . $o["grupo_destino"] . " — " . $o["destino"]); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo-completo">
                        <button type="submit" class="boton-guardar">Añadir orden</button>
                    </div>
                </form>

            <?php endif; ?>
        </section>

        <!-- ===================================================== -->
        <!-- LISTADO DE FACTURAS                                    -->
        <!-- ===================================================== -->
        <section class="panel-seccion">
            <h2>Facturas registradas</h2>

            <div class="cards">
                <?php
                $facturas->data_seek(0);
                while ($factura = $facturas->fetch_assoc()):
                    $factura_id = intval($factura["id"]);
                ?>
                    <div class="card">
                        <h3>Factura: <?php echo h($factura["numero_factura"]); ?></h3>
                        <p><strong>Destino:</strong> <?php echo h($factura["destino"]); ?></p>
                        <p><strong>Grupo destino:</strong> <?php echo h($factura["grupo_destino"]); ?></p>
                        <p><strong>Fecha:</strong> <?php echo h($factura["fecha_factura"]); ?></p>
                        <p><strong>Importe:</strong> <?php echo h(number_format((float)$factura["importe_total"], 2, ',', '.')); ?> €</p>

                        <div class="lineas-box">
                            <h4>Órdenes asociadas</h4>
                            <?php
                            $stmtOf = $conexion->prepare("
                                SELECT o.numero_orden, o.destino, o.codigo_vendedor
                                FROM factura_ordenes fo
                                JOIN ordenes o ON fo.orden_id = o.id
                                WHERE fo.factura_id = ?
                                ORDER BY o.id DESC
                            ");
                            $stmtOf->bind_param("i", $factura_id);
                            $stmtOf->execute();
                            $ordenesFactura = $stmtOf->get_result();

                            if ($ordenesFactura->num_rows > 0):
                                while ($o = $ordenesFactura->fetch_assoc()):
                            ?>
                                <div class="linea-item">
                                    <p><strong>PO:</strong> <?php echo h($o["numero_orden"]); ?></p>
                                    <p><strong>Destino:</strong> <?php echo h($o["destino"]); ?></p>
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
            </div>
        </section>

    </main>
</div>

<script>
const paises = {
    "España":      "🇪🇸 España",
    "Francia":     "🇫🇷 Francia",
    "Italia":      "🇮🇹 Italia",
    "Alemania":    "🇩🇪 Alemania",
    "Reino Unido": "🇬🇧 Reino Unido"
};

function actualizarPrefijo() {
    const select  = document.getElementById("orden_id");
    const opcion  = select.options[select.selectedIndex];
    const prefijo = opcion.getAttribute("data-prefijo") || "";
    const grupo   = opcion.getAttribute("data-grupo") || "";

    const spanPrefijo  = document.getElementById("prefijo_visible");
    const inputOculto  = document.getElementById("prefijo_factura_input");
    const inputNumero  = document.getElementById("numero_factura");
    const info         = document.getElementById("prefijo_info");

    spanPrefijo.innerText  = prefijo || "—";
    inputOculto.value      = prefijo;

    if (prefijo) {
        // Habilitar el campo de número y poner foco
        inputNumero.disabled    = false;
        inputNumero.placeholder = "001";
        inputNumero.focus();

        const nombrePais = paises[grupo] || grupo;
        info.innerHTML   = "Prefijo para <strong>" + nombrePais + "</strong>: <strong>" + prefijo + "</strong>";
        info.style.color = "#2563eb";
    } else {
        inputNumero.disabled    = true;
        inputNumero.placeholder = "Selecciona una orden primero";
        inputNumero.value       = "";
        info.innerText          = "El prefijo se asigna automáticamente según el país de destino.";
        info.style.color        = "#888";
    }
}
</script>

</body>
</html>