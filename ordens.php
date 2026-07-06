<?php

require_once "includes/autenticacao.php";
require_once "config/conexao.php";



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente_id = intval($_POST['cliente_id']);
    $moto_id = intval($_POST['moto_id']);
    $problema_relatado = trim($_POST['problema_relatado']);
    $servicos = $_POST['servicos'] ?? [];
    $status = $_POST['status'];

    if (empty($cliente_id) || empty($moto_id) || empty($servicos) || empty($status)) {
        echo "<script>alert('preencha os dados corretamente')</script>";
    } else {
        $sql_ordem = "INSERT INTO ordens_servico 
        (cliente_id, moto_id, problema_relatado, status) 
        VALUES 
        ('$cliente_id', '$moto_id', '$problema_relatado', '$status')";

        $conn->query($sql_ordem);

        $ordem_id = $conn->insert_id;

        foreach ($servicos as $servico_id) {
            $servico_id = intval($servico_id);

            $sql_item = "INSERT INTO ordem_servicos_itens 
            (ordem_servico_id, servico_id)
            VALUES 
            ('$ordem_id', '$servico_id')";

            $conn->query($sql_item);
        }

        header("Location: ver_ordem.php?id=$ordem_id");
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
        <div class="container-fluid">
            <h1>Orden de servico</h1>
            <form action="" method="POST">
                <label for="cliente_id" class="form-label"></label>

                <select name="cliente_id" id="cliente_id" class="form-control">
                    <option value="">Selecione o cliente</option>
                    <?php while ($cliente = $result_clientes->fetch_assoc()): ?>
                        <option value="<?php echo $cliente['id']; ?>">
                            <?php echo $cliente['nome']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="moto_id" class="form-label"></label>
                <select name="moto_id" id="moto_id" class="form-control">
                    <option value="">selecione a moto</option>
                    <?php while ($moto = $result_motos->fetch_assoc()): ?>
                        <option
                            value="<?php echo $moto['id']; ?>"
                            data-cliente="<?php echo $moto['cliente_id']; ?>">
                            <?php echo $moto['marca']; ?>
                            <?php echo $moto['modelo']; ?> -
                            <?php echo $moto['placa']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>


                <label class="form-label">Serviços</label>

                <?php while ($servico = $result_servicos->fetch_assoc()): ?>

                    <div class="form-check mb-2">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="servicos[]"
                            value="<?php echo $servico['id']; ?>"
                            id="servico_<?php echo $servico['id']; ?>">

                        <label
                            class="form-check-label"
                            for="servico_<?php echo $servico['id']; ?>">

                            <?php echo $servico['nome']; ?>
                            -
                            R$ <?php echo number_format($servico['valor'], 2, ',', '.'); ?>

                        </label>

                    </div>

                <?php endwhile; ?>

                <label for="problema_relatado" class="form-label">problema relatado</label>
                <textarea name="problema_relatado" id="problema_relatado" class="form-control"></textarea>



                <label for="status" class="form-label">status</label>
                <select name="status" id="status" class="form-control">

                    <option value="aberta">aberto</option>
                    <option value="em_andamento">andamento</option>
                    <option value="concluida">concluido</option>
                    <option value="cancelada">cancelada</option>
                </select>

                <button type="submit" class="btn btn-primary mt-3">Salvar OS</button>

            </form>

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