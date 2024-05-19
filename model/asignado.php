<?php

require_once("bd.php");

class AsignadoModelo extends BD {
    // Variables de la tabla
    public $id;
    public $tareaid;
    public $usuarioid;
    public $grupoid;
    public $fecha;
    public $completado;
    public $falta;
    public $denegado;
    public $iniciado;
    public $acabado;
    public $tiempo;
    public $comentario;
    public $mes;
    public $ususid;

    // Variables Calculadas
    public $gruposjson;
    public $tareasjson;
    public $usuario;
    public $tarea;
    public $tarda;

    // Cuando se crea una asignación, solo se necesita la tarea, el usuario y la fecha, inicializando completado y falta a 0.
    public function Insertar() {
        $sql = "INSERT INTO asignado VALUES" . 
                " (default, '$this->tareaid', '$this->usuarioid', '$this->fecha', 0, 0, 0, default, default, default, default)";

        return $this->_ejecutar($sql);
    }
    public function InsertarGrupo() {
        $sql = "INSERT INTO asignado (grupoid, gruposjson, mes, ususid) 
        VALUES ('$this->grupoid', '$this->gruposjson', '$this->mes', '$this->ususid')";    

        return $this->_ejecutar($sql);
    }
    

    // Cuando se comienza una tarea, iniciado se actualiza al tiempo actual
    public function Comenzar() {
        $t=time();
        $sql = "UPDATE asignado SET iniciado='" . date("H:i:s",$t) . "' WHERE id=$this->id";
        return $this->_ejecutar($sql);
    }
    public function ComenzarGrupo() {
        $t=time();
        $sql = "INSERT INTO asignado VALUES" . 
        " (default, '$this->tareaid', '$this->usuarioid', '$this->fecha', 0, 0, 0, '".date("H:i:s",$t)."', default, default, default)";
        return $this->_ejecutar($sql);
    }

    // Cuando se acaba una tarea, acabado se actualiza al tiempo actual, se completa la tarea, y el trigger en la base de datos calcula el tiempo
    public function Acabar() {
        $sql = "UPDATE asignado SET completado=1, acabado='" . $this->acabado . "', tiempo='" . $this->tiempo . "'";
        if ($this->falta == true) {
            $sql .= ", falta = 1";
        }
        if ($this->comentario != '') {
            $sql .= ", comentario='$this->comentario'";
        }
        $sql .= " WHERE id=$this->id";
        return $this->_ejecutar($sql);
    }

    // Si se comete una falta, actualiza en la base de datos
    public function Falta() {
        $sql = "UPDATE asignado SET falta=1 WHERE id=$this->id";
        return $this->_ejecutar($sql); 
    }

    // Si se reasigna una tarea, se actualiza el usuario y la fecha con los mismos datos
    public function Reasignar() {
        $sql = "UPDATE asignado SET" . 
                " usuarioid='$this->usuarioid', fecha='$this->fecha' WHERE id=$this->id";

        return $this->_ejecutar($sql);
    }

    // Si se deniega una función, se actualiza el campo con el mensaje
    public function Denegar() {
        $sql = "UPDATE asignado SET" . 
                " denegado='1', falta=0, comentario='$this->comentario' WHERE id=$this->id";

        return $this->_ejecutar($sql);
    }

    public function Reiniciar() {
        $sql = "UPDATE asignado SET acabado=NULL, tiempo=NULL, iniciado=NULL, comentario=NULL, denegado=0, completado=0, falta=0 WHERE id=$this->id";
        return $this->_ejecutar($sql);
    }

    // Para borrar una fila
    public function Borrar() {
        $sql = "DELETE FROM asignado WHERE id=$this->id";

        return $this->_ejecutar($sql);
    }

    public function seleccionarGrupo(){
        $usuarioId = $this->usuarioid;
        $sql = "SELECT * FROM grupo WHERE FIND_IN_SET('$usuarioId', ususid)";    
        $this->filas = $this->_consultar($sql);
        if($this->filas == null)
            return false;

        $this->id = $this->filas[0]->id;
        $this->tareasjson = $this->filas[0]->tareas;
        $this->gruposjson = $this->filas[0]->gruposjson;
        $this->mes = $this->filas[0]->mes;
        return true;
    }

