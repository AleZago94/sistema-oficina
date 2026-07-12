<?php
require_once "includes/autenticacao.php";

require_once "config/conexao.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $marca = trim($_POST['marca']);
    $modelo = trim($_POST['modelo']);
    $placa = trim($_POST['placa']);
    $ano = intval($_POST['ano']);

    if (empty($marca) || empty($modelo) || empty($placa) || empty($ano)) {
        echo "<script>alert('Preencha todos os campos');</script>";
        exit;
    }

    $sql_update = "UPDATE motos SET marca = ?, modelo = ?, placa = ?, ano = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sssii", $marca, $modelo, $placa, $ano, $id);

    if (!$stmt->execute()) {
        echo "erro na edicao";
        exit;
    }

    header("location: motos.php");
    exit;
}
if (!isset($_GET["id"])) {
    header("location: motos.php?erro=id_nao_encontrado");
    exit;
}
$id = intval($_GET['id']);

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
        <div class="container-fluid">
            <form action="" method="POST">

                <input type="hidden" name="id" value="<?php echo $motos['id']; ?>">

                <label for="marca" class="form-label">marca</label>
                <input type="text" name="marca" id="marca" value="<?php echo $motos['marca']; ?>" class="form-control">

                <label for="modelo" class="form-label">modelo</label>
                <input type="text" name="modelo" id="modelo" value="<?php echo $motos['modelo']; ?>" class="form-control">

                <label for="placa" class="form-label">placa</label>
                <input type="text" name="placa" id="placa" value="<?php echo $motos['placa']; ?>" class="form-control">

                <label for="ano" class="form-label">ano de fabricacao</label>
                <input type="text" name="ano" id="ano" value="<?php echo $motos['ano']; ?>" class="form-control">

                <button type="submit" class="btn btn-primary mt-3">Salvar Alterações</button>

            </form>

        </div>

    </div>



</main>

<?php include "includes/footer.php"; ?>