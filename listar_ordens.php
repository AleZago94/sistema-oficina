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

            <!-- Cabeçalho -->
            <div class="d-flex justify-content-between align-items-center mb-3">

                <h4 class="mb-0">Ordens de Serviço</h4>

                <a href="ordens.php" class="btn btn-primary btn-sm me-1">
                    Nova OS
                </a>

            </div>


            <!-- Tabela -->
            <div class="card">

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped text-nowrap">

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

                                        <td>
                                            <?php echo intval($ordem['id']); ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $ordem['nome_cliente'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $ordem['marca'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );

                                            echo ' ';

                                            echo htmlspecialchars(
                                                $ordem['modelo'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $ordem['placa'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </td>


                                        <?php

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

                                            default:
                                                $classe_status = "bg-secondary";
                                        }

                                        ?>


                                        <td>

                                            <span class="badge <?php echo $classe_status; ?>">

                                                <?php
                                                echo htmlspecialchars(
                                                    $ordem['status'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                                ?>

                                            </span>

                                        </td>


                                        <td>
                                            <?php
                                            echo date(
                                                'd/m/Y H:i',
                                                strtotime($ordem['created_at'])
                                            );
                                            ?>
                                        </td>


                                        <td>

                                            <a
                                                href="ver_ordem.php?id=<?php echo intval($ordem['id']); ?>"
                                                class="btn btn-primary btn-sm">
                                                Ver
                                            </a>


                                            <?php if (
                                                $ordem['status'] == 'aberta' ||
                                                $ordem['status'] == 'em_andamento'
                                            ): ?>

                                                <a
                                                    href="editar_ordem.php?id=<?php echo intval($ordem['id']); ?>"
                                                    class="btn btn-warning btn-sm">
                                                    Editar
                                                </a>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>
    </div>

</main>
<?php include "includes/footer.php"; ?>