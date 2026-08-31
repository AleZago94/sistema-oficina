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
$inicioMes = new DateTime('first day of this month');
$inicioProximoMes = new DateTime('first day of next month');

$inicio = $inicioMes->format('Y-m-d 00:00:00');
$fim = $inicioProximoMes->format('Y-m-d 00:00:00');

$sql_listar = "SELECT movi.id,
                      movi.tipo,
                      movi.descricao,
                      movi.valor,
                      movi.created_at
               FROM movimentacoes_financeiras AS movi
               WHERE movi.created_at >= ?
               AND movi.created_at < ?
               ORDER BY movi.created_at DESC";

$stmt_listar = $conn->prepare($sql_listar);

$stmt_listar->bind_param("ss", $inicio, $fim);

$stmt_listar->execute();

$resultado_lista = $stmt_listar->get_result();





$sql_entrada = "SELECT SUM(movi.valor) AS total_entrada
                FROM movimentacoes_financeiras movi
                WHERE movi.tipo = 'entrada' 
                AND movi.created_at >= ?
                AND movi.created_at < ? ";

$stmt_entrada = $conn->prepare($sql_entrada);

$stmt_entrada->bind_param("ss", $inicio, $fim);

$stmt_entrada->execute();

$resultado_entrada = $stmt_entrada->get_result();

$entrada = $resultado_entrada->fetch_assoc();


$sql_saida = "SELECT SUM(movi.valor) AS total_saida
             FROM  movimentacoes_financeiras movi
             WHERE movi.tipo = 'saida'
             AND movi.created_at >= ?
             AND movi.created_at < ? ";

$stmt_saida = $conn->prepare($sql_saida);

$stmt_saida->bind_param("ss", $inicio, $fim);

$stmt_saida->execute();

$resultado_saida = $stmt_saida->get_result();

