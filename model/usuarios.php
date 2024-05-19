<?php
require_once("bd.php");
class UsuariosModelo extends BD
{
    // Campos de la tabla.
    public $id;
    public $role;
    public $nombre;     // Nullable
    public $apellidos;  // Nullable
    public $correo;      // Nullable
    public $password;   // Nullable
    public $creado;
    public $baja;       // Nullable
    public $turno;
    public $nfc;
    public $salt;
    public $residentes;
    public $contDias;

    public $tars;
    public $groups;

    public function asignarDatos($datos) {
        foreach ($datos as $propiedad => $valor) {
            // Verificar si la propiedad existe en la clase antes de asignar
            if (property_exists($this, $propiedad)) {
                $this->$propiedad = $valor;
            }
        }
    }

    public function Insertar() {
        $sql = "INSERT INTO usuario VALUES (default, '$this->role', '$this->nombre', '$this->apellidos'" . 
        ", '$this->correo', '$this->password', default, 0, default, default, default , '$this->salt', default, '$this->residentes')";
        return $this->_ejecutar($sql);
    }

    public function Modificar() {
        $sql = "UPDATE usuario SET 
        role = '$this->role', 
        nombre = '$this->nombre', 
        apellidos = '$this->apellidos', 
        correo = '$this->correo', 
        password = '$this->password', 
        salt = '$this->salt', 
        residentes = '$this->residentes' 
        WHERE id = $this->id";
        return $this->_ejecutar($sql);
    }

    public function Borrar() {
        $sql = "DELETE FROM usuario WHERE id=$this->id";

        return $this->_ejecutar($sql);
    }

    public function Seleccionar()
    {
        $sql = 'SELECT * FROM usuario';
        
        // Si me han pasado un id, obtenemos solo el registro indicado.
        if ($this->id != 0)
            $sql .= " WHERE id=$this->id";

        $sql .= " ORDER BY nombre, apellidos ASC";
    
        $this->filas = $this->_consultar($sql);
        
        if ($this->filas == null)
            return false;
        
        if ($this->id != 0)
        {
            // Guardamos los campos en las propiedades.
            $this->role = $this->filas[0]->role;
            $this->nombre = $this->filas[0]->nombre;
            $this->apellidos = $this->filas[0]->apellidos;
            $this->correo = $this->filas[0]->correo;
            $this->password = $this->filas[0]->password;
            $this->creado = $this->filas[0]->creado;
            $this->baja = $this->filas[0]->baja;
            $this->nfc = $this->filas[0]->nfc;
            $this->salt = $this->filas[0]->salt;
        }
        return true;
    }

    

    public function Mostrar() {
        $sql = "SELECT * FROM usuario WHERE id=$this->id";
    
        $this->filas = $this->_consultar($sql);
        
        if ($this->filas == null)
            return false;
        
        // Guardamos los campos en las propiedades.
        $this->role = $this->filas[0]->role;
        $this->nombre = $this->filas[0]->nombre;
        $this->apellidos = $this->filas[0]->apellidos;
        $this->correo = $this->filas[0]->correo;
        $this->baja = $this->filas[0]->baja;
        $this->turno = $this->filas[0]->turno;
    }

    public function SeleccionarPorRol() {

        $sql = "SELECT * FROM usuario WHERE role='$this->role'";
        $this->filas = $this->_consultar($sql);
        if ($this->filas == null)
            return false;
        return true;
    }

    public function SeleccionarPorEmail($fecha) {
        $data = array( 'correo' => $this->correo,
                        'fecha' =>  $fecha);
        $response = integracionApi::llamarAPI('selecgmail', $data);
        if ($response['success'] === true) {
            
                $userData = $response['userData']; // Esta es la cadena JSON enviada desde la API
                $this->id = $userData['id'];
                $this->role = $userData['role'];
                $this->nombre = $userData['nombre'];
                $this->apellidos = $userData['apellidos'];
                $this->correo = $_SESSION['correo'];
                $this->residentes = $userData['residentes'];
                $this->tars = $userData['tars'];
                
        } else 
                return false;
    }
    public function SeleccionarResidentes() {
        $sql = "SELECT residentes FROM usuario WHERE id='$this->id'";
        $this->filas = $this->_consultar($sql);
        if ($this->filas == null)
            return false;
        $this->residentes = str_replace("\"", "&quot;",$this->filas[0]->residentes);
        
        return true;
    }
    

