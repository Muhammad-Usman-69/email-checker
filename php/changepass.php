<?php

require_once("./Checker.php");

$request = new Checker();
if ($request->changePass()) {
    echo "Email has been sent.";
}