$saida = $resultado_saida->fetch_assoc();



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

    <!-- Cabeçalho da página -->
    <div class="app-content-header">
        <div class="container-fluid">

            <div class="row mb-3">

                <div class="col-sm-8">
                    <h3 class="mb-1">Financeiro</h3>

                    <p class="text-body-secondary mb-0">
                        Acompanhe as movimentações financeiras da oficina
                    </p>
                </div>

                <div class="col-sm-4 d-flex justify-content-sm-end align-items-center mt-3 mt-sm-0">

                    <span class="badge text-bg-secondary fs-6">
                        <i class="bi bi-calendar3 me-1"></i>
                        Mês atual
                    </span>

                </div>

            </div>

        </div>
    </div>


    <div class="app-content">
        <div class="container-fluid">


            <!-- Cards financeiros -->
            <div class="row g-3 mb-4">

                <!-- Entradas -->
                <div class="col-lg-4 col-md-6">

                    <div class="small-box text-bg-primary h-100">

                        <div class="inner">

                            <p class="mb-1">
                                Total de Entradas
                            </p>

                            <h3 class="mb-0">
                                R$ <?php echo number_format($valor_entrada, 2, ',', '.'); ?>
                            </h3>

                        </div>

                        <div class="small-box-icon">
                            <i class="bi bi-arrow-up-circle"></i>
                        </div>

                    </div>

                </div>


                <!-- Saídas -->
                <div class="col-lg-4 col-md-6">

                    <div class="small-box text-bg-warning h-100">

                        <div class="inner">

                            <p class="mb-1">
                                Total de Saídas
                            </p>

                            <h3 class="mb-0">
                                R$ <?php echo number_format($valor_saida, 2, ',', '.'); ?>
                            </h3>

                        </div>

                        <div class="small-box-icon">
                            <i class="bi bi-arrow-down-circle"></i>
                        </div>

                    </div>

                </div>


                <!-- Saldo -->
                <div class="col-lg-4 col-md-12">

                    <div class="small-box <?php echo $saldo >= 0 ? 'text-bg-success' : 'text-bg-danger'; ?> h-100">

                        <div class="inner">

                            <p class="mb-1">
                                Saldo do Mês
                            </p>

                            <h3 class="mb-0">
                                R$ <?php echo number_format($saldo, 2, ',', '.'); ?>
                            </h3>

                        </div>

                        <div class="small-box-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>

                    </div>

                </div>

            </div>


            <!-- Conteúdo principal -->
            <div class="row g-4">


                <!-- Formulário -->
                <div class="col-lg-4">

                    <div class="card h-100">

                        <div class="card-header">

                            <h3 class="card-title">
                                <i class="bi bi-plus-circle me-2"></i>
                                Nova Movimentação
                            </h3>

                        </div>


                        <div class="card-body">

                            <form action="" method="POST">


                                <!-- Tipo -->
                                <div class="mb-3">

                                    <label for="tipo" class="form-label">
                                        Tipo
                                    </label>

                                    <select
                                        name="tipo"
                                        id="tipo"
                                        class="form-select"
                                        required>

                                        <option value="entrada">
                                            Entrada manual
                                        </option>

                                        <option value="saida">
                                            Saída
                                        </option>

                                    </select>

                                </div>


                                <!-- Descrição -->
                                <div class="mb-3">

                                    <label for="descricao" class="form-label">
                                        Descrição
                                    </label>

                                    <input
                                        type="text"
                                        name="descricao"
                                        id="descricao"
                                        class="form-control"
                                        placeholder="Ex: Compra de óleo"
                                        required>

                                </div>


                                <!-- Valor -->
                                <div class="mb-3">

                                    <label for="valor" class="form-label">
                                        Valor
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            R$
                                        </span>

                                        <input
                                            type="number"
                                            name="valor"
                                            step="0.01"
                                            min="0.01"
                                            id="valor"
                                            class="form-control"
                                            placeholder="0,00"
                                            required>

                                    </div>

                                </div>


                                <!-- CSRF -->
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">


                                <!-- Botão -->
                                <div class="d-grid">

                                    <button
                                        type="submit"
                                        class="btn btn-primary">

                                        <i class="bi bi-check-circle me-1"></i>
                                        Salvar Movimentação

                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>


                <!-- Movimentações -->
                <div class="col-lg-8">

                    <div class="card h-100">

                        <div class="card-header d-flex align-items-center">

                            <h3 class="card-title mb-0">

                                <i class="bi bi-list-ul me-2"></i>
                                Movimentações do Mês

                            </h3>

                        </div>


                        <div class="card-body p-0">

                            <div class="table-responsive">

                                <table class="table table-hover table-striped mb-0 align-middle">

                                    <thead>

                                        <tr>

                                            <th>
                                                Tipo
                                            </th>

                                            <th>
                                                Descrição
                                            </th>

                                            <th>
                                                Valor
                                            </th>

                                            <th>
                                                Data
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        <?php if ($resultado_lista->num_rows > 0): ?>

                                            <?php while ($listar = $resultado_lista->fetch_assoc()): ?>

                                                <tr>

                                                    <!-- Tipo -->
                                                    <td>

                                                        <?php if ($listar['tipo'] === 'entrada'): ?>

                                                            <span class="badge text-bg-success">

                                                                <i class="bi bi-arrow-up me-1"></i>
                                                                Entrada

                                                            </span>

                                                        <?php else: ?>

                                                            <span class="badge text-bg-danger">

                                                                <i class="bi bi-arrow-down me-1"></i>
                                                                Saída

                                                            </span>

                                                        <?php endif; ?>

                                                    </td>


                                                    <!-- Descrição -->
                                                    <td>

                                                        <?php
                                                        echo htmlspecialchars(
                                                            $listar['descricao'],
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        );
                                                        ?>

                                                    </td>


                                                    <!-- Valor -->
                                                    <td class="fw-semibold">

                                                        R$
                                                        <?php
                                                        echo number_format(
                                                            $listar['valor'],
                                                            2,
                                                            ',',
                                                            '.'
                                                        );
                                                        ?>

                                                    </td>


                                                    <!-- Data -->
                                                    <td>

                                                        <?php
                                                        echo date(
                                                            'd/m/Y H:i',
                                                            strtotime($listar['created_at'])
                                                        );
                                                        ?>

                                                    </td>

                                                </tr>

                                            <?php endwhile; ?>


                                        <?php else: ?>

                                            <tr>

                                                <td
                                                    colspan="4"
                                                    class="text-center text-body-secondary py-5">

                                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>

                                                    Nenhuma movimentação encontrada neste mês.

                                                </td>

                                            </tr>

                                        <?php endif; ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>

<?php include "includes/footer.php" ?>