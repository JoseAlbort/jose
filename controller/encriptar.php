<?php
require_once('crypt.php');
$nombre = '123';
$nombreEncriptado = Crypt::Desencriptar($nombre);
echo '<pre>';
echo $nombreEncriptado;
echo '<br>';

$nombreDesencriptado = Crypt::Encriptar($nombreEncriptado);

echo $nombreDesencriptado;
echo '<pre>';
?>