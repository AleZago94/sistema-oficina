<?php
require_once "includes/autenticacao.php";

require_once "config/conexao.php";

include "includes/header.php";
include "includes/sidebar.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $valor = floatval($_POST['valor']);


    if (empty($nome) || empty($valor)) {
        echo "<script>alert('preencha todos os campos')</script>";
    } else {
        $sql_servicos = "INSERT INTO servicos(nome, valor) VALUES('$nome', '$valor')";
        $result_servicos = $conn->query($sql_servicos);
    }
}

$sql = "SELECT * FROM servicos ORDER BY nome ASC";
$result = $conn->query($sql);


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
                                <td><?php echo $result_servico['nome']; ?> </td>
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