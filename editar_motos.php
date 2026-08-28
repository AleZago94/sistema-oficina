<?php

require_once "includes/autenticacao.php";
require_once "config/conexao.php";
require_once "includes/helpers.php";


if (isset($_GET['erro'])) {

    switch ($_GET['erro']) {

        case "campos_vazios":
            echo "<script>alert('Preencha todos os campos');</script>";
            break;
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!validarCsrf()) {
        header("location: motos.php?erro=token_invalido");
        exit;
    }


    $id = intval($_POST['id']);
    $marca = trim($_POST['marca']);
    $modelo = trim($_POST['modelo']);
    $placa = trim($_POST['placa']);
    $ano = intval($_POST['ano']);
    $observacoes = trim($_POST['observacoes']);


    if (
        $id <= 0 ||
        empty($marca) ||
        empty($modelo) ||
        empty($placa) ||
        empty($ano) ||
        empty($observacoes)
    ) {

        header("location: editar_motos.php?id=$id&erro=campos_vazios");
        exit;
    }


    $sql_update = "
        UPDATE motos
        SET marca = ?,
            modelo = ?,
            placa = ?,
            ano = ?,
            observacoes = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($sql_update);

    $stmt->bind_param(
        "sssisi",
        $marca,
        $modelo,
        $placa,
        $ano,
        $observacoes,
        $id
    );


    if (!$stmt->execute()) {

        header("location: motos.php?erro=erro_edicao_moto");
        exit;
    }


    header("location: motos.php?sucesso=moto_atualizada");
    exit;
}



/* =========================
   BUSCAR MOTO
========================= */

if (!isset($_GET["id"])) {

    header("location: motos.php?erro=id_nao_encontrado");
    exit;
}


$id = intval($_GET['id']);


if ($id <= 0) {

    header("location: motos.php?erro=id_invalido");
    exit;
}


$sql = "SELECT * FROM motos WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();


$result = $stmt->get_result();


if ($result->num_rows == 0) {

    header("location: motos.php?erro=id_inexistente");
    exit;
}


$motos = $result->fetch_assoc();


include "includes/header.php";
include "includes/sidebar.php";

?>


<main class="app-main">

    <div class="app-content">

        <div class="container-fluid py-4">


            <!-- Cabeçalho -->
            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>

                    <h4 class="mb-1">
                        Editar Moto
                    </h4>

                    <small class="text-muted">
                        Atualize os dados da moto
                    </small>

                </div>


                <a
                    href="motos.php"
                    class="btn btn-secondary btn-sm">
                    Voltar
                </a>

            </div>


            <!-- Card -->
            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">
                        Dados da Moto
                    </h3>

                </div>


                <div class="card-body">

                    <form action="" method="POST">


                        <!-- ID -->
                        <input
                            type="hidden"
                            name="id"
                            value="<?php echo intval($motos['id']); ?>">


                        <!-- Marca / Modelo -->
                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label
                                    for="marca"
                                    class="form-label">
                                    Marca
                                </label>

                                <input
                                    type="text"
                                    name="marca"
                                    id="marca"
                                    value="<?php
                                            echo htmlspecialchars(
                                                $motos['marca'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>"
                                    class="form-control">

                            </div>


                            <div class="col-md-6 mb-3">

                                <label
                                    for="modelo"
                                    class="form-label">
                                    Modelo
                                </label>

                                <input
                                    type="text"
                                    name="modelo"
                                    id="modelo"
                                    value="<?php
                                            echo htmlspecialchars(
                                                $motos['modelo'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>"
                                    class="form-control">

                            </div>

                        </div>


                        <!-- Placa / Ano -->
                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label
                                    for="placa"
                                    class="form-label">
                                    Placa
                                </label>

                                <input
                                    type="text"
                                    name="placa"
                                    id="placa"
                                    value="<?php
                                            echo htmlspecialchars(
                                                $motos['placa'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>"
                                    class="form-control">

                            </div>


                            <div class="col-md-6 mb-3">

                                <label
                                    for="ano"
                                    class="form-label">
                                    Ano de fabricação
                                </label>

                                <input
                                    type="number"
                                    name="ano"
                                    id="ano"
                                    value="<?php echo intval($motos['ano']); ?>"
                                    class="form-control">

                            </div>

                        </div>


                        <!-- Observações -->
                        <div class="mb-3">

                            <label
                                for="observacoes"
                                class="form-label">
                                Observações
                            </label>

                            <textarea
                                name="observacoes"
                                id="observacoes"
                                class="form-control"
                                rows="3"><?php
                                            echo htmlspecialchars(
                                                $motos['observacoes'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?></textarea>

                        </div>


                        <!-- CSRF -->
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php
                                    echo htmlspecialchars(
                                        $_SESSION['csrf_token'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>">


                        <!-- Ações -->
                        <div class="d-flex justify-content-end gap-2 mt-3">

                            <a
                                href="motos.php"
                                class="btn btn-secondary">
                                Cancelar
                            </a>

                            <button
                                type="submit"
                                class="btn btn-primary">
                                Salvar alterações
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</main>


<?php include "includes/footer.php"; ?>