<?php

//deleting after 7 day of creation
require "config.php";
require "Checker.php";

$obj = new Checker();

$obj->dbConnect(DATABASE_HOSTNAME, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);

//days
$days = 7;

// Taking current time
date_default_timezone_set("Asia/Karachi");
$today = date("Y-m-d");

// delete until
$until = date("Y-m-d", strtotime($today . " - $days days"));
$obj->delete($until);


/*
$today . " - $days days" combines today's date with the number of days you want to subtract (7). It looks something like "2023-11-22 - 7 days".
strtotime() takes this combined string and converts it into a number representing the date and time.
date("Y-m-d", ...) takes that number and converts it back into a date in the "Y-m-d" format.
*/

