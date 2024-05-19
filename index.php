<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once("config.php");
require_once("controller/app.php");
require_once("controller/login.php");


if(isset($_GET['c']) || isset($_POST["controlador"])) :
    $controlador = (isset($_GET['c']) ? $_GET['c'] : $_POST["controlador"]);

    $metodo = '';
    if(isset($_GET['m']) || isset($_POST["metodo"])):
        $metodo = (isset($_GET['m']) ? $_GET['m'] : $_POST["metodo"]);
    endif;
    
    switch ($controlador) :
        case 'login' :
            if (method_exists('LoginControlador', $metodo)):
                LoginControlador::{$metodo}();
            else:
                LoginControlador::index();
            endif;
            break;
    endswitch;
else :
    AppControlador::index();
endif;
?>