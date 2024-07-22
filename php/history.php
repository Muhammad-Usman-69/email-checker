<?php


require "Checker.php";

$obj = new Checker();

$obj->dbConnect(DATABASE_HOSTNAME, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);

$result = $obj->history();

header("content-type:json");
echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);