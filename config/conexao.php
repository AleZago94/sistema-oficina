<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "oficina";

$conn = new mysqli($servidor, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("conexao falhou:" . $conn->connect_error);
}


$conn->set_charset("utf8mb4");
