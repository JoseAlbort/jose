<?php

require_once("bd.php");

class TareasModelo extends BD {
    public $id;
    public $nombre;
    public $tipo;
    public $nota;
    public $role;
    public $tiempo;
    public $tiempo_medio;       // Nullable
    public $repetible;
    public $fechas;             // Nullable (no)
    public $tareasuperior;      // Nullable
    public $habilitada;
    public $archivos;
    public $horarios;


    // Campos calculados
    public $tareaid;
    public $hora;
    public $progreso;
    public $fotos;


    public function Insertar() {
        $sql = "INSERT INTO tareas VALUES" . 
                " (default, '$this->nombre', '$this->tipo', '$this->nota'," .
                " '$this->role', '$this->tiempo', default, $this->repetible, '$this->fechas'";
        if ($this->tareasuperior != '') {
            $sql .= ", '$this->tareasuperior";
        }
        else {
            $sql .= ", default";
        }
        $sql .= ", 1, default, '$this->horarios')";

        return $this->_ejecutar($sql);
    }

    public function Modificar() {
        $sql = "UPDATE tareas SET" . 
                " nombre='$this->nombre', tipo='$this->tipo', nota='$this->nota'," .
                " role='$this->role', tiempo='$this->tiempo', repetible=$this->repetible";
        if ($this->tareasuperior != '') {
            $sql .= ", tareasuperior=$this->tareasuperior";
        }
        if ($this->fechas != '') {
            $sql .= ", fechas='$this->fechas'";
        }

        $sql .= " WHERE id=$this->id";

        return $this->_ejecutar($sql);
    }

    public function Deshabilitar() {
        $sql = "UPDATE tareas SET habilitada=0 WHERE id=$this->id";
        return $this->_ejecutar($sql);
    }

    public function Habilitar() {
        $sql = "UPDATE tareas SET habilitada=1 WHERE id=$this->id";
        return $this->_ejecutar($sql);
    }

    public function Borrar()
    {
        $sql = "DELETE FROM tareas WHERE id=$this->id";

        return $this->_ejecutar($sql);
    }



    public function Mostrar() {
        $sql = "SELECT * FROM tareas WHERE id=$this->id";
    
        $this->filas = $this->_consultar($sql);
        
        if ($this->filas == null)
            return false;
        
        // Guardamos los campos en las propiedades.
        $this->nombre = $this->filas[0]->nombre;
        $this->tipo = $this->filas[0]->tipo;
        $this->nota = $this->filas[0]->nota;
        $this->role = $this->filas[0]->role;
        $this->tiempo = $this->filas[0]->tiempo;
        $this->tiempo_medio = $this->filas[0]->tiempo_medio;
        $this->repetible = $this->filas[0]->repetible;
        $this->fechas = $this->filas[0]->fechas;
        $this->tareasuperior = $this->filas[0]->tareasuperior;
        $this->habilitada = $this->filas[0]->habilitada;
        $this->archivos = $this->filas[0]->archivos;
        if ($this->archivos != "") {
            $aux1 = [];
            $aux = json_decode($this->archivos, true);
            foreach($aux as $key => $valor) {
                $aux1[$key] = $valor["archivo_url"];
            }
            $this->fotos = $aux1;
        }
        return true;
    }

    public function SeleccionarNoAsignadas() {

        $sql = "SELECT t.* FROM tareas t WHERE t.id NOT IN (SELECT a.tareaid FROM asignado a) AND role='$this->role'";
        $this->filas = $this->_consultar($sql);

        if ($this->filas == null)
            return false;

        return true;
    }
    
    public function FiltroNombre() {
        $sql = "SELECT * FROM tareas WHERE nombre NOT LIKE '%$this->nombre%'";
        $this->filas = $this->_consultar($sql);

        if ($this->filas == null)
            return false;
        return true;
    }

    public function Filtro() {
        $sql = "SELECT * FROM tareas WHERE id=0";
        $this->filas = $this->_consultar($sql);
    }

