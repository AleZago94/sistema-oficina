<?php
require_once "config/conexao.php";
require_once "includes/autenticacao.php";
require_once "includes/helpers.php";

$listar_ordens = "SELECT 
    os.id,
    os.status,
    os.created_at,
    c.nome AS nome_cliente,
    m.marca,
    m.modelo,
    m.placa


FROM ordens_servico os
JOIN clientes c ON os.cliente_id = c.id
JOIN motos m ON os.moto_id = m.id
ORDER BY os.id DESC";

$result_ordens = $conn->query($listar_ordens);

include "includes/header.php";
include "includes/sidebar.php";

?>
<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>OS</th>
                        <th>Cliente</th>
                        <th>Moto</th>
                        <th>Placa</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($ordem = $result_ordens->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $ordem['id']; ?></td>
                            <td><?php echo $ordem['nome_cliente']; ?></td>
                            <td><?php echo $ordem['marca'] . ' ' . $ordem['modelo']; ?></td>
                            <td><?php echo $ordem['placa']; ?></td>

                            <?php $ordem['status'];

                            switch ($ordem['status']) {
                                case "aberta":
                                    $classe_status = "bg-warning text-dark";
                                    break;

                                case "em_andamento":
                                    $classe_status = "bg-primary";
                                    break;

                                case "concluida":
                                    $classe_status = "bg-success";
                                    break;

                                case "cancelada":
                                    $classe_status = "bg-danger";
                                    break;
                            }
                            ?>
                            <td>
                                <span class="badge <?php echo $classe_status; ?>"><?php echo $ordem['status']; ?>
                                </span>
                            </td>




                            <td><?php echo date('d/m/Y H:i', strtotime($ordem['created_at'])); ?></td>
                            <td><a href="ver_ordem.php?id=<?php echo $ordem['id']; ?>" class="btn btn-primary btn-sm"> ver</a></td>

                        </tr>
                    <?php endwhile; ?>

                </tbody>
            </table>

        </div>

    </div>


</main>
<?php include "includes/footer.php"; ?>