    // Si queremos seleccionar las asignaciones
    public function Seleccionar() {
        // No mostraremos los ids de las tareas y los usuarios. En su lugar, queremos mostrar los nombres
        $sql = 'SELECT *, ' .
            "(SELECT CONCAT(u.nombre, ' ', u.apellidos) FROM usuario u WHERE u.id = a.usuarioid) AS usuario, " .
            "(SELECT nombre FROM tareas t WHERE t.id = a.tareaid) AS tarea_nombre " .
            'FROM asignado a';
        
        // Si me han pasado un id, obtenemos solo el registro indicado.
        if ($this->id != 0)
            $sql .= " WHERE id=$this->id";
        $sql .= " ORDER BY FECHA DESC";
    
        $this->filas = $this->_consultar($sql);
        
        if ($this->filas == null)
            return false;
        
        if ($this->id != 0){
            // Guardamos los campos en las propiedades.
            $this->tareaid = $this->filas[0]->tareaid;
            $this->usuarioid = $this->filas[0]->usuarioid;
            $this->fecha = $this->filas[0]->fecha;
            $this->completado = $this->filas[0]->completado;
            $this->falta = $this->filas[0]->falta;
            $this->denegado = $this->filas[0]->denegado;
            $this->iniciado = $this->filas[0]->iniciado;
            $this->acabado = $this->filas[0]->acabado;
            $this->tiempo = $this->filas[0]->tiempo;
            $this->comentario = $this->filas[0]->comentario;

            $this->usuario = $this->filas[0]->usuario;
        }
        return true;
    }
    
    // Para poder seleccionar las tareas asignadas a un usuario
    public function SeleccionPorUsuario() {
        $sql = 'SELECT *,' .
        " (SELECT CONCAT(u.nombre, ' ', u.apellidos) FROM usuario u WHERE u.id = a.usuarioid) AS usuario," .
        " (SELECT t.nombre FROM tareas t WHERE t.id = a.tareaid) AS tarea" .
        " FROM asignado a  WHERE usuarioid = $this->usuarioid ORDER BY fecha DESC";
        // Queremos ordenar por fecha en orden descendente
        $this->filas = $this->_consultar($sql);
        if ($this->filas == null) {
            return false;
        }
        return true;
    }

    public function comprobarDisponibilidad($id, $fechasDiario) {
        //Obtener la hora actual
        $horaActual = date('H:i:s'); // Formato: HH:MM:SS

        $sql = "SELECT asignado.fecha, asignado.usuarioid, tareas.id
        FROM asignado
        INNER JOIN tareas ON asignado.tareaid = tareas.id
        WHERE asignado.usuarioid = $id AND tareas.repetible = 1";

        // Queremos ordenar por fecha en orden descendente
        $this->filas = $this->_consultar($sql);
        if ($this->filas == null) {
            return false;
        }

        foreach($horaActual as $hora){
            //echo $hora;
        }
        foreach($this->filas as $fila){
            $fechaHoy = date("Y-m-d");
           
            $fechaCompleta = $fila->fecha;
            $fechaAsig = substr($fechaCompleta, 0, 10); // Obtener la parte de la fecha (primeros 10 caracteres)
            $horaAsig = substr($fechaCompleta, 11); // Obtener la parte de la hora (desde el caracter 11 en adelante)

            //Comprobamos que el trabajdor tiene el dia libre
            if($fechaHoy != $fechaAsig){
                
            }
            else{

            }
                //echo "ESHOY" . '<br>';
            
            

            //echo $horaAsig . '<br>';
        }
        
        return true;
    }

    public function TareasTrabajador() {
        //SELECT * FROM tu_tabla WHERE tu_campo LIKE '%informacion%'; Para buscar una fila de la subcadena.

        $sql = "SELECT * FROM asignado a WHERE usuarioid = $this->usuarioid AND fecha LIKE '$this->fecha%' ORDER BY fecha ASC";
        // Queremos ordenar por fecha en orden descendente
        $this->filas = $this->_consultar($sql);
        if ($this->filas == null) {
            return false;
        }
        return true;
    }

