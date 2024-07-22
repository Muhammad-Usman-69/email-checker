<?php

//check if post
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    exit();
}

//check if didn't get
if (!isset($_POST["email"])) {
    echo json_encode(["error" => "Didn't Get"], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit();
}

//check if empty
if ($_POST["email"] == "") {
    echo json_encode(["error" => "Empty Input"], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit();
}

//check if allowed
session_start();
if (!isset($_SESSION["allow"]) || $_SESSION["allow"] != true) {
    header("location:./php/verify.php");
    exit();
}

header("Content-Type: application/json");


require "Checker.php";

$email = $_POST["email"];

$obj = new Checker();

$result = $obj->singleCheck($email, API);
$obj->saveToDb($result["id"], "none", "Single", $result["url"]);
$obj->increaseUse(1);

echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);