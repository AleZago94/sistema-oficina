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

        case "falha_ao_preparar_atualizacao":
            echo "<script>alert('houve uma falha ao tentar atualizar OS tente novamente ou contate ADM')</script>";
            break;

        case "falha_ao_preparar_soma":
            echo "<script>alert('falha ao somar tente novamente ou contate ADM')</script>";
            break;

        case "falha_ao_preparar_movimentacao":
            echo "<script>alert('falha na movimentacao tente novamente ou contate ADM')</script>";
            break;

        case "id_nao_encontrado":
            echo "<script>alert('erro ao obter ID')</script>";
            break;

        case "id_invalido":
            echo "<script>alert('ID invalido')</script>";
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



if (!isset($_GET['id'])) {
    header("location: ordens.php?erro=id_nao_encontrado");
    exit;
}

$id = intval($_GET['id']);

if ($id <= 0) {
    header("location: ordens.php?erro=id_invalido");
    exit;
}


if (isset($_POST['concluir']) && $_POST['concluir'] === '1') {


    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo "token invalido";
        exit;
    }


    // if (isset($_GET['concluir'])) {

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

    try {
        $conn->begin_transaction();

        $sql_update = "UPDATE ordens_servico 
                       SET status = 'concluida'
                       WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);

        //   if ($stmt_update === false) {
        //     $conn->rollback();
        //   header("location: ver_ordem.php?id=$id&erro=falha_ao_preparar_atualizacao");
        // exit;
        // }

        $stmt_update->bind_param("i", $id);
        $stmt_update->execute();

        //if (!$stmt_update->execute()) {
        //    $conn->rollback();
        //      header("location: ver_ordem.php?id=$id&erro=falha_update");
        //      exit;
        //    }


        $sql_soma_os = " SELECT SUM(s.valor) AS total
        FROM ordem_servicos_itens osi
        JOIN servicos s ON osi.servico_id = s.id
        JOIN ordens_servico os ON osi.ordem_servico_id = os.id
        WHERE os.id = ? ";

        $stmt_soma_os = $conn->prepare($sql_soma_os);

        //  if ($stmt_soma_os === false) {
        //       $conn->rollback();
        //     header("location: ver_ordem.php?id=$id&erro=falha_ao_preparar_soma");
        //         exit;
        //    }
        $stmt_soma_os->bind_param("i", $id);
        $stmt_soma_os->execute();

        // if (!$stmt_soma_os->execute()) {
        //     $conn->rollback();
        //     header("location: ver_ordem.php?id=$id&erro=falha_ao_calcular");
        //     exit;
        // }
        $soma = $stmt_soma_os->get_result();
        $soma_os = $soma->fetch_assoc();

        $os = $soma_os['total'] ?? 0;


        $insert_os = "INSERT INTO movimentacoes_financeiras (tipo, descricao, valor, origem, ordem_id) VALUES (?, ?, ?, ?, ?)";

        $stmt_insert_os = $conn->prepare($insert_os);

        //   if ($stmt_insert_os === false) {
        //     $conn->rollback();
        //      header("location: ver_ordem.php?id=$id&erro=falha_ao_preparar_movimentacao");
        //       exit;
        //       }
        $stmt_insert_os->bind_param("ssdsi", $tipo, $descricao, $os, $origem, $id);
        $stmt_insert_os->execute();

        //  if (!$stmt_insert_os->execute()) {
        //       $conn->rollback();
        //         header("location: ver_ordem.php?id=$id&erro=falha_inserir_movimentacao");
        //       exit;
        //     }

        $conn->commit();

        header("Location: ver_ordem.php?id=$id&sucesso=cadastro_efetuado");
        exit;
    } catch (mysqli_sql_exception $erro) {

        $conn->rollback();
        header("location: ver_ordem.php?id=$id&erro=falha_inserir_movimentacao");
        exit;
    }
    //}
}

// if (isset($_GET['cancelar'])) 
if (isset($_POST['cancelar']) && $_POST['cancelar'] === '1') {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo "token invalido";
        exit;
    }

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
                         WHERE id = ?";

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
    header("location: ordens.php?id=$id&erro=falha_obter_dados");
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
                    <h3 class="card-title">OS # <?php echo intval($ordem['id']); ?></h3>
                </div>
                <div class="card-body">
                    <p><strong>Cliente</strong> <?php echo htmlspecialchars($ordem['cliente_nome'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Telefone</strong><?php echo htmlspecialchars($ordem['telefone'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Moto</strong><?php echo htmlspecialchars($ordem['modelo_moto'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Placa</strong><?php echo htmlspecialchars($ordem['placa'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Status</strong><?php echo htmlspecialchars($ordem['status'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Problema relatado</strong><?php echo htmlspecialchars($ordem['problema_relatado'], ENT_QUOTES, 'UTF-8'); ?></p>


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
                                        <td><?php echo htmlspecialchars($item['os_nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>R$ <?php echo number_format($item['valor'], 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- botão imprimir aqui -->
                <a
                    href="imprimir_os.php?id=<?php echo intval($ordem['id']); ?>"
                    target="_blank"
                    class="btn btn-secondary">
                    Imprimir Comanda
                </a>
                <?php if ($ordem['status'] != 'concluida' && $ordem['status'] != 'cancelada'):  ?>
                    <!-- <a
                        href="ver_ordem.php?id=<?php echo intval($ordem['id']); ?>&concluir=1" class="btn btn-success" onclick="return confirm('Deseja concluir esta OS?')">
                        Concluir OS
                    </a> -->
                    <form method="POST" action="ver_ordem.php?id=<?php echo intval($ordem['id']); ?>">
                        <input type="hidden" name="concluir" value="1">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                        <button class="btn btn-success" onclick="return confirm('Deseja concluir esta OS')">Concluir OS</button>

                    </form>
                <?php endif ?>

                <?php if ($ordem['status'] != 'cancelada' && $ordem['status'] == 'aberta'): ?>

                    <form method="POST" action="ver_ordem.php?id=<?php echo intval($ordem['id']); ?>">
                        <input type="hidden" name="cancelar" value="1">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                        <button type="submit" class="btn btn-danger" onclick="return confirm('Cancelar OS')"> Cancelar OS</button>
                    </form>
                <?php endif ?>

            <?php else: ?>

                <p>Nenhuma ordem encontrada.</p>

            <?php endif; ?>

        </div>
    </div>
</main>




<?php include "includes/footer.php"; ?>