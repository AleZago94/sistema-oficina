<?php
require_once "includes/autenticacao.php";
require_once "config/conexao.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

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
        <div class="container-fluid">
            <form action="" method="POST">
                <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">

                <label for="nome" class="form-label">Nome</label>
                <input type="text" name="nome" id="nome" value="<?php echo $cliente['nome']; ?>" class="form-control">

                <label for="telefone" class="form-label">telefone:</label>
                <input type="text" name="telefone" id="telefone" value="<?php echo $cliente['telefone']; ?>" class="form-control">

                <label for="cpf" class="form-label">CPF</label>
                <input type="text" name="cpf" id="cpf" value="<?php echo $cliente['cpf']; ?>" class="form-control">

                <button type="submit" class="btn btn-primary mt-3">Salvar Alteraçao</button>

            </form>
        </div>
    </div>


</main>


<?php include "includes/footer.php"; ?>