<?php
require_once "includes/autenticacao.php";

require_once "config/conexao.php";

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    $sql = "DELETE FROM motos WHERE id = $id";

    $conn->query($sql);

    header("location: motos.php");
    exit;
}
