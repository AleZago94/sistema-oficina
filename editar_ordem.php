<?php
require_once "includes/autenticacao.php";

require_once "config/conexao.php";
require_once "includes/helpers.php";

if (!isset($_GET['id'])) {
    header("location: ordens.php?erro=ordem_nao_encontrada");
    exit;
}
$id = intval($_GET['id']);

$sql_ordem = "SELECT  
    os.id,
    os.problema_relatado,
    os.valor_mao_obra,
    os.valor_pecas,
    os.status,
    motos.modelo AS modelo_moto, 
    c.nome AS nome_cliente
    FROM ordens_servico AS os
    JOIN clientes c on os.cliente_id = c.id
    JOIN motos  on os.moto_id =  motos.id 
    WHERE os.id = ?  ";
$stmt = $conn->prepare($sql_ordem);
$stmt->bind_param("i", $id);

if (!$stmt->execute()) {
    header("location: ordens.php?erro=id_nao_encontrado");
    exit;
}
$ordem = $stmt->get_result();

if ($ordem->num_rows == 0) {
    header("location: ordens.php?erro=ordem_nao_encontrada");
    exit;
}
$result = $ordem->fetch_assoc();



$servicos_existentes = "SELECT servico_id
FROM ordem_servicos_itens
WHERE ordem_servico_id = ?";
$stmt = $conn->prepare($servicos_existentes);
$stmt->bind_param("i", $id);

if (!$stmt->execute()) {
    header("location: ordens.php?erro=servicos_nao_encontrados");
    exit;
}

$result_existentes = $stmt->get_result();

if ($result_existentes->num_rows == 0) {
    header("location: ordens.php?erro=servicos_nao_encontrados");
    exit;
}

$servicos_marcados = [];

while ($servico_check = $result_existentes->fetch_assoc()) {
    $servicos_marcados[] = $servico_check['servico_id'];
}

$servicos = "SELECT * FROM servicos ORDER BY nome ASC";
$result_servicos = $conn->query($servicos);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!validarCsrf()) {
        header("location: ordens.php?erro=token_invalido");
        exit;
    }

    $id = intval($_POST['id']);
    $cliente_id = trim($_POST['cliente_id']);
    //$modelo_moto = trim($_POST['modelo_moto']);
    $problema_relatado = trim($_POST['problema_relatado']);
    $valor_mao_obra = floatval($_POST['valor_mao_obra']);
    $valor_pecas = floatval($_POST['valor_pecas']);
    $status = trim($_POST['status']);
    $servicos = $_POST['servicos'] ?? [];

    if (empty($cliente_id)  || empty($problema_relatado)) {
        header("location: editar_ordem.php?erro=campos_vazios");
        exit;
    }
    $sql_update_ordem = "UPDATE ordens_servico 
    SET 
    problema_relatado = ?,
    valor_mao_obra = ?,
    valor_pecas = ?,
    status = ?
    WHERE id = ?";
    $stmt = $conn->prepare($sql_update_ordem);
    $stmt->bind_param("sddsi", $problema_relatado, $valor_mao_obra, $valor_pecas, $status, $id);

    if (!$stmt->execute()) {
        header("location: ordens.php?erro=falha_atualizar_orden");
        exit;
    }
    // $conn->query($sql_update_ordem);

    $ordem_id = $id;

    foreach ($servicos as $servico_id) {
        if (!in_array($servico_id, $servicos_marcados)) {
            $servico_id = intval($servico_id);

            $sql_servico = "INSERT INTO ordem_servicos_itens
            (ordem_servico_id, servico_id) VALUES ( ? , ? )";
            $stmt = $conn->prepare($sql_servico);
            $stmt->bind_param("ii", $ordem_id, $servico_id);

            if (!$stmt->execute()) {
                header("location: ordens.php?erro=falha_insercao");
                exit;
            }
        }
    }
    header("Location: ver_ordem.php?id=$ordem_id");
    exit;
}


include "includes/header.php";
include "includes/sidebar.php";
?>

<main class="app-main">
    <div class="app-content">
        <div class="conatainer-fluid">

            <form action="" method="POST">
                <input type="hidden" name="id" value="<?php echo intval($result['id']); ?>">
                <div class="mb-3">
                    <label for="cliente_id" class="form-label">cliente</label>
                    <input type="text" name="cliente_id" id="cliente_id" value="<?php echo htmlspecialchars($result['nome_cliente'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control"> <br>
                </div>

                <div class="mb-3">
                    <label for="modelo_moto" class="form-label">modelo da moto <?php echo  htmlspecialchars($result['modelo_moto'], ENT_QUOTES, 'UTF-8'); ?> </label>
                    <!-- <input type="text" name="modelo_moto" id="modelo_moto" value=""> -->
                </div>

                <div class="mb-3">
                    <label for="problema_relatada" class="form-label"> problema relatado </label>
                    <input type="text" name="problema_relatado" id="problema_relatado" value="<?php echo htmlspecialchars($result['problema_relatado'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="mb-3">
                    <label for="valor_mao_obra" class="form-label"> valor mao da mao de obra </label>
                    <input type="number" step="0.01" name="valor_mao_obra" id="valor_mao_obra" value="<?php echo htmlspecialchars($result['valor_mao_obra'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control">

                </div>

                <div class="mb-3">
                    <label for="valor_pecas" class="form-label"> valor das pecas </label>
                    <input type="number" step="0.01" name="valor_pecas" id="valor_pecas" value="<?php echo    htmlspecialchars($result['valor_pecas'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label"></label>
                    <select name="status" id="status" class="form-control">
                        <option value="aberta">aberto</option>
                        <option value="em_andamento">andamento</option>
                        <option value="concluida">concluido</option>
                        <option value="cancelada">cancelada</option>
                    </select>
                </div>

                <label class="form-label">servicos</label>

                <?php while ($servico = $result_servicos->fetch_assoc()): ?>


                    <div class="form-check mb-2">
                        <input class="form-check-input"
                            type="checkbox"
                            name="servicos[]"
                            <?php if (in_array($servico['id'], $servicos_marcados)) echo "checked"; ?>
                            value="<?php echo intval($servico['id']); ?>"
                            id="servico_<?php echo intval($servico['id']); ?>">
                        <label
                            class="form-check-label"
                            for="servico_<?php echo intval($servico['id']); ?>">

                            <?php echo    htmlspecialchars($servico['nome'], ENT_QUOTES, 'UTF-8'); ?>
                            -
                            R$ <?php echo number_format($servico['valor'], 2, ',', '.'); ?>

                        </label>
                    </div>
                <?php endwhile; ?>

                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">


                <button type="submit" class="btn btn-primary mt-3">Salvar edicao </button>

            </form>

        </div>

    </div>

</main>




<?php include "includes/footer.php" ?>