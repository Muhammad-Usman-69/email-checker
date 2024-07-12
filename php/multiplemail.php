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

header("Content-Type: application/json");

require "config.php";
require "Checker.php";

$emails = $_POST["emails"];

$obj = new Checker();

$obj->dbConnect(DATABASE_HOSTNAME, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
$task_id = $obj->multipleCheck($emails, API);
$obj->saveTask($task_id);
$result = $obj->getMultipleResults($task_id, API);
$obj->saveToDb($result["id"], $task_id, "Multiple", $result["url"]);

echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
