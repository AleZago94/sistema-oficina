<?php
require_once "includes/autenticacao.php";

require_once "config/conexao.php";
include "includes/header.php";
include "includes/sidebar.php";

if (isset($_GET["erro"])) {
    if ($_GET["erro"] == "id_nao_encontrado") {
        echo "<script>alert('nao foi possivel encontra o id');</script>";
    }

    if ($_GET["erro"] == "id_inexistente") {
        echo "<script>alert('nao foi possivel encontrar moto com este id');</script>";
    }
    if ($_GET["erro"] == "erro_excluir_moto") {
        echo "<script>alert('nao foi possivel excluir o cadastro de motos')</script>";
    }

    if ($_GET["erro"] == "id_invalido") {
        echo "<script>alert('id invalido')</script>";
    }
}




if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $cliente_id = intval($_POST['cliente_id']);
    $marca = trim($_POST['marca']);
    $modelo = trim($_POST['modelo']);
    $placa = trim($_POST['placa']);
    $ano = trim($_POST['ano']);
    $observacoes = trim($_POST['observacoes']);

    if (empty($marca) || empty($modelo) || empty($placa) || empty($ano) || empty($observacoes) || empty($cliente_id)) {
        echo "<script>alert('preencha os campos corretamente')</script>";
    } else {
        $sql_cadastro = "INSERT INTO motos (cliente_id, marca, modelo, placa, ano, observacoes) VALUES('$cliente_id', '$marca', '$modelo', '$placa', '$ano', '$observacoes')";

        $result_cadastro = $conn->query($sql_cadastro);
    }
}


$sql_clientes = "SELECT * FROM clientes ORDER BY nome ASC";
$result_clientes = $conn->query($sql_clientes);



$sql = "SELECT motos.*, clientes.nome AS nome_cliente
 FROM motos
 JOIN clientes ON motos.cliente_id = clientes.id";
$result = $conn->query($sql);




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
                            <option value="<?php echo $cliente['id']; ?>">
                                <?php echo $cliente['nome']; ?>
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
                                <td><?php echo $result_motos['nome_cliente']; ?></td>
                                <td><?php echo $result_motos['marca']; ?></td>
                                <td><?php echo $result_motos['modelo']; ?></td>
                                <td><?php echo $result_motos['placa']; ?></td>
                                <td><?php echo $result_motos['ano']; ?></td>
                                <td><?php echo $result_motos['observacoes']; ?></td>

                                <td>
                                    <a href="editar_motos.php?id=<?php echo $result_motos['id']; ?>" class="btn btn-warning btn-sm">editar</a>
                                    <a href="excluir_motos.php?id=<?php echo $result_motos['id']; ?>" class="btn btn-danger btn-sm">excluir</a>


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