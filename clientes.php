<?php
require_once 'includes/autenticacao.php';
require_once 'config/conexao.php';
require_once "includes/helpers.php";
if (isset($_GET["erro"])) {

    switch ($_GET["erro"]) {

        case "cliente_nao_encontrado":
            echo "<script>alert('ERRO AO EDITAR CLIENTE'); </script>";
            break;

        case "erro_ao_excluir":
            echo "<script>alert('nao foi possivel excluir o cliente')</script>";
            break;

        case  "id_invalido":
            echo "<script>alert('id invalido')</script>";
            break;

        case "id_inexistente":
            echo "<script>alert('nao foi possivel encontrar  este id');</script>";
            break;

        case "id_nao_encontrado":
            echo "<script>alert('nao foi possivel encontrar moto com este id');</script>";
            break;

        case "falha_ao_cadastrar":
            echo "<script>alert('falha ao cadastrar cliente')</script>";
            break;

        case "token_invalido":
            echo "<script>alert('Requisição inválida. Tente novamente.')</script>";
            break;

        case "campos_vazios":
            echo "<script>alert('preencha os campos corretamente.')</script>";
            break;
    }
}

if (isset($_GET['sucesso'])) {

    switch ($_GET['sucesso']) {

        case "cliente_cadastrado":
            echo "<script>alert('Cliente Cadastrado com sucesso')</script>";
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!validarCsrf()) {
        header("Location: clientes.php?erro=token_invalido");
        exit;
    }

    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $cpf = trim($_POST['cpf']);
    $modelo = trim($_POST['modelo']);
    $placa = trim($_POST['placa']);



    if (empty($nome) || empty($telefone)) {
        echo "<script>alert('Preencha todos os campos');</script>";
        header("location: clientes.php?erro=campos_vazios");
        exit;
    }

    try {
        $conn->begin_transaction();

        $sql = "INSERT INTO clientes (nome, telefone, cpf) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nome, $telefone, $cpf);
        $stmt->execute();



        $cliente_id = $conn->insert_id;

        if (!empty($modelo) || !empty($placa)) {
            $sql_moto = "INSERT INTO motos (cliente_id, modelo, placa) VALUES(?, ?, ?)";
            $stmt = $conn->prepare($sql_moto);
            $stmt->bind_param("iss", $cliente_id, $modelo, $placa);
            $stmt->execute();
        }



        //  $conn->query($sql_moto);
        $conn->commit();

        header("location: clientes.php?sucesso=cliente_cadastrado");
        exit;
    } catch (mysqli_sql_exception $erro) {
        $conn->rollback();

        header("location: clientes.php?erro=falha_ao_cadastrar");
        exit;
    }
}
$sql_cliente = "SELECT * FROM clientes ORDER BY id DESC";
$result_cliente = $conn->query($sql_cliente);

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="app-main">

    <div class="app-content">
        <div class="container-fluid py-4">

            <!-- Cabeçalho -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-1">Clientes</h4>
                    <small class="text-muted">
                        Cadastre e gerencie os clientes da oficina
                    </small>
                </div>
            </div>


            <!-- Cadastro -->
            <div class="card mb-4">

                <div class="card-header">
                    <h3 class="card-title">
                        Novo Cliente
                    </h3>
                </div>

                <div class="card-body">

                    <form action="" method="post">

                        <div class="row">

                            <!-- Nome -->
                            <div class="col-md-6 mb-3">

                                <label
                                    for="nome"
                                    class="form-label">
                                    Nome
                                </label>

                                <input
                                    type="text"
                                    name="nome"
                                    id="nome"
                                    class="form-control">

                            </div>


                            <!-- Telefone -->
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label">Telefone</label>

                                <input
                                    type="tel"
                                    name="telefone"
                                    id="telefone"
                                    class="form-control"
                                    maxlength="15"
                                    placeholder="(16) 99999-9999">
                            </div>


                            <div class="row">

                                <!-- CPF -->
                                <div class="col-md-6 mb-3">
                                    <label for="cpf" class="form-label">CPF</label>

                                    <input
                                        type="text"
                                        name="cpf"
                                        id="cpf"
                                        class="form-control"
                                        maxlength="14"
                                        inputmode="numeric"
                                        placeholder="000.000.000-00">
                                </div>


                                <!-- Modelo -->
                                <div class="col-md-6 mb-3">

                                    <label
                                        for="modelo"
                                        class="form-label">
                                        Modelo da moto
                                    </label>

                                    <input
                                        type="text"
                                        name="modelo"
                                        id="modelo"
                                        class="form-control">

                                </div>

                            </div>


                            <div class="row">

                                <!-- Placa -->
                                <div class="col-md-6 mb-3">
                                    <label for="placa" class="form-label">Placa</label>

                                    <input
                                        type="text"
                                        name="placa"
                                        id="placa"
                                        class="form-control"
                                        maxlength="8"
                                        placeholder="ABC1D23"
                                        style="text-transform: uppercase;">
                                </div>
                            </div>


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
                                    Salvar cliente
                                </button>

                            </div>

                    </form>

                </div>

            </div>


            <!-- Listagem -->
            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">
                        Clientes Cadastrados
                    </h3>
                </div>

                <div class="card-body">

                    <?php if ($result_cliente->num_rows > 0): ?>

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped align-middle mb-0">

                                <thead>

                                    <tr>
                                        <th>Nome</th>
                                        <th>Telefone</th>
                                        <th>CPF</th>
                                        <th class="text-center">Ações</th>
                                    </tr>

                                </thead>


                                <tbody>

                                    <?php while ($cliente = $result_cliente->fetch_assoc()): ?>

                                        <tr>

                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $cliente['nome'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                                ?>
                                            </td>


                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $cliente['telefone'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                                ?>
                                            </td>


                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $cliente['cpf'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                                ?>
                                            </td>


                                            <td class="text-center">

                                                <div class="d-flex justify-content-center gap-2 flex-wrap">

                                                    <a
                                                        href="editar_cliente.php?id=<?php echo intval($cliente['id']); ?>"
                                                        class="btn btn-warning btn-sm">
                                                        Editar
                                                    </a>


                                                    <form
                                                        action="excluir_cliente.php?id=<?php echo intval($cliente['id']); ?>"
                                                        method="POST"
                                                        class="m-0">

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

                                                        <button
                                                            type="submit"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Deseja excluir este cliente?')">
                                                            Excluir
                                                        </button>

                                                    </form>

                                                </div>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="text-center text-muted py-4">

                            Nenhum cliente cadastrado.

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>
    </div>

</main>

<script>
    const telefone = document.getElementById('telefone');
    const cpf = document.getElementById('cpf');
    const placa = document.getElementById('placa');

    telefone.addEventListener('input', function() {
        let valor = telefone.value.replace(/\D/g, '');

        valor = valor.substring(0, 11);

        if (valor.length > 10) {
            valor = valor.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else {
            valor = valor.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        }

        telefone.value = valor;
    });


    cpf.addEventListener('input', function() {
        let valor = cpf.value.replace(/\D/g, '');

        valor = valor.substring(0, 11);

        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

        cpf.value = valor;
    });


    placa.addEventListener('input', function() {
        let valor = placa.value
            .replace(/[^a-zA-Z0-9]/g, '')
            .toUpperCase();

        valor = valor.substring(0, 7);

        placa.value = valor;
    });
</script>
<?php include 'includes/footer.php'; ?>