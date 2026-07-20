<?php
require_once "includes/autenticacao.php";
require_once "config/conexao.php";

include "includes/header.php";
include "includes/sidebar.php";

switch ($_GET["sucesso"]) {

  case "usuario_autenticado":
    echo "<script>alert('Olá, " . $_SESSION['usuario_nome'] . "! ')</script>";
    break;
}

$sql_clientes = "SELECT COUNT(*) AS total FROM clientes";
$total_clientes = $conn->query($sql_clientes)->fetch_assoc()['total'];

$sql_motos = "SELECT COUNT(*) AS total FROM motos";
$total_moto = $conn->query($sql_motos)->fetch_assoc()['total'];

$sql_servicos = "SELECT COUNT(*) AS total FROM servicos";
$total_servicos = $conn->query($sql_servicos)->fetch_assoc()['total'];

$sql_os_aberta = "SELECT COUNT(*) AS total FROM ordens_servico WHERE status = 'aberta'";
$total_os_aberta = $conn->query($sql_os_aberta)->fetch_assoc()['total'];

$sql_os_andamento = "SELECT COUNT(*) AS total FROM ordens_servico WHERE status = 'em_andamento'";
$total_os_andamento = $conn->query($sql_os_andamento)->fetch_assoc()['total'];

$sql_os_concluida = "SELECT COUNT(*) AS total FROM ordens_servico WHERE status = 'concluida'";
$total_os_concluidas = $conn->query($sql_os_concluida)->fetch_assoc()['total'];

$sql_os_canceladas = "SELECT COUNT(*) AS total FROM ordens_servico WHERE status = 'cancelada'";
$total_canceladas = $conn->query($sql_os_canceladas)->fetch_assoc()['total'];

$sql_ultimas_os = "SELECT 
    os.id,
    os.status,
    clientes.nome AS nome_cliente,
    motos.modelo AS modelo_moto,
    motos.placa
FROM ordens_servico os
JOIN clientes ON os.cliente_id = clientes.id
JOIN motos ON os.moto_id = motos.id
ORDER BY os.id DESC
LIMIT 5";

$result_ultimas_os = $conn->query($sql_ultimas_os);
?>

<main class="app-main">
  <div class="app-content">
    <div class="container-fluid py-4">

      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-primary">
            <div class="inner">
              <h3><?php echo $total_clientes; ?></h3>
              <p>Clientes</p>
            </div>
            <a href="clientes.php" class="small-box-footer">Ver clientes</a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-success">
            <div class="inner">
              <h3><?php echo $total_moto; ?></h3>
              <p>Motos</p>
            </div>
            <a href="motos.php" class="small-box-footer">Ver motos</a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-warning">
            <div class="inner">
              <h3><?php echo $total_servicos; ?></h3>
              <p>Serviços</p>
            </div>
            <a href="servicos.php" class="small-box-footer">Ver serviços</a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-danger">
            <div class="inner">
              <h3><?php echo $total_os_aberta; ?></h3>
              <p>OS abertas</p>
            </div>
            <a href="ordens.php" class="small-box-footer">Ver ordens</a>

          </div>
        </div>
      </div>

      <div class="row mt-4">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Últimas Ordens de Serviço</h3>
            </div>

            <div class="card-body">
              <?php if ($result_ultimas_os->num_rows > 0): ?>
                <table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>OS</th>
                      <th>Cliente</th>
                      <th>Moto</th>
                      <th>Placa</th>
                      <th>Status</th>
                    </tr>
                  </thead>

                  <tbody>
                    <?php while ($os = $result_ultimas_os->fetch_assoc()): ?>
                      <tr>
                        <td>#<?php echo $os['id']; ?></td>
                        <td><?php echo $os['nome_cliente']; ?></td>
                        <td><?php echo $os['modelo_moto']; ?></td>
                        <td><?php echo $os['placa']; ?></td>
                        <td><?php echo $os['status']; ?></td>
                        <td> <a href="ver_ordem.php?id=<?php echo $os['id']; ?>" class="btn btn-primary btn-sm">Ver</a></td>
                        <td><a href="editar_ordem.php?id=<?php echo $os['id']; ?>" class="btn btn-primary btn-sm">Editar</a></td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              <?php else: ?>
                <p>Nenhuma OS cadastrada ainda.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Resumo das OS</h3>
            </div>

            <div class="card-body">
              <p>Abertas: <?php echo $total_os_aberta; ?></p>
              <p>Em andamento: <?php echo $total_os_andamento; ?></p>
              <p>Concluídas: <?php echo $total_os_concluidas; ?></p>
              <p>caceladas: <?php echo $total_canceladas; ?></p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>

<?php include "includes/footer.php"; ?>