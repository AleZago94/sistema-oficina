<?php
require_once "includes/autenticacao.php";
require_once "config/conexao.php";

if (isset($_GET["erro"])) {

    switch ($_GET["erro"]) {

        case "falha_obter_status":
            echo "<script>alert('status nao econtrado')</script>";
            break;

        case "ordem_nao_encontrada":
            echo "<script>alert('ordem nao econtrada')</script>";
            break;

        case "falha_verificar_movimentacao":
            echo "<script>alert('falha na movimentacao')</script>";
            break;


        case "movimentacao_ja_existe":
            echo "<script>alert('ja existe uma movimentacao')</script>";
            break;

        case "falha_ao_calcular":
            echo "<script>alert('falha ao somar OS')</script>";
            break;

        case "falha_inserir_movimentacao":
            echo "<script>alert('falha ao inserir a movimentacao')</script>";
            break;

        case "falha_update":
            echo "<script>alert('falha ao atualizar')</script>";
            break;

        case "OS_concluida":
            echo  "<script>alert('nao é possivel concluir. OS ja concluida')</script>";
            break;

        case "falha_cancelar_OS":
            echo "<script>alert('nao é possivel cancelar os ja cancelada')</script>";
            break;
    }
}


if (isset($_GET["sucesso"])) {
    switch ($_GET["sucesso"]) {

        case "ordem_cancelada":
            echo "<script>alert('OS cancelada')</script>";
            break;

        case "cadastro_efetuado":
            echo "<script>alert('cadastro efetuado com sucesso')</script>";
            break;
    }
}
$ordem = null;
$result_item = null;

$tipo = 'entrada';

$origem = 'os';



if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if (isset($_GET['concluir'])) {

        $concluir_status = "SELECT status
                            FROM ordens_servico
                            WHERE id = ?";

        $stmt_concluir = $conn->prepare($concluir_status);
        $stmt_concluir->bind_param("i", $id);

        if (!$stmt_concluir->execute()) {
            header("location: ver_ordem.php?id=$id&erro=falha_obter_status");
            exit;
        }
        $result_concluir = $stmt_concluir->get_result();
        $status_concluir = $result_concluir->fetch_assoc();

        if (!$status_concluir) {
            header("location: ver_ordem.php?id=$id&erro=ordem_nao_encontrada");
            exit;
        }

        $status_concluir = $status_concluir['status'];

        if ($status_concluir !== 'aberta' && $status_concluir !== 'em_andamento') {
            header("location: ver_ordem.php?id=$id&erro=OS_concluida");
            exit;
        }

        $sql_movimentacao = "SELECT id
                  FROM movimentacoes_financeiras 
                  WHERE origem = 'os'
                  AND ordem_id = ?";

        $stmt_movimentacao = $conn->prepare($sql_movimentacao);
        $stmt_movimentacao->bind_param("i", $id);

        if (!$stmt_movimentacao->execute()) {
            header("location: ver_ordem.php?id=$id&erro=falha_verificar_movimentacao");
            exit;
        }

        $result_movimentacao = $stmt_movimentacao->get_result();

        if ($result_movimentacao->num_rows > 0) {
            header("location: ver_ordem.php?id=$id&erro=movimentacao_ja_existe");
            exit;
        }


        $descricao = "OS #$id";
        $sql_update = "UPDATE ordens_servico 
                       SET status = 'concluida'
                       WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $id);

        if (!$stmt_update->execute()) {
            header("location: ver_ordem.php?id=$id&erro=falha_update");
            exit;
        }


        $sql_soma_os = " SELECT SUM(s.valor) AS total
        FROM ordem_servicos_itens osi
        JOIN servicos s ON osi.servico_id = s.id
        JOIN ordens_servico os ON osi.ordem_servico_id = os.id
        WHERE os.id = ? ";

        $stmt_soma_os = $conn->prepare($sql_soma_os);
        $stmt_soma_os->bind_param("i", $id);

        if (!$stmt_soma_os->execute()) {
            header("location: ver_ordem.php?id=$id&erro=falha_ao_calcular");
            exit;
        }
        $soma = $stmt_soma_os->get_result();
        $soma_os = $soma->fetch_assoc();

        $os = $soma_os['total'] ?? 0;


        $insert_os = "INSERT INTO movimentacoes_financeiras (tipo, descricao, valor, origem, ordem_id) VALUES (?, ?, ?, ?, ?)";

        $stmt_insert_os = $conn->prepare($insert_os);
        $stmt_insert_os->bind_param("ssdsi", $tipo, $descricao, $os, $origem, $id);

        if (!$stmt_insert_os->execute()) {
            header("location: ver_ordem.php?id=$id&erro=falha_inserir_movimentacao");
            exit;
        }

        header("Location: ver_ordem.php?id=$id&sucesso=cadastro_efetuado");
        exit;
    }


    if (isset($_GET['cancelar'])) {

        $cancela_status = "SELECT status
                           FROM ordens_servico
                           WHERE id = ?";

        $stmt_cancela_status = $conn->prepare($cancela_status);
        $stmt_cancela_status->bind_param("i", $id);

        if (!$stmt_cancela_status->execute()) {
            header("location: ver_ordem.php?id=$id&erro=falha_obter_status");
            exit;
        }

        $result_status = $stmt_cancela_status->get_result();

        if ($result_status->num_rows <= 0) {
            header("location: ver_ordem.php?id=$id&erro=ordem_nao_encontrada");
            exit;
        }

        $status_cancelar = $result_status->fetch_assoc();
        $status_os = $status_cancelar['status'];

        if ($status_os !== 'aberta' && $status_os !== 'em_andamento') {

            header("location: ver_ordem.php?id=$id&erro=falha_cancelar_OS");
            exit;
        }
        $sql_cancelar = "UPDATE ordens_servico 
                         SET status = 'cancelada'
                         WHERE id = $id";

        $stmt_cancelar_os = $conn->prepare($sql_cancelar);
        $stmt_cancelar_os->bind_param("i", $id);

        if (!$stmt_cancelar_os->execute()) {
            header("location: ver_ordem.php?id=$id&erro=falha_cancelar_os");
            exit;
        }

        header("Location: ver_ordem.php?id=$id&sucesso=ordem_cancelada");
        exit;
    } //else {
    //echo "nao é possivel cancelar os ja cancelada";
    // }
}




