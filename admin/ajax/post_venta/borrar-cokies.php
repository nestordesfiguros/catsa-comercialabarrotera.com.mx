<?php
$valor='';
setcookie("fact_serie", $valor, [
    "expires" => time() - 3600,
    "path" => "/",
    "samesite" => "Strict"
  ]);
  setcookie("fact_no", $valor, [
    "expires" => time() - 3600,
    "path" => "/",
    "samesite" => "Strict"
  ]);
  setcookie("id_factura", $valor, [
    "expires" => time() - 3600,
    "path" => "/",
    "samesite" => "Strict"
  ]);
  setcookie("fact_cliente", $valor, [
    "expires" => time() - 3600,
    "path" => "/",
    "samesite" => "Strict"
  ]);
  setcookie("fact_folio", $valor, [
    "expires" => time() - 3600,
    "path" => "/",
    "samesite" => "Strict"
  ]);

?>