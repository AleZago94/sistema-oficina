<?php
session_start();

if(isset($_SESSION['usuario_id'])){
    header("location: index.php");
    exit;
}

include "config/conexao.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    if (empty($usuario) || empty($senha)) {
        echo "informe o usuario e senha";
    } else {

        $login = "SELECT id, nome, senha FROM usuarios where email = ?";
        $stmt = $conn->prepare($login);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $sql_login = $stmt->get_result();

       // $sql_login = $conn->query($login);

        if ($sql_login->num_rows > 0) {

            $result_login = $sql_login->fetch_assoc();

            $senha_login = $result_login['senha'];

            if (password_verify($senha, $senha_login)) {
                //senha correta 
                $_SESSION['usuario_id'] = $result_login['id'];
                $_SESSION['usuario_nome'] = $result_login['nome'];

                header("Location: index.php");
                exit;
            } else {
                echo "senha incorreta";
            }
        } else {
            echo "usuario nao encontrado";
        }
    }
}

include "includes/header.php";
include "includes/sidebar.php";

?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <form action="" method="POST">

                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" name="usuario" id="usuario" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" name="senha" id="senha" class="form-control">

                </div>

                <button type="submit" class="btn btn-primary" mt-3>fazer login</button>

            </form>

        </div>

    </div>

</main>