    <?php

if (session_status() === PHP_SESSION_NONE)
    session_start();


class AppControlador
{
    static function index(){
        /*if ($_SESSION["role"] == "Administrador") {
            $web = "inicio";
            require_once("view/dashboard.php");
            return;
        }*/
        
        require_once("view/dashboard.php");
    }
}
?>