    public function FiltroNombreApellidos() {

        $sql = "SELECT * FROM usuario";
        if ($this->nombre != "") {
            $sql .= " WHERE CONCAT(nombre, ' ', apellidos) NOT LIKE '%$this->nombre%'";
        }
        $this->filas = $this->_consultar($sql);
        if ($this->filas == null)
            return false;
        return true;
    }

    public function FiltroMaestro() {
        $concat = false;
        $sql = 'SELECT * FROM usuario WHERE';
        if (isset($this->nombre)) {
            $sql .= " CONCAT(nombre, ' ', apellidos) LIKE '%$this->nombre%'";
            $concat = true;
        }
        if (isset($this->role)) {
            if ($concat) {
                $sql .= " and";
            }
            $sql .= " role='$this->role'";
            $concat = true;
        }
        $sql .= " ORDER BY nombre, apellidos ASC";
        $this->filas = $this->_consultar($sql);
        if ($this->filas == null) {
            return false;
        }
        return true;
    }
    public function Darbaja() {
        $sql = "UPDATE usuario SET baja = 1 WHERE id = $this->id";
        $this->_ejecutar($sql);
        return;
    }
    public function Desbaja() {
        $sql = "UPDATE usuario SET baja = 0 WHERE id = $this->id";
        $this->_ejecutar($sql);
        return;
    }

    public function ComprobarDia($frecuencia2, $dia, $hora){
        $resto = 1;
        $hoy = date('Y-m-d');
        $dia = date('d');
        $diaHoy = date($hoy);
        
        foreach ($frecuencia2 as $frec) {
                $dias = $frec['dias'];
                $frecuencia = $frec['frecuencia'];
                $inicio = $frec['inicio'];
        }
        //[{"dias":"todos","horas":["13:04","16:32","15:07"],"frecuencia":"diario","inicio":"2023-05-12"}]
        $dias = 0;
        $diaInicio = date($inicio);
        $fechaInicialSegundos = strtotime($diaInicio);
        $fechaFinalSegundos = strtotime($diaHoy);
        $contador = ($fechaFinalSegundos - $fechaInicialSegundos) / 86400;
        //------ C A L C U L A R   D I A S --------//
        if (preg_match('/\bdia\b/', $frecuencia)) {
            if(preg_match('/\d+/', $frecuencia, $matches)){
                $number = $matches[0];
                $num = intval($number);
                $dias = $num;
            }
            else{
                $dias = 1;
            }
            $resto = $contador % $dias;
        }

        //------ C A L C U L A R   D I A S   S E M A N A S --------//
        if(strpos($frecuencia, 'semana') !== false){
            if(preg_match('/\d+/', $frecuencia, $matches)){
                $number = $matches[0];
                $num = intval($number);
                for($i= 0; $i< $num; $i++){
                    $dias += 7;
                }  
            }
            else{
                $dias = 7;
            }
            $resto = $contador % $dias;
        }

        //------ C A L C U L A R   D I A S   M E S E S --------//
        if(strpos($frecuencia, 'mes') !== false){
            if(preg_match('/\d+/', $frecuencia, $matches)){
                $number = $matches[0];
                $num = intval($number);
                $mes = date('m', strtotime($inicio));
                for($i= 0; $i< $num; $i++){
                    $numeroDias = date('t', mktime(0, 0, 0, $mes, 1));// Obtener el número de días en el mes utilizando mktime()
                    $dias += $numeroDias;
                    $mes++;
                } 
            }
            else{
                $mes = date('m', strtotime($inicio));
                $numeroDias = date('t', mktime(0, 0, 0, $mes, 1));
                $dias = $numeroDias;
            }
            $resto = $contador % $dias;
        }

        //------ C A L C U L A R   D I A S   I M P A R E S --------//
        if(strpos($frecuencia, 'impar') !== false){
            $dividir = $dia % 2;
            if($dividir != 0)
                $resto = 0;
            else
                $resto = 1;

        }//------ C A L C U L A R   D I A S   P A R E S --------//
        else if(strpos($frecuencia, 'par') !== false){
            $resto = $dia % 2;
        }

        if($resto == 0 || $frecuencia == 'diario'){
            return true;
        }
        else{
            return false;
        }
    }

