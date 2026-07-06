<?php
require_once "includes/autenticacao.php";

require_once "config/conexao.php";

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    $sql_ordem = "SELECT  
    os.id,
    os.problema_relatado,
    os.valor_mao_obra,
    os.valor_pecas,
    os.status,
    motos.modelo AS modelo_moto, 
    c.nome AS nome_cliente
    FROM ordens_servico AS os
    JOIN clientes c on os.cliente_id = c.id
    JOIN motos  on os.moto_id =  motos.id 
    WHERE os.id = $id  ";
    $ordem = $conn->query($sql_ordem);
    $result = $ordem->fetch_assoc();
} else {
    echo "nenhum id encontrado";
}

$servicos_existentes = "SELECT servico_id
FROM ordem_servicos_itens
WHERE ordem_servico_id = $id";
$result_existentes = $conn->query($servicos_existentes);

$servicos_marcados = [];

while ($servico_check = $result_existentes->fetch_assoc()) {
    $servicos_marcados[] = $servico_check['servico_id'];
}

$servicos = "SELECT * FROM servicos ORDER BY nome ASC";
$result_servicos = $conn->query($servicos);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $cliente_id = $_POST['cliente_id'];
    $modelo_moto = $_POST['modelo_moto'];
    $problema_relatado = $_POST['problema_relatado'];
    $valor_mao_obra = $_POST['valor_mao_obra'];
    $valor_pecas = $_POST['valor_pecas'];
    $status = $_POST['status'];
    $servicos = $_POST['servicos'] ?? [];

    if (empty($cliente_id)  || empty($problema_relatado)) {
        echo "<script>alert('Preencha todos os campos');</script>";
    } else {
        $sql_update_ordem = "UPDATE ordens_servico 
    SET 
    problema_relatado = '$problema_relatado',
    valor_mao_obra = '$valor_mao_obra',
    valor_pecas = '$valor_pecas',
    status = '$status'
    WHERE id = $id";
        $conn->query($sql_update_ordem);

        $ordem_id = $id;

        foreach ($servicos as $servico_id) {
            if (!in_array($servico_id, $servicos_marcados)) {
                $servico_id = intval($servico_id);

                $sql_servico = "INSERT INTO ordem_servicos_itens
            (ordem_servico_id, servico_id) VALUES ('$ordem_id', '$servico_id')";

                $conn->query($sql_servico);
            }
        }
        header("Location: ver_ordem.php?id=$ordem_id");
        exit;
    }
}


include "includes/header.php";
include "includes/sidebar.php";
?>

<main class="app-main">
    <div class="app-content">
        <div class="conatainer-fluid">

            <form action="" method="POST">
                <input type="hidden" name="id" value="<?php echo $result['id']; ?>">
                <div class="mb-3">
                    <label for="cliente_id" class="form-label">cliente</label>
                    <input type="text" name="cliente_id" id="cliente_id" value="<?php echo $result['nome_cliente']; ?>" class="form-control"> <br>
                </div>

                <div class="mb-3">
                    <label for="modelo_moto" class="form-label">modelo da moto <?php echo $result['modelo_moto']; ?> </label>
                    <!-- <input type="text" name="modelo_moto" id="modelo_moto" value=""> -->
                </div>

                <div class="mb-3">
                    <label for="problema_relatadao" class="form-label"> problema relatado </label>
                    <input type="texte" name="problema_relatado" id="problema_relatado" value="<?php echo $result['problema_relatado']; ?>">
                </div>

                <div class="mb-3">
                    <label for="valor_mao_obra" class="form-label"> valor mao da mao de obra </label>
                    <input type="number" step="0.01" name="valor_mao_obra" id="valor_mao_obra" value="<?php echo $result['valor_mao_obra']; ?>" class="form-control">

                </div>

                <div class="mb-3">
                    <label for="valor_pecas" class="form-label"> valor das pecas </label>
                    <input type="number" step="0.01" name="valor_pecas" id="valor_pecas" value="<?php echo $result['valor_pecas']; ?>" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label"></label>
                    <select name="status" id="status" class="form-control">
                        <option value="aberta">aberto</option>
                        <option value="em_andamento">andamento</option>
                        <option value="concluida">concluido</option>
                        <option value="cancelada">cancelada</option>
                    </select>
                </div>

                <label class="form-label">servicos</label>

                <?php while ($servico = $result_servicos->fetch_assoc()): ?>


                    <div class="form-check mb-2">
                        <input class="form-check-input"
                            type="checkbox"
                            name="servicos[]"
                            <?php if (in_array($servico['id'], $servicos_marcados)) echo "checked"; ?>
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


                <button type="submit" class="btn btn-primary mt-3">Salvar edicao </button>


                <!-- if ($ordem->num_rows > 0): {


            echo $result['nome_cliente'];
            echo $result['modelo_moto'];
            echo $result['problema_relatado'];
            echo $result['valor_mao_obra'];
            echo $result['valor_pecas'];
            echo $result['status'];
        }
    endif; -->


            </form>

        </div>

    </div>

</main>




<?php include "includes/footer.php" ?>