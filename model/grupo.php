<?php

require_once("bd.php");

class GrupoModelo extends BD {
    // Variables de la tabla
    public $id;
    public $tareas;
    public $nombre;
    public $role;
    public $mes;
    public $gruposjson;


    public function Insertar() {
        $sql = "INSERT INTO grupo VALUES (default,'$this->nombre', '$this->tareas'" . 
        ", '$this->role')";
        return $this->_ejecutar($sql);
    }

    public function Modificar() {
        $sql = "UPDATE grupo SET 
        usuarios = '$this->usuarios', 
        tareas = '$this->tareas', 
        ciclo = '$this->ciclo', 
        diaInicio = '$this->diaInicio',
        nombre = '$this->nombre',
        role = '$this->role' 
        WHERE id = $this->id";
        return $this->_ejecutar($sql);
    }

    public function InsertarGrupo() {
        $sql = "UPDATE grupo 
        SET gruposjson = '$this->gruposjson', mes = '$this->mes', ususid = '$this->ususid'
        WHERE id = $this->id";   

        return $this->_ejecutar($sql);
    }

    public function Borrar() {
        $sql = "DELETE FROM grupo WHERE id=$this->id";

        return $this->_ejecutar($sql);
    }

    public function Seleccionar()
    {
        $sql = 'SELECT * FROM grupo';
        
        // Si me han pasado un id, obtenemos solo el registro indicado.
        if ($this->id != 0)
            $sql .= " WHERE id=$this->id";

        $sql .= " ORDER BY nombre ASC";
    
        $this->filas = $this->_consultar($sql);
        
        if ($this->filas == null)
            return false;
        
        if ($this->id != 0){
            // Guardamos los campos en las propiedades.
            $this->tareas = $this->filas[0]->tareas;
            $this->role = $this->filas[0]->role;
            $this->nombre = $this->filas[0]->nombre;
        }
        return true;
    }

    

    public function Mostrar() {
        $sql = "SELECT * FROM grupo WHERE id=$this->id";
    
        $this->filas = $this->_consultar($sql);
        
        if ($this->filas == null)
            return false;
        
        // Guardamos los campos en las propiedades.
        $this->usuarios = $this->filas[0]->usuarios;
        $this->tareas = $this->filas[0]->tareas;
        $this->ciclo = $this->filas[0]->ciclo;
        $this->cont = $this->filas[0]->cont;
        $this->nombre = $this->filas[0]->nombre;
    }

}