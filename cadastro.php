<?php
require_once "config/conexao.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $senha2 = $_POST['senha2'];


    if (empty($nome) || empty($email) || empty($senha) || empty($senha2)) {
        echo "prencha todos o campos corretamente";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "email invalido";
        exit;
    }


    if ($senha !== $senha2) {
        echo "digite a mesma senha corretamente";
        exit;
    }

    $sql_email = "SELECT id FROM usuarios WHERE email = ? ";
    $stmt = $conn->prepare($sql_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result_email = $stmt->get_result();


    if ($result_email->num_rows > 0) {
        echo "email ja cadastrado";
        exit;
    }

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    $insert = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";

    $stmt = $conn->prepare($insert);
    $stmt->bind_param("sss", $nome, $email, $senha_hash);


    if (!$stmt->execute()) {
        echo "erro no cadastro tente novamente ou contate um ADM";
        exit;
    }

    header("Location: login.php");
    exit;
}

include "includes/header.php";
include "includes/sidebar.php";


?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="nome" class="form-label">Digite o nome de usuario </label>
                    <input type="text" name="nome" id="nome" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Digite o email</label>
                    <input type="email" name="email" id="email" class="form-control">

                </div>

                <div class="mb-3">
                    <label for="senha" class="form-label">Digite sua senha </label>
                    <input type="password" name="senha" id="senha" class="form-control">

                    <label for="senha2" class="form-label">Digite a senha novamente</label>
                    <input type="password" name="senha2" id="senha2" class="form-control">

                </div>

                <button type="submit" class="btn btn-primary mt-3"> Cadastrar </button>




            </form>

        </div>

    </div>

</main>

<?php include "includes/footer.php"; ?>