<?php
$hashAdmin = password_hash("admin123", PASSWORD_DEFAULT);
$hashCliente = password_hash("cliente123", PASSWORD_DEFAULT);

echo $hashAdmin . " " . $hashCliente;
?>
