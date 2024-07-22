<?php


require "Checker.php";

$obj = new Checker();

$result = $obj->history();

header("content-type:json");
echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);