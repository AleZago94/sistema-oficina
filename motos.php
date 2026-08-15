<?php
require_once "includes/autenticacao.php";

require_once "config/conexao.php";
require_once "includes/helpers.php";


if (isset($_GET["erro"])) {

    switch ($_GET["erro"]) {

        case "id_nao_encontrado":
            echo "<script>alert('nao foi possivel encontra o id');</script>";
            break;

        case "id_inexistente":
            echo "<script>alert('nao foi possivel encontrar moto com este id');</script>";
            break;

        case "erro_excluir_moto":
            echo "<script>alert('nao foi possivel excluir o cadastro de motos')</script>";
            break;

        case "id_invalido":
            echo "<script>alert('id invalido')</script>";
            break;

        case "campos_vazios":
            echo "<script>alert('preencha os campos corretamente')</script>";
            break;

        case "falha_no_cadastro":
            echo "<script>alert('falha ao cadastrar moto tente novamente')</script>";
            break;

        case "erro_edicao_moto":
            echo "<script>alert('erro ao editar moto')</script>";
            break;

        case "token_invalido":
            echo "<script>alert('Requisicao invalida tente novamente')</script>";
            break;

        case "requisicao_invalida";
            echo "<script>alert('Requisicao invalida tente novamente')</script>";
            break;
    }
}

if (isset($_GET["sucesso"])) {

    switch ($_GET["sucesso"]) {

        case "moto_cadastrada":
            echo "<script>alert('moto cadastrada com sucesso')</script>";
            break;

        case "moto_atualizada";
            echo "<script>alert('moto atualizada com sucesso')</script>";
            break;
    }
}




if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!validarCsrf()) {
        header("location: motos.php?erro=token_invalido");
        exit;
    }

    $cliente_id = intval($_POST['cliente_id']);
    $marca = trim($_POST['marca']);
    $modelo = trim($_POST['modelo']);
    $placa = trim($_POST['placa']);
    $ano = trim($_POST['ano']);
    $observacoes = trim($_POST['observacoes']);

    if (empty($marca) || empty($modelo) || empty($placa) || empty($ano) || empty($observacoes) || empty($cliente_id)) {
        header("location: motos.php?erro=campos_vazios");
        exit;
    }

    $sql_cadastro = "INSERT INTO motos (cliente_id, marca, modelo, placa, ano, observacoes) VALUES(?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_cadastro);
    $stmt->bind_param("isssss", $cliente_id, $marca, $modelo, $placa, $ano, $observacoes);

    if (!$stmt->execute()) {
        header("location: motos.php?erro=falha_no_cadastro");
        exit;
    }

    header("location: motos.php?sucesso=moto_cadastrada");
    exit;
}


$sql_clientes = "SELECT * FROM clientes ORDER BY nome ASC";
$result_clientes = $conn->query($sql_clientes);



$sql = "SELECT motos.*, clientes.nome AS nome_cliente
 FROM motos
 JOIN clientes ON motos.cliente_id = clientes.id";
$result = $conn->query($sql);


include "includes/header.php";
include "includes/sidebar.php";

?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <h1>Cadastro de motos </h1>

            <form action="" method="POST">

                <div class="mb-3">
                    <label for="cliente_id" class="form-label">Cliente</label>
                    <select name="cliente_id" id="cliente_id" class="form-control">
                        <option value="">Selecione um cliente</option>
                        <?php while ($cliente = $result_clientes->fetch_assoc()): ?>
                            <option value="<?php echo intval($cliente['id']); ?>">
                                <?php echo htmlspecialchars($cliente['nome'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>


        </div>
        <div class="mb-3">
            <label for="marca" class="form-label">Marca</label>
            <input type="text" name="marca" id="marca" class="form-control">
        </div>

        <div class="mb-3">
            <label for="modelo" class="form-label">Modelo</label>
            <input type="text" name="modelo" id="modelo" class="form-control">
        </div>

        <div class="mb-3">
            <label for="placa" class="form-label">placa</label>
            <input type="text" name="placa" id="placa" class="form-control">
        </div>

        <div class="mb-3">
            <label for="ano" class="form-label">ano de fabricacao</label>
            <input type="text" name="ano" id="ano" class="form-control">
        </div>

        <div class="mb-3">
            <label for="observacoes" class="form-label">observacoes</label>
            <input type="text" name="observacoes" id="observacoes" class="form-control">

        </div>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" class="btn btn-primary">Salvar</button>

        </form>

    </div>





    <div class="app-content">
        <div class="container-fluid">
            <h1>Motos cadastradas</h1>
            <?php if ($result->num_rows > 0): ?>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>cliente</th>
                            <th>marca</th>
                            <th>modelo</th>
                            <th>placa</th>
                            <th>ano de fabricacao</th>
                            <th>observacoes</th>
                            <th>acoes</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($result_motos = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($result_motos['nome_cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($result_motos['marca'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($result_motos['modelo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($result_motos['placa'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($result_motos['ano'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($result_motos['observacoes'], ENT_QUOTES, 'UTF-8'); ?></td>

                                <td>
                                    <a href="editar_motos.php?id=<?php echo $result_motos['id']; ?>" class="btn btn-warning btn-sm">editar</a>

                                    <form action="excluir_motos.php?id=<?php echo intval($result_motos['id']); ?>" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja excluir esta moto?')">excluir</button>

                                    </form>



                                </td>

                            </tr>
                        <?php endwhile ?>
                    </tbody>

                </table>

            <?php else: ?>
                <p>nenhuma moto cadastrada</p>

            <?php endif ?>

        </div>

    </div>

</main>

<?php include "includes/footer.php" ?>