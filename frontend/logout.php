<?php
session_start();
$_SESSION = [];
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');
header("Location: /tfg-gestion-pedidos-amazon/login.php?logout=1");
exit();