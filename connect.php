<?php

try 
{
    $dbhost = 'localhost';
    $dbuser = 'root';
    $dbpass = '';
    $db = 'aquastore';
    $conn = new PDO("mysql:host=$dbhost;dbname=$db", $dbuser, $dbpass);
}
catch (PDOException $e)
{
    echo "Error!: " . $e->getMessage() . "<br/>";
    die();
 }
