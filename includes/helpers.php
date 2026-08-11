<?php 
function validarCsrf() {

    if (
        !isset($_SESSION['csrf_token']) ||
        !isset($_POST['csrf_token'])
    ) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'],$_POST['csrf_token']);
}

?>