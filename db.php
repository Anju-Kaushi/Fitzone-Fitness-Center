<?php

$conn = mysqli_connect("localhost", "root", "", "fitzone");

if(!$conn){
    die("Connection Faild. " . connect_error());
}
?>