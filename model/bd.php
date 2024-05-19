<?php
require_once("./config.php");

/*{"0":{"archivo_nombre":"24-icons8-flecha-derecha-larga-24.png","archivo_url":"http://localhost/Tareas/imagenes/24-icons8-flecha-derecha-larga-24.png"},
"1":{"archivo_nombre":"24-reside.jpg","archivo_url":"http://localhost/Tareas/imagenes/24-reside.jpg"},
"2":{"archivo_nombre":"24-casita.png","archivo_url":"http://localhost/Tareas/imagenes/24-casita.png"}}*/

class BD
{
    private $con = null; // Conexión a la BBDD.
    private $con2 = null;
    private $error = ''; // Mensaje de error.

    function __construct(){
        //$this->con = $this->createConnection("95.60.181.162", "apps_", "dnarosa", "infor_matica_7");
        //$this->con = $this->createConnection("192.168.0.165", "apps_", "dnarosa", "infor_matica_7");
        //$this->con2 = $this->createConnection("http://jmvidareal.synology.me/", "gerencia", "root", "Barba_De_Pecho_1");
        //$this->con2 = $this->createConnection("192.168.0.59", "gerencia", "root", "Barba_De_Pecho_1");
        $this->con2 = $this->createConnection("localhost", "food_db", "root", "");
    }
    function createConnection($servidor, $basedatos, $usuario, $contrasena){
        $this->error = '';
        try{
            // Creamos la conexión.
            $this->con = new PDO('mysql:host=' . $servidor .
                                 ';port=3306;dbname=' . $basedatos .
                                 ';charset=utf8',
                                 $usuario,
                                 $contrasena);
            
            // Si se logra crear la conexión.
            if ($this->con)
            {
                // Ponemos los atributos para gestionar los errores con excepciones.
                $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // El juego de caracteres será utf-8
                $this->con->exec('SET CHARACTER SET utf8');
            }
        }catch (PDOException $e){

        }
    }
    function __destruct()
    {
        // Cerramos la conexión a la BBDD.
        $this->con = null;
    }
    protected function _consultar($query){
        $this->error = '';
        $filas = null;
        try {
            // Preparamos la consulta...
            $stmt = $this->con->prepare($query);

            // y la ejecutamos.
            $stmt->execute();
            
            // Si nos devuelve alguna fila...
            if ($stmt->rowCount() > 0)
            {
                // Creamos el array...
                $filas = array();

                // y lo rellenamos con los datos de la consulta.
                while ($registro = $stmt->fetchObject())
                    $filas[] = $registro;
             }
        }
        catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        
        // Devolvemos las filas obtenidas de la consulta.
        return $filas;
    }

    protected function _ejecutar($query)
    {
        $this->error = '';
        $filas = 0;

        try
        {
            // Ejecutamos la sentencia y guardamos el número de filas afectadas.
            $filas = $this->con->exec($query);
        }
        catch (PDOException $e)
        {
            $this->error = $e->getMessage();
        }

        // Devolvemos el número de filas afectadas.
        return $filas;
    }

    public function UltimoId()
    {
        // Devolvemos el id de la última fila insertada.
        return $this->con->lastInsertId();
    }

    public function GetError()
    {
        // Obtenemos el mensaje del error, si este se produce.
        return $this->error;
    }

    public function Error()
    {
        // Indicamos si ha habido algún error.
        return ($this->error != '');
    }
}
?>