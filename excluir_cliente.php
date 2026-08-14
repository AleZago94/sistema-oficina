<?php
require_once "includes/autenticacao.php";

require_once "config/conexao.php";
require_once "includes/helpers.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("location: clientes.php?erro=requisicao_invalida");
    exit;
}
if (!validarCsrf()) {
    header("location: clientes.php?erro=token_invalido");
    exit;
}

if (!isset($_GET["id"])) {
    header("location: clientes.php?erro=id_nao_encontrado");
    exit;
}



$id = intval($_GET["id"]);

if ($id <=  0) {
    header("location: clientes.php?erro=id_invalido");
    exit;
}

$sql = "DELETE FROM clientes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
if (!$stmt->execute()) {
    header("location: clientes.php?erro=erro_ao_excluir");
    exit;
}

if ($stmt->affected_rows === 0) {
    header("location: clientes.php?erro=id_inexistente");
    exit;
}

header("location: clientes.php");
exit;
