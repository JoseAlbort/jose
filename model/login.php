<?php

if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once('bd.php');
require_once('controller/crypt.php');

class LoginModelo extends BD {
    public $email;
    public $password;
    public $id;

    public function LogIn() {

        $sql = "SELECT * FROM users WHERE email='$this->email'";
        
        $this->filas = $this->_consultar($sql);
        if ($this->filas != null) {
            if (Crypt::Encriptar($this->password)/* + $this->filas[0]->salt*/ === $this->filas[0]->password) {
                $_SESSION['email'] = $this->filas[0]->email;
                $_SESSION['id'] = $this->filas[0]->id;
                //$_SESSION['role'] = $this->filas[0]->role;
                $_SESSION['loggedIn'] = true;
                $_SESSION['loggedstart'] = time();

                return true;
            }
        


                
        }
        return false;
    }
    public function LogInQR() {
        $sql = "SELECT * FROM usuario WHERE id=$this->id AND baja=0";
        $this->filas = $this->_consultar($sql);
        if ($this->filas != null) {
                $_SESSION['id'] = $this->filas[0]->id;
                $_SESSION['correo'] = $this->filas[0]->correo;
                $_SESSION['role'] = $this->filas[0]->role;
                $_SESSION['loggedIn'] = true;

                $_SESSION['loggedstart'] = time();

                return true;
        }
        return false;
    }
    public function codigoQR() {
        $cid = $_SESSION['id'];
        $sql = "SELECT * FROM usuario WHERE id=$cid AND codeqr=$this->codeqr AND baja=0";
        $this->filas = $this->_consultar($sql);
        if ($this->filas != null) {
            if ($this->codeqr === $this->filas[0]->codeqr) {
                return true;
            }
        }
        return false;
    }

}
?>