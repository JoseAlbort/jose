<?php

require_once("bd.php");

class ResidenteModelo extends BD {

    public $id;
    public $nombre;
    public $apellidos;
    public $asistido;
    public $fecha;
    public $bloque_id;

    public function Insertar(){
        $sql = "INSERT INTO residente VALUES" . 
                " (default, '$this->nombre', '$this->apellidos', '$this->asistido', '$this->fecha', 0, '$this->bloque_id', default, default)";

        return $this->_ejecutar($sql);
    }
    public function Seleccionar(){
  
        $sql = 'SELECT * FROM residente';

        $sql .= " ORDER BY nombre ASC";
    
        $this->filas = $this->_consultar($sql);

        if ($this->filas == null)
            return false;
        
        if ($this->id != 0)
        {
            // Guardamos los campos en las propiedades.
            $this->id = $this->filas[0]->id;
            $this->nombre = $this->filas[0]->nombre;
            $this->apellidos = $this->filas[0]->apellidos;
            $this->asistido = $this->filas[0]->asistido;
            $this->fecha = $this->filas[0]->fecha;
            $this->bloque_id = $this->filas[0]->bloque_id;
            
        }
        return true;
    }

}