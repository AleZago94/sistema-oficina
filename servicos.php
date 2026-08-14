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
        <div class="container-fluid">
            <h1>cadastro de servicos</h1>
            <form action="" method="POST">
                <label for="nome" class="form-label">nome do servico</label>
                <input type="text" name="nome" id="nome" class="form-control">

                <label for="valor" class="form-label">Valor do servico</label>
                <input type="number" step="0.01" name="valor" id="valor" class="form-control">

                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-primary mt-3">salvar</button>
            </form>

        </div>

    </div>


    <div class="app-content">
        <div class="container-fluid">
            <h1>servicos cadastrados</h1>
            <?php if ($result->num_rows > 0): ?>
                <table class="table table-bordered table-striped">
                    <thead>
                        <th>servico</th>
                        <th>valor</th>
                    </thead>
                    <tbody>
                        <?php while ($result_servico = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($result_servico['nome'], ENT_QUOTES, 'UTF-8'); ?> </td>
                                <td>R$ <?php echo number_format($result_servico['valor'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endwhile ?>
                    </tbody>

                </table>

            <?php else: ?>
                <p>nenhum servico cadastrado</p>

            <?php endif ?>

            </table>

        </div>

    </div>

</main>