<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once("../config.php");
require("layout/header.php");
?>

<br />
<div class="alert alert-danger" role="alert">
    <h4 class="alert-heading">¡Error!</h4>
    <p>Ha habido un error al realizar la operación:</p>
    <p style="font-style: italic;"><?php echo $_SESSION["CRUDMVC_ERROR"]; ?></p>
    <hr>
    <p class="mb-0">
        <button type="submit" class="btn btn-primary" onclick="window.history.back()">Reintentar</button>
        <a href="<?php echo URLSITE; ?>"><button type="button" class="btn btn-outline-secondary float-end">Cancelar</button></a>
    </p>
</div>

<?php require("layout/footer.php"); ?>