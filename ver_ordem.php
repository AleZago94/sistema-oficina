<?php
require_once "includes/autenticacao.php";
require_once "config/conexao.php";

$ordem = null;
$result_item = null;

$tipo = 'entrada';

$origem = 'os';



if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if (isset($_GET['concluir'])) {

        $concluir_status = "SELECT status
                            FROM ordens_servico
                            WHERE id = $id";
        $result_concluir = $conn->query($concluir_status);
        $status_concluir = $result_concluir->fetch_assoc();

        $status_concluir = $status_concluir['status'];

        if ($status_concluir == 'aberta' || $status_concluir == 'em_andamento') {

            $status = "SELECT id
                  FROM movimentacoes_financeiras 
                  WHERE origem = 'os'
                  AND ordem_id = $id";

            $result = $conn->query($status);

            if ($result->num_rows > 0) {
            } else {

                $descricao = "OS #$id";
                $sql_update = "UPDATE ordens_servico 
                       SET status = 'concluida'
                       WHERE id = $id";

                $conn->query($sql_update);

                $sql_soma_os = " SELECT SUM(s.valor) AS total
        FROM ordem_servicos_itens osi
        JOIN servicos s ON osi.servico_id = s.id
        JOIN ordens_servico os ON osi.ordem_servico_id = os.id
        WHERE os.id = $id ";

                $soma = $conn->query($sql_soma_os);
                $soma_os = $soma->fetch_assoc();

                $os = $soma_os['total'] ?? 0;


                $insert_os = "INSERT INTO movimentacoes_financeiras (tipo, descricao, valor, origem, ordem_id) VALUES ('$tipo', '$descricao', '$os', '$origem', '$id')";

                $conn->query($insert_os);

                header("Location: ver_ordem.php?id=$id");
                exit;
            }
        } else {
            echo "nao é possivel concluir os ja concluida";
        }
    }

    if (isset($_GET['cancelar'])) {

        $cancela_status = "SELECT status
                           FROM ordens_servico
                           WHERE id = $id";

        $result_status = $conn->query($cancela_status);

        $status_cancelar = $result_status->fetch_assoc();
        $status_os = $status_cancelar['status'];

        if ($status_os == 'aberta' || $status_os == 'em_andamento') {
            $sql_cancelar = "UPDATE ordens_servico 
                         SET status = 'cancelada'
                         WHERE id = $id";

            $conn->query($sql_cancelar);

            header("Location: ver_ordem.php?id=$id");
            exit;
        } else {
            echo "nao é possivel cancelar os ja cancelada";
        }
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
            WHERE os.id = $id";

    $result = $conn->query($sql);
    $ordem = $result->fetch_assoc();

    $sql_item = "SELECT 
                    osi.*, 
                    servicos.nome AS os_nome,
                    servicos.valor
                 FROM ordem_servicos_itens osi
                 JOIN servicos ON osi.servico_id = servicos.id 
                 WHERE osi.ordem_servico_id = $id";

    $result_item = $conn->query($sql_item);
}
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