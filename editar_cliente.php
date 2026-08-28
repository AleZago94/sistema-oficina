<?php
require_once "includes/autenticacao.php";
require_once "config/conexao.php";
require_once "includes/helpers.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!validarCsrf()) {
        header("location: clientes.php?erro=token_invalido");
        exit;
    }


    $id = intval($_POST['id']);
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $cpf = trim($_POST['cpf']);


    if (empty($nome) || empty($telefone) ||  empty($cpf)) {
        echo "nao existe usuario para editar";
        exit;
    }


    $sql_update = "UPDATE clientes SET nome = ?, telefone = ?, cpf = ?  WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sssi", $nome, $telefone, $cpf, $id);


    if (!$stmt->execute()) {
        echo "Erro ao atualizar.";
        exit;
    }
    header("Location: clientes.php");
    exit;
}
if (!isset($_GET["id"])) {
    header("Location: clientes.php?erro=cliente_nao_encontrado");
    exit;
}


$id = intval($_GET["id"]);

$sql = "SELECT * FROM  clientes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();


$resultado =  $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: clientes.php?erro=cliente_nao_encontrado");
    exit;
}


$cliente = $resultado->fetch_assoc();




include "includes/header.php";
include "includes/sidebar.php";

?>
<main class="app-main">

    <div class="app-content">
        <div class="container-fluid py-4">

            <!-- Cabeçalho -->
            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>
                    <h4 class="mb-1">Editar Cliente</h4>

                    <small class="text-muted">
                        Atualize os dados do cliente
                    </small>
                </div>

                <a
                    href="clientes.php"
                    class="btn btn-secondary btn-sm">
                    Voltar
                </a>

            </div>


            <!-- Card -->
            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">
                        Dados do Cliente
                    </h3>
                </div>


                <div class="card-body">

                    <form action="" method="POST">

                        <input
                            type="hidden"
                            name="id"
                            value="<?php echo intval($cliente['id']); ?>">


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
                                    value="<?php
                                            echo htmlspecialchars(
                                                $cliente['nome'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>"
                                    class="form-control">

                            </div>


                            <!-- Telefone -->
                            <div class="col-md-6 mb-3">

                                <label
                                    for="telefone"
                                    class="form-label">
                                    Telefone
                                </label>

                                <input
                                    type="text"
                                    name="telefone"
                                    id="telefone"
                                    value="<?php
                                            echo htmlspecialchars(
                                                $cliente['telefone'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>"
                                    class="form-control">

                            </div>

                        </div>


                        <div class="row">

                            <!-- CPF -->
                            <div class="col-md-6 mb-3">

                                <label
                                    for="cpf"
                                    class="form-label">
                                    CPF
                                </label>

                                <input
                                    type="text"
                                    name="cpf"
                                    id="cpf"
                                    value="<?php
                                            echo htmlspecialchars(
                                                $cliente['cpf'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>"
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


                        <!-- Ações -->
                        <div class="d-flex justify-content-end gap-2 mt-3">

                            <a
                                href="clientes.php"
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