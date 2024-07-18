<?php

header("Content-Type: json");

//check if post
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    exit();
}

//check if didn't get
if (!isset($_POST["id"]) || !isset($_POST["select"])) {
    echo json_encode(["error" => "Didn't Get"], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit();
}

//check if empty
if ($_POST["id"] == "" || $_POST["select"] == "") {
    echo json_encode(["error" => "Empty Input"], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit();
}

//check if allowed
session_start();
if (!isset($_SESSION["allow"]) || $_SESSION["allow"] != true) {
    header("location:./php/verify.php");
    exit();
}


require "config.php";
require "Checker.php";

$id = $_POST["id"];
$select = $_POST["select"];

$obj = new Checker();

$obj->dbConnect(DATABASE_HOSTNAME, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);

$obj->download($id, $select);

