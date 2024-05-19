<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once("model/usuarios.php");
require_once("model/residente.php");
require_once("crypt.php");
require_once("model/asignado.php");
require_once("model/integracionapi.php");

class UsuariosControlador{
    static function index()
    {
        $web="usuarios";
        $usuarios = new UsuariosModelo();
        $filtro = false;
        if (isset($_GET['nombre'])) {
            $usuarios->nombre = $_GET["nombre"];
            $filtro = true;
        }
        if (isset($_GET['role'])) {
            $usuarios->role = $_GET["role"];
            $filtro = true;
        }
        if ($filtro) {
            $usuarios->FiltroMaestro();
        }
        else {
            $usuarios->Seleccionar();
            /*$response = integracionApi::llamarAPI('selecusu', null);
            echo "<script>console.log('" . $response ."');</script>";
            if ($response['success']) {
                $userData = $response['userData'];
                $usuarios->asignarDatos($userData);
            }*/
        }
        if (isset($_GET["p"])) {
            $pagnum = $_GET["p"];
        }
        else {
            $pagnum = 0;
        }
        $maxpagnum = floor((count((array) $usuarios->filas) - 1)/8);

        require_once("view/usuarios/usuarios.php");
    }

    static function Mostrar() {
        $web="usuarios";
        $usuario = new UsuariosModelo();
        $usuario->id = $_GET['id'];
        $usuario->Mostrar();
        $asignados = new AsignadoModelo();
        $asignados->usuarioid = $usuario->id;
        $asignados->SeleccionPorUsuario();

        require_once("view/usuarios/mostrarUsuario.php");
    }

    static function Insertar(){
        $usuario = new UsuariosModelo();
        $web="usuarios";
        $usuario->role = $_POST['role'];
        $usuario->nombre = $_POST['nombre'];
        $usuario->apellidos = $_POST['apellidos'];
        $usuario->correo = $_POST['email'];
        $usuario->residentes = $_POST['residentes'];  
        $usuario->salt = Crypt::generarSalt();    
        $usuario->password = Crypt::Encriptar($_POST['password'], $usuario->salt);
        if ($usuario->Insertar() == 1){
            header("location:" . URLSITE . '?c=usuarios');
        }
        else 
        {
            $_SESSION["CRUDMVC_ERROR"] = $usuario->GetError();
            header("location:" . URLSITE . "view/error.php");
        }
        /*$data = array(
            'role'=> $_POST['role'],
            'nombre'=> $_POST['nombre'], 
            'apellidos'=> $_POST['apellidos'],
            'correo' => $_POST['correo'],
            'contrasena' => $_POST['password'],
            'residentes'=> $_POST['residentes']
            // Añade más campos según sea necesario para tu solicitud
        );
        $response = integracionApi::llamarAPI('register', $data);

        /*if ($response['success'] === true) {
            $userData = $response["userData"];
        
            header("Location: " . URLSITE);
       /*} else {
                session_destroy();
                header("Location: " . URLSITE . '?c=login');
               echo '<script language="javascript">console.log('. $responseData['message'] .');</script>';
        }*/
    }

    static function modificar(){
        $data = array(
            'id' => $_GET['id'],
            'role'=> $_POST['role'],
            'nombre'=> $_POST['nombre'], 
            'apellidos'=> $_POST['apellidos'],
            'correo' => $_POST['correo'],
            'contrasena' => $_POST['password']
            //'residentes'=> $_POST['residentes']
        );
        $jsonData = json_encode($data);
        $ch = curl_init('http://192.168.0.59:8085/modify');
        //$ch = curl_init('http://jmvidareal.synology.me:8085/login');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));
        // Execute cURL request
        $response = curl_exec($ch);
        // Check for cURL errors
        if (curl_errno($ch)) {
            return false;
        } else {
            $login= true;
            $responseData = json_decode($response, true);
            if($responseData['success']){
                header("location:" . URLSITE . '?c=usuarios');  
            } else {
                session_destroy();
                header("Location: " . URLSITE);
            }
        }
        curl_close($ch);
    }

    static function editar() {
        $web="usuarios";
        $residente = new ResidenteModelo();
        $residente->Seleccionar();
        $usuarios =new UsuariosModelo();
        $usuarios->id = $_GET['id'];
        $usuarios->Seleccionar(); 
        $usuarios->correo;
        $usuario = new UsuariosModelo();
        $usuario->id = $_GET['id'];
        $usuario->SeleccionarResidentes();
        $opcion = 'EDITAR'; // Opción de insertar una tarea.
        require_once("view/usuarios/usuariosmantenimiento.php");
    }

    static function Darbaja(){
        $usuarios = new UsuariosModelo();
        $usuarios->id = $_GET['id'];
        $usuarios->DarBaja();

        header("location:" . URLSITE . '?c=usuarios');
    }
    static function Desbaja(){
        $usuarios = new UsuariosModelo();
        $usuarios->id = $_GET['id'];
        $usuarios->Desbaja();

        header("location:" . URLSITE . '?c=usuarios');
    }

    static function Nuevo(){
        $residente = new ResidenteModelo();
        $residente->Seleccionar();
        $web="usuarios";
        $usuarios = new UsuariosModelo();
        $opcion = 'NUEVO'; // Opción de insertar una tarea.
        require_once("view/usuarios/usuariosmantenimiento.php");
    }
    
    static function Inicio(){
        $web= "inicio";
        require_once("view/dashboard.php");
    }
}
?>