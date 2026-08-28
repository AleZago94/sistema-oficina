<?php
require_once "includes/autenticacao.php";

require_once "config/conexao.php";
require_once "includes/helpers.php";



if (isset($_GET["erro"])) {

    switch ($_GET["erro"]) {

        case "campos_vazios":
            echo "<script>alert('preencha todos os campos')</script>";
            break;

        case "falha_inserir_servicos":
            echo "<script>alert('falha ao inserir servico')</script>";
            break;

        case "token_invalido":
            echo "<script>alert('Requisicao invalida tente novamente')</script>";
            break;
    }
}

if (isset($_GET["sucesso"])) {

    switch ($_GET["sucesso"]) {

        case "servico_cadastrado_sucesso":
            echo "<script>alert('servico cadastrado com sucesso')</script>";
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!validarCsrf()) {
        header("location: servicos.php?erro=token_invalido");
        exit;
    }

    $nome = trim($_POST['nome']);
    $valor = floatval($_POST['valor']);


    if (empty($nome) || empty($valor)) {
        header("location: servicos.php?erro=campos_vazios");
        exit;
    }
    $sql_servicos = "INSERT INTO servicos(nome, valor) VALUES(?, ?)";
    $stmt = $conn->prepare($sql_servicos);
    $stmt->bind_param("sd", $nome, $valor);

    if (!$stmt->execute()) {
        header("location: servicos.php?erro=falha_inserir_servicos");
        exit;
    }

    header("location: servicos.php?sucesso=servico_cadastrado_sucesso");
    exit;
}

$sql = "SELECT * FROM servicos ORDER BY nome ASC";
$result = $conn->query($sql);
include "includes/header.php";
include "includes/sidebar.php";

?>

<main class="app-main">

    <div class="app-content">
        <div class="container-fluid py-4">

            <!-- Cabeçalho -->
            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>
                    <h4 class="mb-1">Serviços</h4>
                    <small class="text-muted">
                        Cadastre e consulte os serviços oferecidos pela oficina
                    </small>
                </div>

            </div>


            <!-- Cadastro -->
            <div class="card mb-4">

                <div class="card-header">
                    <h3 class="card-title">
                        Novo Serviço
                    </h3>
                </div>

                <div class="card-body">

                    <form action="" method="POST">

                        <div class="row">

                            <!-- Nome -->
                            <div class="col-md-8 mb-3">

                                <label
                                    for="nome"
                                    class="form-label">
                                    Nome do serviço
                                </label>

                                <input
                                    type="text"
                                    name="nome"
                                    id="nome"
                                    class="form-control">

                            </div>


                            <!-- Valor -->
                            <div class="col-md-4 mb-3">

                                <label
                                    for="valor"
                                    class="form-label">
                                    Valor
                                </label>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="valor"
                                    id="valor"
                                    class="form-control">

                            </div>

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


                        <div class="d-flex justify-content-end">

                            <button
                                type="submit"
                                class="btn btn-primary">
                                Salvar serviço
                            </button>

                        </div>

                    </form>

                </div>

            </div>


            <!-- Listagem -->
            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">
                        Serviços Cadastrados
                    </h3>

                </div>


                <div class="card-body">

                    <?php if ($result->num_rows > 0): ?>

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped align-middle mb-0">

                                <thead>

                                    <tr>
                                        <th>Serviço</th>
                                        <th style="width: 180px;">Valor</th>
                                    </tr>

                                </thead>


                                <tbody>

                                    <?php while ($result_servico = $result->fetch_assoc()): ?>

                                        <tr>

                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $result_servico['nome'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                                ?>
                                            </td>

                                            <td>
                                                <strong>
                                                    R$
                                                    <?php
                                                    echo number_format(
                                                        $result_servico['valor'],
                                                        2,
                                                        ',',
                                                        '.'
                                                    );
                                                    ?>
                                                </strong>
                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="text-center text-muted py-4">
                            Nenhum serviço cadastrado.
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>
    </div>

</main>