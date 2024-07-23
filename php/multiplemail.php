<?php

//check if post
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    exit();
}

//check if didn't get
if (!isset($_POST["emails"])) {
    echo json_encode(["error" => "Didn't Get"], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit();
}

//check if empty
if ($_POST["emails"] == "") {
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

$emails = $_POST["emails"];

//making limit to email
$limit = ONETIMELIMIT;
if (count($emails) > $limit) {
    echo json_encode(["error" => "Email limit exceeded. Can't be more than $limit."]);
    exit();
}

$count = count($emails);

$obj = new Checker();

$obj->checkUse($count);
$task_id = $obj->multipleCheck($emails, API);
$obj->saveTask($task_id);
$result = $obj->getMultipleResults($task_id, API);
$obj->saveToDb($id, $task_id, "Multiple", $result["url"], "none");
$obj->increaseUse($count);

echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