    public function FiltroMaestro() {
        $concat = false;
        $sql = 'SELECT * FROM tareas WHERE';
        if (isset($this->nombre)) {
            $sql .= " nombre LIKE '%$this->nombre%'";
            $concat = true;
        }
        if (isset($this->tipo)) {
            if ($concat) {
                $sql .= " and";
            }
            $sql .= " tipo='$this->tipo'";
            $concat = true;
        }
        if (isset($this->role)) {
            if ($concat) {
                $sql .= " and";
            }
            $sql .= " role='$this->role'";
            $concat = true;
        }
        if (isset($this->habilitada)) {
            if ($concat) {
                $sql .= " and";
            }
            $sql .= " habilitada=$this->habilitada";
            $concat = true;
        }
        if (isset($this->repetible)) {
            if ($concat) {
                $sql .= " and";
            }
            $sql .= " repetible=$this->repetible";
            $concat = true;
        }
        $sql .= " ORDER BY nombre ASC";
        $this->filas = $this->_consultar($sql);
        if ($this->filas == null) {
            return false;
        }
        return true;
    }

    public function ModificarFoto(){
        $sql="UPDATE tareas SET".
            " archivos='$this->archivos'".
            " WHERE id=$this->id";

        return $this->_ejecutar($sql);
    }
    public function SeleccionarPorRole(){
        $sql = "SELECT * FROM usuario WHERE role='$this->role' AND baja=0";

        $this->filas = $this->_consultar($sql);

        return;
    }

    public function Seleccionar(){
        $response = integracionApi::llamarAPI('selectareas', null);
        if ($response['success'] === true) {
            $this->filas = $response['tasks'];
        }
        return true;
    }

    public function SeleccionarTarea(){
        $hoy = date('Y-m-d');
        $dia = date('d');

        $sql = "SELECT * FROM tareas
                WHERE tareas.id NOT IN (SELECT asignado.tareaid FROM asignado)
                AND tareas.habilitada = 1
                ORDER BY tareas.nombre ASC";
        $this->filas = $this->_consultar($sql);
        $this->fechas = [];

        if ($this->filas !== null) {
            $resto= 1;
            foreach ($this->filas as $key => $fila) {

                $fechasJson = $fila->fechas;
                $role = $fila->role;
                $id = $fila->id;
                $horarioObj = $fila->tiempo;
                $tiempo = substr($horarioObj, 0, -3);

                if (!is_null($fila->fechas) && $fila->fechas !== "") {
                    $fechasJson = preg_replace_callback(
                        '/"horas":\["([^"]+)"\]/',
                        function ($matches) {
                            $horas = $matches[1];
                            $horas = preg_replace('/\s(?!(?:horas|minutos)\b)/', '", "', $horas);
                            return '"horas":["' . $horas . '"]';
                        },
                        $fechasJson,
                        1
                    );

                    $fechasJson = substr_replace($fechasJson, '"id":"' . $id . '", "role":"' . $role . '", "tiempoTarea":"' . $tiempo . '",', 1, 0); // Agregar "id":"$id" y "0" al inicio del JSON
                    
                }
                //Comprobar las tareas que son de hoy
                $fechasObj = json_decode($fechasJson); // Convertir la cadena JSON en objeto stdClass
                $salir= false;
                if ($fechasObj !== null) {   
                    foreach ($fechasObj as $key => $fechasItem) {
                        if ($key === '0') {
                            $hora = $fechasItem->horas;
                            $dias = $fechasItem->dias;
                            $frecuencia = $fechasItem->frecuencia;
                            $inicio = $fechasItem->inicio;

                        }
                    }
                
                    //Pasamos la fecha a un formato numerico
                    $dias= 0; 
                    $diaHoy = date($hoy);
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
                        $this->fechas[] = $fechasJson;
                    }
                    else{
                        unset($this->filas[$key]);
                    }     
                } 
            }
            $this->fechas = "[" . implode(", ", $this->fechas) . "]";

        } else {
            $this->fechas = "[]";
        }

        return;
    }
}


?>