// buscar OS sempre que tiver id
$sql = "SELECT os.id,
                   os.status,
                   os.problema_relatado,
                   os.created_at,
                   c.nome AS cliente_nome,
                   c.telefone,
                   m.modelo AS modelo_moto,
                   m.placa
            FROM ordens_servico os
            JOIN clientes c ON os.cliente_id = c.id
            JOIN motos m ON os.moto_id = m.id
            WHERE os.id = ?";

$stmt_buscar_os = $conn->prepare($sql);
$stmt_buscar_os->bind_param("i", $id);

if (!$stmt_buscar_os->execute()) {
    header("location: ver_ordem.php?id=$id&erro=falha_ober_id");
    exit;
}


$result = $stmt_buscar_os->get_result();

if ($result->num_rows <= 0) {
    header("location: ver_ordem.php?id=$id&erro=falha_obter_dados");
    exit;
}

$ordem = $result->fetch_assoc();

$sql_item = "SELECT 
                    osi.*, 
                    servicos.nome AS os_nome,
                    servicos.valor
                 FROM ordem_servicos_itens osi
                 JOIN servicos ON osi.servico_id = servicos.id 
                 WHERE osi.ordem_servico_id = ?";

$stmt_item = $conn->prepare($sql_item);
$stmt_item->bind_param("i", $id);

if (!$stmt_item->execute()) {
    header("location: ver_ordem.php?id=$id&erro=items_nao_econtrados");
    exit;
}

$result_item = $stmt_item->get_result();

include "includes/header.php";
include "includes/sidebar.php";
?>


<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            <?php if ($ordem): ?>

                <!-- card da OS aqui -->
                <div class="card-header">
                    <h3 class="card-title">OS # <?php echo $ordem['id']; ?></h3>
                </div>
                <div class="card-body">
                    <p><strong>Cliente</strong> <?php echo $ordem['cliente_nome']; ?></p>
                    <p><strong>Telefone</strong><?php echo $ordem['telefone']; ?></p>
                    <p><strong>Moto</strong><?php echo $ordem['modelo_moto']; ?></p>
                    <p><strong>Placa</strong><?php echo $ordem['placa']; ?></p>
                    <p><strong>Status</strong><?php echo $ordem['status']; ?></p>
                    <p><strong>Problema relatado</strong><?php echo $ordem['problema_relatado']; ?></p>

                </div>
                <!-- tabela de serviços aqui -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Serviços da OS</h3>
                    </div>

                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Serviço</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php while ($item = $result_item->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $item['os_nome']; ?></td>
                                        <td>R$ <?php echo number_format($item['valor'], 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- botão imprimir aqui -->
                <a
                    href="imprimir_os.php?id=<?php echo $ordem['id']; ?>"
                    target="_blank"
                    class="btn btn-secondary">
                    Imprimir Comanda
                </a>
                <?php if ($ordem['status'] != 'concluida' && $ordem['status'] != 'cancelada'):  ?>
                    <a
                        href="ver_ordem.php?id=<?php echo $ordem['id']; ?>&concluir=1" class="btn btn-success" onclick="return confirm('Deseja concluir esta OS?')">
                        Concluir OS
                    </a>
                <?php endif ?>

                <?php if ($ordem['status'] != 'cancelada' && $ordem['status'] == 'aberta'): ?>
                    <a href="ver_ordem.php?id=<?php echo $ordem['id']; ?>&cancelar=1" class="btn btn-danger" onclick="return confirm('Cancelar OS')"> cancelar OS </a>
                <?php endif ?>

            <?php else: ?>

                <p>Nenhuma ordem encontrada.</p>

            <?php endif; ?>

        </div>
    </div>
</main>




<?php include "includes/footer.php"; ?>