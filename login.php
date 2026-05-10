<?php
session_start();

// Si ya está logueado, redirigir al inicio
if (isset($_SESSION["usuario_id"])) {
    header("Location: frontend/index.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = new mysqli("localhost", "root", "", "tfg_pedidos_amazon");
    $conexion->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario  = trim($_POST["usuario"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($usuario === "" || $password === "") {
        $error = "Introduce usuario y contraseña.";
    } else {
        $stmt = $conexion->prepare("SELECT id, nombre, usuario, password FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();

            if (password_verify($password, $user["password"])) {
                $_SESSION["usuario_id"]     = $user["id"];
                $_SESSION["usuario_nombre"] = $user["nombre"];
                $_SESSION["usuario"]        = $user["usuario"];

                header("Location: frontend/index.php");
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos.";
            }
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — Logística Amazon</title>
    <link rel="stylesheet" href="/tfg-gestion-pedidos-amazon/frontend/css/styles.css">
</head>
<body class="login-body">

<div class="login-box">
    <h1>Logística Amazon</h1>
    <p>Introduce tus credenciales para acceder</p>



    <?php if (isset($_GET["logout"])): ?>
    <div class="mensaje-logout">Sesión cerrada correctamente.</div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="campo">
            <label>Usuario</label>
            <input type="text" name="usuario" autofocus autocomplete="username">
        </div>

        <div class="campo">
            <label>Contraseña</label>
            <input type="password" name="password" autocomplete="current-password">
        </div>

        <button type="submit" class="boton-login">Entrar</button>
    </form>

    <p class="login-logo">TFG — Gestión de pedidos Amazon</p>
</div>

</body>
</html>