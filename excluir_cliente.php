<?php
require_once "includes/autenticacao.php";

require_once 'config/conexao.php';

if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);

    $sql = "DELETE FROM clientes WHERE id = $id";

    $conn->query($sql);

    header("location: clientes.php");
    exit;
}
