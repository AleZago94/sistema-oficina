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

if ($result['status'] === 'concluida' || $result['status'] === 'cancelada') {
    header("location: ver_ordem.php?id=$id&erro=ordem_nao_editavel");
    exit;
}



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
    //$modelo_moto = trim($_POST['modelo_moto']);
    $problema_relatado = trim($_POST['problema_relatado']);
    $valor_mao_obra = floatval($_POST['valor_mao_obra']);
    $valor_pecas = floatval($_POST['valor_pecas']);
    $status = trim($_POST['status']);
    $servicos = $_POST['servicos'] ?? [];

    $status_permitidos = ['aberta', 'em_andamento'];

    if (!in_array($status, $status_permitidos, true)) {
        header("location: editar_ordem.php?id=$id&erro=status_invalido");
        exit;
    }

    if (empty($problema_relatado) || empty($servicos)) {
        header("location: editar_ordem.php?id=$id&erro=campos_vazios");
        exit;
    }

    try {
        $conn->begin_transaction();

        $sql_update_ordem = "UPDATE ordens_servico 
    SET 
    problema_relatado = ?,
    valor_mao_obra = ?,
    valor_pecas = ?,
    status = ?
    WHERE id = ?";
        $stmt = $conn->prepare($sql_update_ordem);
        $stmt->bind_param("sddsi", $problema_relatado, $valor_mao_obra, $valor_pecas, $status, $id);
        $stmt->execute();

        $ordem_id = $id;
        // $conn->query($sql_update_ordem);
        $servicos_remover = array_diff($servicos_marcados, $servicos);

        $servicos_adicionar = array_diff($servicos, $servicos_marcados);

        foreach ($servicos_remover as $servico_id) {
            $servico_id = intval($servico_id);

            $sql_remover_servico =  "DELETE FROM ordem_servicos_itens
                                            WHERE ordem_servico_id = ?
                                            AND servico_id = ?";
            $stmt = $conn->prepare($sql_remover_servico);
            $stmt->bind_param("ii", $ordem_id, $servico_id);
            $stmt->execute();
        }



        foreach ($servicos_adicionar as $servico_id) {

            $servico_id = intval($servico_id);

            $sql_servico = "INSERT INTO ordem_servicos_itens
            (ordem_servico_id, servico_id) VALUES ( ? , ? )";
            $stmt = $conn->prepare($sql_servico);
            $stmt->bind_param("ii", $ordem_id, $servico_id);
            $stmt->execute();
        }
        $conn->commit();

        header("Location: ver_ordem.php?id=$ordem_id");
        exit;
    } catch (mysqli_sql_exception $erro) {

        $conn->rollback();
        header("location: editar_ordem.php?id=$id&erro=falha_editar_ordem");
        exit;
    }
}


include "includes/header.php";
include "includes/sidebar.php";
?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-1">
                        Editar Ordem de Serviço #<?php echo intval($result['id']); ?>
                    </h4>
                    <small class="text-muted">
                        Atualize os dados permitidos da ordem
                    </small>
                </div>

                <a
                    href="ver_ordem.php?id=<?php echo intval($result['id']); ?>"
                    class="btn btn-secondary btn-sm">
                    Voltar
                </a>
            </div>


            <form action="" method="POST">

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo intval($result['id']); ?>">


                <!-- Dados principais -->
                <div class="card mb-4">

                    <div class="card-header">
                        <h3 class="card-title">
                            Dados da Ordem
                        </h3>
                    </div>

                    <div class="card-body">

                        <!-- Cliente e Moto -->
                        <div class="row mb-4">

                            <div class="col-md-6 mb-3 mb-md-0">
                                <strong>Cliente:</strong>
                                <div class="mt-1">
                                    <?php
                                    echo htmlspecialchars(
                                        $result['nome_cliente'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <strong>Moto:</strong>
                                <div class="mt-1">
                                    <?php
                                    echo htmlspecialchars(
                                        $result['modelo_moto'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>
                                </div>
                            </div>

                        </div>


                        <!-- Problema relatado -->
                        <div class="mb-3">

                            <label
                                for="problema_relatado"
                                class="form-label">
                                Problema relatado
                            </label>

                            <textarea
                                name="problema_relatado"
                                id="problema_relatado"
                                class="form-control"
                                rows="3"><?php
                                            echo htmlspecialchars(
                                                $result['problema_relatado'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?></textarea>

                        </div>


                        <!-- Valores -->
                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label
                                    for="valor_mao_obra"
                                    class="form-label">
                                    Valor da mão de obra
                                </label>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="valor_mao_obra"
                                    id="valor_mao_obra"
                                    value="<?php echo htmlspecialchars(
                                                $result['valor_mao_obra'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>"
                                    class="form-control">

                            </div>


                            <div class="col-md-6 mb-3">

                                <label
                                    for="valor_pecas"
                                    class="form-label">
                                    Valor das peças
                                </label>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="valor_pecas"
                                    id="valor_pecas"
                                    value="<?php echo htmlspecialchars(
                                                $result['valor_pecas'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>"
                                    class="form-control">

                            </div>

                        </div>


                        <!-- Status -->
                        <div class="mb-3">

                            <label
                                for="status"
                                class="form-label">
                                Status
                            </label>

                            <select
                                name="status"
                                id="status"
                                class="form-control">

                                <option
                                    value="aberta"
                                    <?php
                                    if ($result['status'] === 'aberta') {
                                        echo 'selected';
                                    }
                                    ?>>
                                    Aberta
                                </option>

                                <option
                                    value="em_andamento"
                                    <?php
                                    if ($result['status'] === 'em_andamento') {
                                        echo 'selected';
                                    }
                                    ?>>
                                    Em andamento
                                </option>

                            </select>

                        </div>

                    </div>

                </div>


                <!-- Serviços -->
                <div class="card mb-4">

                    <div class="card-header">
                        <h3 class="card-title">
                            Serviços
                        </h3>
                    </div>

                    <div class="card-body">

                        <div class="pachecos-servicos-box">

                            <div class="row">

                                <?php while ($servico = $result_servicos->fetch_assoc()): ?>

                                    <div class="col-md-6">

                                        <div class="form-check mb-2">

                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="servicos[]"
                                                value="<?php echo intval($servico['id']); ?>"
                                                id="servico_<?php echo intval($servico['id']); ?>"
                                                <?php
                                                if (
                                                    in_array(
                                                        $servico['id'],
                                                        $servicos_marcados
                                                    )
                                                ) {
                                                    echo 'checked';
                                                }
                                                ?>>

                                            <label
                                                class="form-check-label"
                                                for="servico_<?php echo intval($servico['id']); ?>">

                                                <?php
                                                echo htmlspecialchars(
                                                    $servico['nome'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                                ?>

                                                -

                                                R$
                                                <?php
                                                echo number_format(
                                                    $servico['valor'],
                                                    2,
                                                    ',',
                                                    '.'
                                                );
                                                ?>

                                            </label>

                                        </div>

                                    </div>

                                <?php endwhile; ?>

                            </div>

                        </div>

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


                <!-- Ações -->
                <div class="d-flex justify-content-end gap-2">

                    <a
                        href="ver_ordem.php?id=<?php echo intval($result['id']); ?>"
                        class="btn btn-secondary">
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary">
                        Salvar edição
                    </button>

                </div>

            </form>

        </div>
    </div>
</main>




<?php include "includes/footer.php" ?>