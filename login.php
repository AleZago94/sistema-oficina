<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header("location: index.php");
    exit;
}
if (isset($_GET["erro"])) {

    switch ($_GET["erro"]) {

       // case "falha_na_autenticacao":
          //  echo "<script>alert('usuario ou senha estao incorretos')</script>";
         //   break;

        case "usuario_senha_invalido":
            echo "<script>alert('usuario ou senha invalidos')</script>";
            break;

        case "campos_vazios":
            echo "<script>alert('informe o usuario e senha')</script>";
            break;
    }
}

include "config/conexao.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $usuario =  trim($_POST['usuario']);
    $senha = trim($_POST['senha']);

    if (empty($usuario) || empty($senha)) {
        header("location: login.php?erro=campos_vazios");
        exit;
    }

    $login = "SELECT id, nome, senha FROM usuarios where email = ?";
    $stmt = $conn->prepare($login);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $sql_login = $stmt->get_result();

    // $sql_login = $conn->query($login);

    if ($sql_login->num_rows <= 0) {
        header("location: login.php?erro=usuario_senha_invalidos");
        exit;
    }

    $result_login = $sql_login->fetch_assoc();

    if (password_verify($senha, $result_login['senha'])) {
        //senha correta 

        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $result_login['id'];
        $_SESSION['usuario_nome'] = $result_login['nome'];

        header("Location: index.php?sucesso=usuario_autenticado");
        exit;
    }

    header("location: login.php?erro=usuario_senha_invalidos");
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