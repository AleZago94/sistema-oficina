<?php
require_once "includes/autenticacao.php";
require_once "config/conexao.php";
require_once "includes/helpers.php";


if (isset($_GET["erro"])) {

    switch ($_GET["erro"]) {

        case "falha_ao_salvar":
            echo "<script>alert('nao foi possivel salvar a movimentacao')</script>";
            break;

        case "campos_vazios":
            echo "<script>alert('preecha os campos corretamente')</script>";
            break;

        case "token_invalido":
            echo "<script>alert('Requisicao invalida tente novamente')</script>";
            break;
    }
}


if (isset($_GET["sucesso"])) {

    switch ($_GET["sucesso"]) {

        case "movimentacao_salva":
            echo "<script>alert('movimentacao cadastrada com sucesso');</script>";
            break;
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!validarCsrf()) {
        header("location: financeiro.php?erro=token_invalido");
        exit;
    }

    //  $id = $_POST['id'];
    $tipo = trim($_POST['tipo']);
    $descricao = trim($_POST['descricao']);
    $valor = floatval($_POST['valor']);
    //$ordem_id = $_POST['ordemid'];
    //  $data_movimentacao = $_POST['data_mov'];


    if (empty($tipo) || empty($descricao) || empty($valor)) {
        header("location: financeiro.php?erro=campos_vazios");
        exit;
    }

    $sql_insert = "INSERT INTO movimentacoes_financeiras (tipo, descricao, valor) VALUES(?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("ssd", $tipo, $descricao, $valor);

    if (!$stmt->execute()) {
        header("location: financeiro.php?erro=falha_ao_salvar");
        exit;
    }

    header("location: financeiro.php?sucesso=movimentacao_salva");
    exit;
}


$sql_listar = "SELECT movi.id,
                      movi.tipo,
                      movi.descricao,
                      movi.valor,
                      movi.created_at
 FROM movimentacoes_financeiras AS movi";

$listar = $conn->query($sql_listar);

$sql_entrada = "SELECT SUM(movi.valor) AS total_entrada
                FROM movimentacoes_financeiras movi
                WHERE movi.tipo = 'entrada'";

$total_entrada = $conn->query($sql_entrada);
$entrada = $total_entrada->fetch_assoc();


$sql_saida = "SELECT SUM(movi.valor) AS total_saida
             FROM  movimentacoes_financeiras movi
             WHERE movi.tipo = 'saida'";

$total_saida = $conn->query($sql_saida);
$saida = $total_saida->fetch_assoc();

$valor_entrada = $entrada['total_entrada'] ?? 0;
$valor_saida = $saida['total_saida'] ?? 0;

$saldo = $valor_entrada - $valor_saida;

$sql_soma = "SELECT SUM(s.valor) AS total
               FROM ordem_servicos_itens osi
JOIN servicos s ON osi.servico_id = s.id
JOIN ordens_servico os ON osi.ordem_servico_id = os.id
                WHERE os.status = 'aberta'";

$soma = $conn->query($sql_soma);
$total = $soma->fetch_assoc();


include "includes/header.php";
include "includes/sidebar.php";

?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">

            <!-- card de entradas -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-primary">
                        <div class="inner">
                            <h3><?php echo number_format($valor_entrada, 2, ',', '.'); ?></h3>
                            <p>total Entradas:</p>

                        </div>

                    </div>
                </div>


                <!-- card de saidas -->


                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-warning">
                        <div class="inner">
                            <h3><?php echo number_format($valor_saida, 2, ',', '.'); ?></h3>
                            <p>total saidas</p>

                        </div>

                    </div>
                </div>

                <!-- card de saldo total -->


                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-success">
                        <div class="inner">
                            <h3><?php echo number_format($saldo, 2, ',', '.'); ?></h3>
                            <p>Saldo total </p>

                        </div>

                    </div>
                </div>
            </div>

            <div>
                <form action="" method="POST">


                    <div class="mb-3">
                        <label for="tipo" name="tipo" id="tipo" class="form-label">despesa </label>
                        <select name="tipo" id="tipo">
                            <option value="entrada">entrada</option>
                            <option value="saida">saida</option>
                        </select>

                    </div>

                    <div class="mb-3">
                        <label for="descricao" name="descricao" id="descricao" class="form-label">descricao da despesa </label>
                        <input type="text" name="descricao" id="descricao" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="valor" name="valor" id="valor" class="form-label">valor da despesa</label>
                        <input type="number" step="0.01" name="valor" id="valor" class="form-control">
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <button type="submit" class="btn btn-primary mt-3">salvar </button>
                </form>
            </div>


            <div>
                <!-- tabela -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Resumo das despesas </h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <th>tipo de despesa </th>
                                    <th>descricao</th>
                                    <th>valor</th>
                                    <th>data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($lista = $listar->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo intval($lista['id']); ?></td>
                                        <td><?php echo htmlspecialchars($lista['tipo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($lista['descricao'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo number_format($lista['valor'], 2, ',', '.'); ?></td>
                                        <td><?php echo $lista['created_at']; ?></td>
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

<?php include "includes/footer.php" ?>