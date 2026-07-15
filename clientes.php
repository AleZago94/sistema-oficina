<?php
require_once 'includes/autenticacao.php';
require_once 'config/conexao.php';

if (isset($_GET["erro"])) {
    if ($_GET["erro"] == "cliente_nao_encontrado") {
        echo "<script>alert('ERRO AO EDITAR CLIENTE'); </script>";
    }

    if ($_GET["erro"] == "erro_ao_excluir") {
        echo "<script>alert('nao foi possivel excluir o cliente')</script>";
    }

    if ($_GET["erro"] == "id_invalido") {
        echo "<script>alert('id invalido')</script>";
    }


    if ($_GET["erro"] == "id_inexistente") {
        echo "<script>alert('nao foi possivel encontrar  este id');</script>";
    }
    if ($_GET["erro"] == "id_nao_encontrado") {
        echo "<script>alert('nao foi possivel encontrar moto com este id');</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $cpf = trim($_POST['cpf']);
    $modelo = trim($_POST['modelo']);
    $placa = trim($_POST['placa']);



    if (empty($nome) || empty($telefone) || empty($cpf)) {
        echo "<script>alert('Preencha todos os campos');</script>";
        exit;
    }

    $sql = "INSERT INTO clientes (nome, telefone, cpf) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nome, $telefone, $cpf);

    if (!$stmt->execute()) {
        echo "erro ao cadastrar cliente ";
        exit;
    }

    $cliente_id = $conn->insert_id;

    $sql_moto = "INSERT INTO motos (cliente_id, modelo, placa) VALUES(?, ?, ?)";
    $stmt = $conn->prepare($sql_moto);
    $stmt->bind_param("iss", $cliente_id, $modelo, $placa);

    //  $conn->query($sql_moto);


    if (!$stmt->execute()) {
        echo "erro ao cadastrar moto";
        exit;
    }

    echo "<script>alert('Cliente cadastrado com sucesso');</script>";
}
$sql_cliente = "SELECT * FROM clientes";
$result_cliente = $conn->query($sql_cliente);

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="app-main">

    <div class="app-content">
        <div class="container-fluid">
            <h1>Cadastro de Clientes</h1>

            <form action="" method="post">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" name="nome" id="nome" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" name="telefone" id="telefone" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="cpf" class="form-label">CPF</label>
                    <input type="text" name="cpf" id="cpf" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="modelo" class="form-label">Modelo</label>
                    <input type="text" name="modelo" id="modelo" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="placa" class="form-label">placa</label>
                    <input type="text" name="placa" id="placa" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary">Salvar</button>
            </form>

        </div>

    </div>

    <div class="app-content">
        <div class="container-fluid">
            <h1>Clientes Cadastrados</h1>

            <?php if ($result_cliente->num_rows > 0): ?>

                <table class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Telefone</th>
                            <th>CPF</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php while ($cliente = $result_cliente->fetch_assoc()): ?>

                            <tr>

                                <td><?php echo $cliente['nome']; ?></td>

                                <td><?php echo $cliente['telefone']; ?></td>

                                <td><?php echo $cliente['cpf']; ?></td>


                                <td>

                                    <a
                                        href="editar_cliente.php?id=<?php echo $cliente['id']; ?>"
                                        class="btn btn-warning btn-sm">
                                        Editar
                                    </a>

                                    <a
                                        href="excluir_cliente.php?id=<?php echo $cliente['id']; ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Deseja excluir este cliente?')">
                                        Excluir
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <p>Nenhum cliente cadastrado.</p>

            <?php endif; ?>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>