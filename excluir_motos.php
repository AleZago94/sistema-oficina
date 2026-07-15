<?php
require_once "includes/autenticacao.php";

require_once "config/conexao.php";

if (!isset($_GET['id'])) {
    header("location: motos.php?erro=id_nao_encontrado");
    exit;

}

    $id = intval($_GET['id']);

    if($id <= 0 ){
        header("location: motos.php?erro=id_invalido");
        exit;
    }

    $sql = "DELETE FROM motos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        header("location: motos.php?erro=erro_excluir_moto");
        exit;
    }

    if($stmt->affected_rows === 0){
        header("location: motos.php?erro=id_inexistente");
        exit;

    }

    header("location: motos.php");
    exit;

