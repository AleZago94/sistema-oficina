<?php

require_once "includes/autenticacao.php";
require_once "config/conexao.php";
require_once "includes/helpers.php";

if (isset($_GET["erro"])) {

    switch ($_GET["erro"]) {

        case "ordem_nao_encontrada":
            echo "<script>alert('nao foi possivel encontrar a ordem')</script>";
            break;

        case "servicos_nao_encontrados":
            echo "<script>alert('nao foi possivel encontrar os servicos')</script>";
            break;

        case "falha_atualizar_orden":
            echo "<script>alert('nao foi possivel fazer a atualizacao da ordem')</script>";
            break;

        case "campos_vazios":
            echo "<script>alert('preencha os dados corretamente')</script>";
            break;

        case "falha_inserir_servicos":
            echo "<script>alert('erro ao inserir servicos')</script>";
            break;

        case "falha_no_cadastro_OS":
            echo "<script>alert('erro ao cadastrar ordem de servico')</script>";
            break;

        case "falha_cadastrar_ordem":
            echo "<script>alert('erro ao cadastrar ordem de servico contate Administrador')</script>";
            break;


        case "token_invalido":
            echo "<script>alert('Requisição inválida. Tente novamente.')</script>";
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!validarCsrf()) {
        header("location: ordens.php?erro=token_invalido");
        exit;
    }
    $cliente_id = intval($_POST['cliente_id']);
    $moto_id = intval($_POST['moto_id']);
    $problema_relatado = trim($_POST['problema_relatado']);
    $servicos = $_POST['servicos'] ?? [];
    $status = $_POST['status'];
    $valor_mao_obra = floatval($_POST['valor_mao_obra']);
    $valor_pecas = floatval($_POST['valor_pecas']);

    $status_permitidos = ['aberta', 'em_andamento'];

    if (!in_array($status, $status_permitidos, true)) {
        header("location: ordens.php?erro=status_invalido");
        exit;
    }

    if (empty($cliente_id) || empty($moto_id) || empty($servicos) || empty($status)) {
        header("location: ordens.php?erro=campos_vazios");
        exit;
    }

    try {
        $conn->begin_transaction();

        $sql_ordem = "INSERT INTO ordens_servico 
        (cliente_id, moto_id, problema_relatado, valor_mao_obra, valor_pecas, status) 
        VALUES 
        (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_ordem);
        $stmt->bind_param("iisdds", $cliente_id, $moto_id, $problema_relatado, $valor_mao_obra, $valor_pecas, $status);
        $stmt->execute();

        $ordem_id = $conn->insert_id;



        $sql_item = "INSERT INTO ordem_servicos_itens
            (ordem_servico_id, servico_id)
            VALUES 
            (?, ?)";

        $stmt_item = $conn->prepare($sql_item);

        foreach ($servicos as $servico_id) {
            $servico_id = intval($servico_id);

            $stmt_item->bind_param("ii", $ordem_id, $servico_id);

            $stmt_item->execute();
        }
        $conn->commit();

        header("Location: ver_ordem.php?id=$ordem_id");
        exit;
    } catch (mysqli_sql_exception $erro) {
        $conn->rollback();
        error_log("Erro ao cadastrar OS: " . $erro->getMessage());
        header("location: ordens.php?erro=falha_cadastrar_ordem");
        exit;
    }
}

$sql_servicos = "SELECT * FROM  servicos ORDER BY nome ASC";
$result_servicos = $conn->query($sql_servicos);

$sql_clientes = "SELECT * FROM clientes ORDER BY nome ASC";
$result_clientes = $conn->query($sql_clientes);


$sql_motos = "SELECT motos.*, clientes.nome AS nome_cliente
    
        FROM motos

        JOIN clientes ON motos.cliente_id = clientes.id 

        ORDER BY clientes.nome ASC";

$result_motos = $conn->query($sql_motos);

include "includes/header.php";
include "includes/sidebar.php";

?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Nova Ordem de Serviço</h4>

                <a href="listar_ordens.php" class="btn btn-secondary btn-sm">
                    Voltar
                </a>
            </div>
            <div class="card card-transparente">
                <div class="card-body">

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="cliente_id" class="form-label">Cliente</label>

                            <select name="cliente_id" id="cliente_id" class="form-control">
                                <option value="">Selecione o cliente</option>
                                <?php while ($cliente = $result_clientes->fetch_assoc()): ?>
                                    <option value="<?php echo intval($cliente['id']); ?>">
                                        <?php echo htmlspecialchars($cliente['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="moto_id" class="form-label">Moto</label>
                            <select name="moto_id" id="moto_id" class="form-control">
                                <option value="">selecione a moto</option>
                                <?php while ($moto = $result_motos->fetch_assoc()): ?>
                                    <option
                                        value="<?php echo intval($moto['id']); ?>"
                                        data-cliente="<?php echo intval($moto['cliente_id']); ?>">
                                        <?php echo htmlspecialchars($moto['marca'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php echo htmlspecialchars($moto['modelo'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php echo htmlspecialchars($moto['placa'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>


                        <div class="mb-3">

                            <label class="form-label fw-semibold">Serviços</label>
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
                                                    id="servico_<?php echo intval($servico['id']); ?>">

                                                <label
                                                    class="form-check-label"
                                                    for="servico_<?php echo intval($servico['id']); ?>">

                                                    <?php echo htmlspecialchars($servico['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                                    -
                                                    R$ <?php echo number_format($servico['valor'], 2, ',', '.'); ?>

                                                </label>

                                            </div>

                                        </div>

                                    <?php endwhile; ?>

                                </div>
                            </div>

                        </div>





                        <label for="problema_relatado" class="form-label">problema relatado</label>
                        <textarea name="problema_relatado" id="problema_relatado" class="form-control"></textarea>

                        <div class="mb-3">
                            <label for="valor_mao_obra" class="form-label"> valor da mao de obra </label>
                            <input type="number" step="0.01" min="0" name="valor_mao_obra" id="valor_mao_obra" value="0" class="form-control">

                        </div>

                        <div class="mb-3">
                            <label for="valor_pecas" class="form-label"> valor das pecas </label>
                            <input type="number" step="0.01" min="0" name="valor_pecas" id="valor_pecas" value="0" class="form-control">
                        </div>


                        <label for="status" class="form-label">status</label>
                        <select name="status" id="status" class="form-control">

                            <option value="aberta">aberto</option>
                            <option value="em_andamento">andamento</option>
                        </select>

                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');  ?>">

                        <button type="submit" class="btn btn-primary mt-3">Salvar OS</button>

                    </form>







                </div>

            </div>



        </div>

    </div>

</main>

<script>
    document.getElementById('cliente_id').addEventListener('change', function() {

        const clienteSelecionado = this.value;
        const opcoesMotos = document.querySelectorAll('#moto_id option');

        document.getElementById('moto_id').value = '';

        opcoesMotos.forEach(function(option) {
            if (option.value === '') {
                option.style.display = 'block';
                return;
            }
            if (option.dataset.cliente === clienteSelecionado) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    });
</script>
<?php include "includes/footer.php"; ?>