    public function AsigGrupo(){
        $sql = "SELECT *
        FROM asignado
        WHERE usuarioid = $this->usuarioid
        AND fecha = '$this->fecha'";

        $this->filas = $this->_consultar($sql);
        if ($this->filas == null) {
            return false;
        }
        return true;
    }

    // Para poder seleccionar todas las asignaciones de una tarea
    public function SeleccionPorTarea() {
        $sql = 'SELECT *,' .
        " (SELECT CONCAT(u.nombre, ' ', u.apellidos) FROM usuario u WHERE u.id = a.usuarioid) AS usuario," .
        " (SELECT t.nombre FROM tareas t WHERE t.id = a.tareaid) AS tarea" .
        ' FROM asignado a';
        
        if ($this->tareaid != 0) {
            $sql .= " WHERE tareaid = $this->tareaid";
        }
       
        $this->filas = $this->_consultar($sql);
        if ($this->filas == null) {
            
            return false;
        }
        
        return true;
    }

    public function FiltroMaestro() {
        $concat = false;
        $sql = 'SELECT *,' .
        " (SELECT CONCAT(u.nombre, ' ', u.apellidos) FROM usuario u WHERE u.id = a.usuarioid) AS usuario," .
        " (SELECT t.nombre FROM tareas t WHERE t.id = a.tareaid) AS tarea" .
        " FROM asignado a WHERE";
        if (isset($this->usuarioid)) {
            if (is_numeric($this->usuarioid)) {
                $sql .= " usuarioid=$this->usuarioid";
            }
            else {
                $sql .= " (SELECT CONCAT(u.nombre, ' ', u.apellidos) FROM usuario u WHERE u.id = a.usuarioid) LIKE '%$this->usuarioid%'";
            }
            $concat = true;
        }
        if (isset($this->tareaid)) {
            if ($concat) {
                $sql .= " and";
            }
            if (is_numeric($this->tareaid)) {
                $sql .= " tareaid=$this->tareaid";
            }
            else {
                $sql .= " (SELECT t.nombre FROM tareas t WHERE t.id = a.tareaid) LIKE '%$this->tareaid%'";
            }
            $concat = true;
        }
        if (isset($this->fecha)) {
            if ($concat) {
                $sql .= " and";
            }
            if (stripos($this->fecha, ":") != false) {
                $sql .= " fecha BETWEEN '" . explode(':', $this->fecha)[0] . " 00:00:00' and '" . explode(':', $this->fecha)[1] . " 23:59:59'";
            }
            else {
                if ($this->fecha != "") {
                    $sql .= " fecha LIKE '$this->fecha%'";
                }
            }
            $concat = true;
        }
        if (isset($this->completado)) {
            if ($concat) {
                $sql .= " and";
            }
            $sql .= " completado=$this->completado";
            $concat = true;
        }
        if (isset($this->denegado)) {
            if ($concat) {
                $sql .= " and";
            }
            $sql .= " denegado=$this->denegado";
            $concat = true;
        }
        if (isset($this->falta)) {
            if ($concat) {
                $sql .= " and";
            }
            $sql .= " falta=$this->falta";
            $concat = true;
        }
        $sql .= " ORDER BY fecha DESC";
        $this->filas = $this->_consultar($sql);
        if ($this->filas == null) {
            return false;
        }
        return true;
    }

    public function checkDateTime() {
        $sql = "SELECT * FROM asignado WHERE tareaid=$this->tareaid and fecha >= '$this->fecha%'";
        $this->filas = $this->_consultar($sql);
        if ($this->filas == null) {
            return false;
        }
        return true;
    }
    public function horarioTrabajador(){
        $sql = "SELECT a.*, (SELECT t.tiempo FROM tareas t WHERE t.id = a.tareaid) AS tarda, ";
        $sql .= "(SELECT t.nombre FROM tareas t WHERE t.id = a.tareaid) AS tarea FROM asignado a ";
        $sql .= "WHERE usuarioid=$this->usuarioid and fecha LIKE '$this->fecha%' ORDER BY fecha ASC";
        $this->filas = $this->_consultar($sql);
        if ($this->filas == null) {
            return false;
        }
        return true;
    }
}

?>