<?php

require_once('controller/login.php');

if (!LoginControlador::isLogged()) {
    header('location:' . URLSITE . 'view/login.php');
    die();
}
require_once("layout/header.php");

?> 
    <link rel="stylesheet" href="view/estilos/dashboard.css">
    <h1>ResiDev Tareas</h1>
    <a href="<?php echo URLSITE . '?c=residente';?>">
        <button type="button">Residente</button>
    </a>
    <a href="<?php echo URLSITE . '?c=usuarios';?>">
        <button type="button">Tabajadores</button>
    </a>
    <a href="<?php echo URLSITE . '?c=tareas';?>">
        <button type="button">Tareas</button>
    </a>
    <a href="<?php echo URLSITE . '?c=asignar';?>">
        <button type="button">Historial</button>
    </a>
    <a href="<?php echo URLSITE . '?c=asignar&m=autoasig';?>">
        <button type="button">Auto asig.</button>
    </a>

    <script src="./view/js/dashboard.js"></script>
<?php
require_once("layout/footer.php");

?>