    public function SeleccionarHabilitados(){
        
        $sql = "SELECT id, role, turno FROM usuario WHERE baja=0";
        $this->filas = $this->_consultar($sql);
        $cont = 0;
        $cont3 = 0 ;
        $jsonObject = "{";
        $jsonSinAsig = "{";
            foreach ($this->filas as $fila) {
                $id = $fila->id;
                $role = $fila->role;
                $turno = $fila->turno; 
                if($role != 'Administrador'){
                    $sql = "SELECT tareaid, fecha FROM asignado WHERE usuarioid = $id AND completado != 1 ORDER BY DATE_FORMAT(fecha, '%H:%i:%s') ASC";
                    $fechas = $this->_consultar($sql);
                    $cont2 = 0;
                    if ($fechas != null) {
                        if ($cont > 0) {
                            $jsonObject .= ",";
                        }
    
                        $jsonObject .= '"' . $cont . '":{"usuarioid":"' . $id . '","role":"' . $role . '","horario":"' . $turno . '","asignadas":{';

                            if (count($fechas) >= 1) {
                                foreach ($fechas as $fech) {
                                    $tareaid = $fech->tareaid;
                                    $sql = "SELECT tiempo,fechas FROM tareas WHERE id = $tareaid";
                                      // Miramos cuanto se tarda en acabar la tarea
                            $tiempoObj = $this->_consultar($sql);
                            $tiempo2 = $tiempoObj[0];
                            $tiempo = substr($tiempo2->tiempo, 0, 5);
                            $frecuencia2 = $tiempo2->fechas;
                            $frecuencia2 = json_decode($frecuencia2, true);
                            // Acceder a frecuencia
                            $fecha = explode(" ", $fech->fecha);
                            list($dia, $hora2) = $fecha;
                            $hora = substr($hora2, 0, 5);
                            if($frecuencia2 != null){
                                $diaTrabajo = self::ComprobarDia($frecuencia2, $dia, $hora);
                            }
                                if ($diaTrabajo) {
                                    if ($cont2 > 0) {
                                        $jsonObject .= ",";
                                    }
    
                                    $jsonObject .= '"' . $cont2 . '":{"tareaid":"' . $tareaid . '","dia":"' . $dia . '","hora":"' . $hora . '","tiempoTarea":"' . $tiempo . '"}';
                                    $cont2++;
                                }
                            }
                        } else {
                            $tareaid = $fechas->tareaid;
                            $sql = "SELECT tiempo,fechas FROM tareas WHERE id = $tareaid";
                            $tiempo2 = $this->_consultar($sql);
                            $tiempo = substr($tiempo2, 0, 5);
                            $fecha = explode(" ", $fechas->fecha);
                            list($dia, $hora2) = $fecha;
                            $hora = substr($hora2, 0, 5);
                            $frecuencia2 = $tiempo2->fechas;
                            $frecuencia2 = json_decode($frecuencia2, true);
                            if($frecuencia2 != null){
                                $diaTrabajo = self::ComprobarDia($frecuencia2, $dia, $hora);
                            }
                            if($diaTrabajo)
                                $jsonObject .= '"0":{"tareaid":"' . $tareaid . '","dia":"' . $dia . '","hora":"' . $hora . '","tiempoTarea":"' . $tiempo . '"}'; 
                            
                        }
                        $cont++;
                        $jsonObject .= "}}";
                    }
                    else{
                        if ($cont3 > 0) {
                            $jsonSinAsig .= ",";
                        }
                        $jsonSinAsig .= '"' . $cont3 . '":{"usuarioid":"' . $id . '","role":"' . $role . '","horario":"' . $turno . '","tareas":0,"puesto":0}';
                        $cont3++;
                    }
            }
            }
            $jsonObject .= "}";
            $jsonSinAsig .= "}";
            $json = $jsonObject;
            $data = json_decode($json, true);
            if($fecha){
                // Función para obtener la hora de una tarea
                function getHora($tarea)
                {
                    return DateTime::createFromFormat('H:i', $tarea['hora'])->getTimestamp();
                }
                // Función para ordenar las tareas por la hora
                function ordenarPorHora($a, $b)
                {
                    return getHora($a) - getHora($b);
                }
                // Ordenar las tareas de cada usuario por la hora
                foreach ($data as &$usuario) {
                    if (isset($usuario['asignadas'])) {
                        uasort($usuario['asignadas'], 'ordenarPorHora');
                    }
                }
                // Ordenar los usuarios por la hora de su primera tarea
                uasort($data, function ($a, $b) {
                    $primerTareaA = reset($a['asignadas']);
                    $primerTareaB = reset($b['asignadas']);
                    return getHora($primerTareaA) - getHora($primerTareaB);
                });
            }
            // Volver a codificar el array como JSON
            $jsonOrdenado = json_encode($data);
    
            return $jsonOrdenado . '<[]>' . $jsonSinAsig;
        }
}
?>

