<?php

//check if post
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    exit();
}

//check if didn't get
if (!isset($_FILES["file"])) {
    echo json_encode(["error" => "Didn't Get"]);
    exit();
}

//check if empty
if ($_FILES["file"] == "") {
    echo json_encode(["error" => "Empty Input"]);
    exit();
}

//check if allowed
session_start();
if (!isset($_SESSION["allow"]) || $_SESSION["allow"] != true) {
    header("location:./php/verify.php");
    exit();
}

header("Content-Type: application/json");

require "config.php";
require "Checker.php";

$file = $_FILES["file"];

$fileName = $file["name"];
$fileTmpName = $file["tmp_name"]; //path of image

//check if csv
$nameArr = explode(".", $fileName);
if (end($nameArr) != "csv") {
    echo json_encode(["error" => "File is not csv"]);
    exit();
}

//opening file for read
$csv = fopen($fileTmpName, "r");

//handling file and pushing data
while (($data = fgetcsv($csv, 2000)) !== FALSE) {
    $rows[] = $data;
}

//taking first row for header
$headers = $rows[0];

// initializing for looping
$i = 0;
// initializing for column index
$index = 0;
// for email found
$found = false;

//looping though headers to get email header index
foreach ($headers as $header) {
    //checking if contain email
    if (str_contains($header, "email")) {
        $found = true;
        $index = $i;
    }
    $i++;
}

// if not found
if ($found == false) {
    echo json_encode(["error" => "Email not found in given file"]);
    exit();
}

array_shift($rows);

//after getting email column index, getting emails from it
foreach ($rows as $row) {
    $emails[] = $row[$index];
}

fclose($csv);

//making limit to email
$limit = 100;
if (count($emails) > $limit) {
    echo json_encode(["error" => "Email limit exceeded. Can't be more than $limit."]);
    exit();
}

$obj = new Checker();

$obj->dbConnect(DATABASE_HOSTNAME, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
$task_id = $obj->multipleCheck($emails, API);
$obj->saveTask($task_id);
$result = $obj->getMultipleResults($task_id, API);
$obj->saveToDb($result["id"], $task_id, "File", $result["url"]);

echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);