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
while (($data = fgetcsv($csv, 200000)) !== FALSE) {
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
    if ($header == "Email" || $header == "email" || $header == "EMAIL") {
        $found = true;
        $index = $i;
    }
    $i++;
}

// if not found
if ($found == false) {
    echo json_encode(["error" => "Email column not found in given file. Allowed column names are \"Email\", \"email\" and \"EMAIL\"."]);
    exit();
}

array_shift($rows);

//after getting email column index, getting emails from it
foreach ($rows as $row) {
    $emails[] = $row[$index];
}

fclose($csv);

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

// saving csv in temp folder
$temp = "../v1/temp/temp" . $result["num"] . ".csv";
$csv = file_get_contents($fileTmpName);
$fp = fopen($temp, "w");
fwrite($fp, $csv);
fclose($fp);

$obj->saveToDb($result["id"], $task_id, "File", $result["url"], $temp);
$obj->increaseUse($count);

echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);